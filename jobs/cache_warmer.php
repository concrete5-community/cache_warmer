<?php

namespace Concrete\Package\CacheWarmer\Job;

use A3020\CacheWarmer\PageTypes;
use Concrete\Core\Application\Application;
use Concrete\Core\Cache\Page\PageCache;
use Concrete\Core\Cache\Page\PageCacheRecord;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Console\ConsoleAwareInterface;
use Concrete\Core\Console\ConsoleAwareTrait;
use Concrete\Core\File\Service\File;
use Concrete\Core\Job\QueueableJob;
use Concrete\Core\Logging\Logger;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\PageList;
use Exception;
use Symfony\Component\Console\Helper\ProgressBar;
use ZendQueue\Message as ZendQueueMessage;
use ZendQueue\Queue as ZendQueue;

class CacheWarmer extends QueueableJob implements ConsoleAwareInterface
{
    use ConsoleAwareTrait;

    /**
     * * Not named 'app' because the parent class might change
     *
     * @var \Concrete\Core\Application\Application
     */
    private $appInstance;

    /** @var ProgressBar */
    protected $progressBar;

    /** @var PageCache */
    protected $cacheLibrary;

    public function __construct(Application $application)
    {
        $this->appInstance = $application;
        $this->jQueueBatchSize = $this->getJobQueueBatchSize();
        $this->cacheLibrary = PageCache::getLibrary();

        // If the job quite unexpectedly before, it'll not continue in the 'start' method
        // Therefore we'll initialize it here, but then without the 2nd parameter (the max number of steps)
        $this->progressBar = new ProgressBar($this->getOutput());
        $this->progressBar->display();

        parent::__construct();
    }

    public function getJobName()
    {
        return t('Cache Warmer');
    }

    public function getJobDescription()
    {
        return t('Generates cache files to reduce load times.');
    }

    /**
     * @throws \Exception
     *
     * @param ZendQueue $q
     */
    public function start(ZendQueue $q)
    {
        if ($this->appInstance->isRunThroughCommandLineInterface()) {
            /** @var \Concrete\Core\Entity\Site\Site|null $site */
            $siteList = $this->appInstance->make('site')->getList();

            if (count($siteList) !== 1) {
                throw new Exception("The CLI modus can only be used if there's one site configured.");
            }

            if (empty($this->getCanonicalUrl())) {
                throw new Exception("Please configure a canonical URL in order to run this job via CLI.");
            }

            /** @var Repository $config */
            $config = $this->appInstance->make(Repository::class);
            if ($config->get('cache_warmer.settings.needs_rewarm', true) === true) {
                // OK, Cache Warmer will do it's job... but first, let's set it to false
                // so that it doesn't keep running if the job is set up e.g. as a minutely cron job.
                $config->save('cache_warmer.settings.needs_rewarm', false);
            } else {
                $this->traitOutput->writeln(t("The cache hasn't been flushed, so rewarming is currently not needed."));
                return;
            }
        }

        /** @var Repository $config */
        $config = $this->appInstance->make(Repository::class);

        $pl = new PageList();
        $pl->sortBy('rand()');
        $pl->ignorePermissions();

        /** @var PageTypes $pageTypes */
        $pageTypes = $this->appInstance->make(PageTypes::class);

        /*
         * Filter by Page Type
         */
        $pageTypeHandles = $pageTypes->getSelectedPageTypeHandles();
        if (count($pageTypeHandles)) {
            $pl->filterByPageTypeHandle($pageTypeHandles);
        }

        /*
         * If cCacheFullPageContent is 0, page cache is disabled.
         */
        $pl->getQueryObject()->andWhere('p.cCacheFullPageContent != 0');

        /*
         * Limit the number of pages per batch.
         * Default: 200.
         */
        $maxPages = $config->get('cache_warmer.settings.max_pages');
        $maxPages = $maxPages ? $maxPages : 200;
        $pl->getQueryObject()->setMaxResults($maxPages);

        /*
         * Get the raw SQL data.
         */
        $results = $pl->executeGetResults();

        foreach ($results as $row) {
            $q->send($row['cID']);
        }

        if ($this->hasConsole()) {
            $this->progressBar = new ProgressBar($this->getOutput(), count($results));
            $this->progressBar->display();
        }
    }

    /**
     * @param ZendQueueMessage $msg
     */
    public function processQueueItem(ZendQueueMessage $msg)
    {
        /** @var Page $page */
        $page = Page::getByID($msg->body);

        if (!$page or $page->isError()) {
            return;
        }

        if ($this->hasConsole()) {
            $this->progressBar->advance();
        }

        // Check if cache file already exists
        try {
            if ($this->isCached($page)) {
                return;
            }
        } catch (Exception $e) {
            $this->appInstance->make(Logger::class)->addError($e->getMessage());
            return;
        }

        /** @var Repository $config */
        $config = $this->appInstance->make(Repository::class);

        /*
         * 1. Check if cache is enabled when blocks on allow it.
         * 2. Check if the page uses the global settings (-1) or if it is set manually to block caching (1).
         */
        if ($config->get('concrete.cache.pages') === 'block' && ($page->getCollectionFullPageCaching() == -1 || $page->getCollectionFullPageCaching() == 1)) {
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
     * @param ZendQueue $q
     *
     * @return string
     */
    public function finish(ZendQueue $q)
    {
        $this->progressBar->clear();

        return t('%s ran successfully', t('Cache Warmer'));
    }

    public function getJobQueueBatchSize()
    {
        /** @var Repository $config */
        $config = $this->appInstance->make(Repository::class);

        $size = (int) $config->get('cache_warmer.settings.job_queue_batch');

        return $size ? $size : 5;
    }

    /**
     * Checks if page is cached and whether cache file is still valid.
     *
     * @param \Concrete\Core\Page\Page $page
     *
     * @return bool
     *
     * @throws \ReflectionException
     */
    private function isCached($page)
    {
        $record = $this->cacheLibrary->getRecord($page);

        /** @var PageCacheRecord $record  */
        if ($record instanceof PageCacheRecord) {
            $validated = $record->validate($this->appInstance->make(\Concrete\Core\Http\Request::class));

            return $validated;
        }

        return false;
    }

    /**
     * Regenerate the cache for a page
     *
     * @param Page $page
     */
    private function cachePage(Page $page)
    {
        $url = $this->getUrlForPage($page);

        $urlParsed = @parse_url($url);
        if (!isset($urlParsed['scheme'])) {
            // An URL without scheme (e.g. '/' shouldn't be processed.
            return;
        }

        /** @var File $fh */
        $fh = $this->appInstance->make('helper/file');

        // This is a C5 wrapper for cURL
        $pageContent = $fh->getContents($url);

        if (empty($pageContent)) {
            $msg = t(/* %s is the url of the page */"Page %s returned nothing. A cache file could probably not be created.", $url);
            $this->appInstance->make(Logger::class)->addError($msg);
        }
    }

    /**
     * Get the full URL of a page
     *
     * @param Page $page
     *
     * @return string
     */
    private function getUrlForPage(Page $page)
    {
        return $page->getCollectionLink(true);
    }

    /**
     * Get the canonical URL of the website
     *
     * When running via CLI, we don't have an active site
     * in that case the default site is picked.
     *
     * @return string|null
     */
    private function getCanonicalUrl()
    {
        $site = $this->appInstance->make('site')->getDefault();

        $siteConfig = $site->getConfigRepository();

        return $siteConfig->get('seo.canonical_url');
    }
}
