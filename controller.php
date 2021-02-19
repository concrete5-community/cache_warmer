<?php

namespace Concrete\Package\CacheWarmer;

use A3020\CacheWarmer\Listener\CacheFlush;
use A3020\CacheWarmer\Listener\CacheWarmerNeedsRewarm;
use Concrete\Core\Job\Job;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Single;

final class Controller extends Package
{
    protected $pkgHandle = 'cache_warmer';
    protected $appVersionRequired = '8.3.1';
    protected $pkgVersion = '2.1.3';
    protected $pkgAutoloaderRegistries = [
        'src/CacheWarmer' => '\A3020\CacheWarmer',
    ];

    public function getPackageName()
    {
        return t('Cache Warmer');
    }

    public function getPackageDescription()
    {
        return t('Generates cache files to reduce load times.');
    }

    public function on_start()
    {
        $this->app['director']->addListener('on_cache_flush', function($event) {
            /** @var \A3020\CacheWarmer\Listener\CacheFlush $listener */
            $listener = $this->app->make(CacheFlush::class);
            $listener->handle($event);
        });

        $this->app['director']->addListener('on_cache_warmer_needs_rewarm', function($event) {
            /** @var \A3020\CacheWarmer\Listener\CacheWarmerNeedsRewarm $listener */
            $listener = $this->app->make(CacheWarmerNeedsRewarm::class);
            $listener->handle($event);
        });
    }

    public function install()
    {
        $pkg = parent::install();

        Job::installByPackage('cache_warmer', $pkg);

        $this->installPage($pkg);
    }

    /**
     * @param \Concrete\Core\Entity\Package $pkg
     */
    protected function installPage($pkg)
    {
        $path = '/dashboard/system/optimization/cache_warmer';

        $page = Page::getByPath($path);
        if (!$page || $page->isError()) {
            $single_page = Single::add($path, $pkg);
            $single_page->update([
                'cName' => 'Cache Warmer',
            ]);
        }
    }
}
