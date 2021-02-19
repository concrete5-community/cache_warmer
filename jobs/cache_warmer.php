<?php 
namespace Concrete\Package\CacheWarmer\Job;

use Core;
use Config;
use Package;
use Page;
use PageList;
use QueueableJob;
use Concrete\Core\Cache\Page\PageCache;
use Concrete\Core\Cache\Page\PageCacheRecord;
use Concrete\Core\Support\Facade\Log;
use Concrete\Package\CacheWarmer\Controller\SinglePage\Dashboard\System\Optimization\CacheWarmer as CacheWarmerController;
use ZendQueue\Message as ZendQueueMessage;
use ZendQueue\Queue as ZendQueue;

class CacheWarmer extends QueueableJob
{
    protected $cacheLibrary;

    public function getJobName()
    {
        $pkg = Package::getByHandle('cache_warmer');
        return $pkg->getPackageName();
    }

    public function getJobDescription()
    {
        $pkg = Package::getByHandle('cache_warmer');
        return $pkg->getPackageDescription();
    }

    public function __construct()
    {
        $this->jQueueBatchSize = 5;
        $this->cacheLibrary = PageCache::getLibrary();
    }


    /**
     * @throws \Exception
     * @param ZendQueue $q
     * @return void
     */
    public function start(ZendQueue $q)
    {
        if (Core::isRunThroughCommandLineInterface()) {
            $this->reset();
            throw new \Exception(t("This job won't run via the CLI"));
        }

        $pl = new PageList();
        $pl->sortBy('rand()');
        $pl->ignorePermissions();


        /**
         * Check if cache is disabled globally.
         * If so, only retrieve pages that override the default cache setting.
         */
        if (Config::get('concrete.cache.pages') == false) {
            $pl->getQueryObject()->andWhere('p.cCacheFullPageContent = 1');
        }


        /**
         * Filter by Page Type
         */
        $page_type_handles = CacheWarmerController::getSelectedPageTypeHandles();
        if (is_array($page_type_handles) && count($page_type_handles) > 0) {
            $pl->filterByPageTypeHandle($page_type_handles);
        }


        /**
         * If cCacheFullPageContent is 0, page cache is disabled.
         */
        $pl->getQueryObject()->andWhere('p.cCacheFullPageContent != 0');


        /**
         * Limit the number of pages per batch.
         * Default: 200.
         */
        $max_pages = Config::get('cache_warmer.settings.max_pages');
        $max_pages = $max_pages ? $max_pages : 200;
        $pl->getQueryObject()->setMaxResults($max_pages);


        /**
         * Get the raw SQL data.
         */
        $results = $pl->executeGetResults();


        foreach($results as $row) {
            $q->send($row['cID']);
        }
    }


    /**
     * @param ZendQueueMessage $msg
     * @return void
     */
    public function processQueueItem(ZendQueueMessage $msg)
    {
        $page = Page::getByID($msg->body);

        if (!$page OR $page->isError()) {
            return;
        }

        // Check if cache file already exists
        if ($this->isCached($page)) {
            return;
        }

        /**
         * 1. Check if cache is enabled when blocks on allow it.
         * 2. Check if the page uses the global settings (-1) or if it is set manually to block caching (1).
         */
        if (Config::get('concrete.cache.pages') === 'block' && ($page->getCollectionFullPageCaching() == -1 OR $page->getCollectionFullPageCaching() == 1)) {
            $blocks = $page->getBlocks();
            $blocks = array_merge($page->getGlobalBlocks(), $blocks);

            foreach ($blocks as $b) {
                if (!$b->cacheBlockOutput()) {
                    return;
                }
            }
        }

        $this->cachePage($page);
    }


    /**
     * Checks if page is cached and whether cache file is still valid.
     *
     * @param \Concrete\Core\Page\Page $page
     * @return bool
     */
    protected function isCached($page)
    {
        $rec = $this->cacheLibrary->getRecord($page);
        if( $rec instanceof PageCacheRecord) {
            if ($rec->validate()) {
                return true;
            }
        }

        return false;
    }


    /**
     * Create cache file for $page.
     *
     * @param \Page $page
     * @return void
     */
    protected function cachePage($page)
    {
        $fh   = Core::make('helper/file');
        $path = $page->getCollectionLink(true);

        // This is the C5 wrapper for cURL
        $page_content = $fh->getContents($path);

        if (empty($page_content)) {
            $msg = t("Cache file couldn't be created for '%s'", $path);
            Log::addError($msg);
        }
    }


    /**
     * @param ZendQueue $q
     * @return string
     */
    public function finish(ZendQueue $q)
    {
        return t("Cache Warmer completed");
    }
}