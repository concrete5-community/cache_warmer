<?php

namespace Concrete\Package\CacheWarmer;

use Concrete\Core\Job\Job;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Single;

final class Controller extends Package
{
    protected $pkgHandle = 'cache_warmer';
    protected $appVersionRequired = '8.3.1';
    protected $pkgVersion = '2.1.0';
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
