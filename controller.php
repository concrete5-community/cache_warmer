<?php
namespace Concrete\Package\CacheWarmer;

use Job;
use Package;
use Page;
use SinglePage;

class Controller extends Package
{
    protected $pkgHandle = 'cache_warmer';
    protected $appVersionRequired = '5.7.4';
    protected $pkgVersion = '1.1';

    protected $single_pages = array(
        '/dashboard/system/optimization/cache_warmer' => array(
            'cName' => 'Cache Warmer',
        ),
    );

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

        $this->installPages($pkg);
    }

    /**
     * @param Package $pkg
     */
    protected function installPages($pkg)
    {
        foreach ($this->single_pages as $path => $value) {
            if (!is_array($value)) {
                $path = $value;
                $value = array();
            }

            $page = Page::getByPath($path);
            if (!$page || $page->isError()) {
                $single_page = SinglePage::add($path, $pkg);

                if ($value) {
                    $single_page->update($value);
                }
            }
        }
    }
}
