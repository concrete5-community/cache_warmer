<?php

namespace A3020\CacheWarmer;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Page\Type\Type;

class PageTypes
{
    /**
     * @var \Concrete\Core\Config\Repository\Repository
     */
    private $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getPageTypeOptions()
    {
        $options = [];
        foreach (Type::getList() as $pt) {
            $options[$pt->getPageTypeID()] = $pt->getPageTypeName();
        }

        return $options;
    }

    /**
     * @return \Concrete\Core\Page\Type\Type[]
     */
    public function getSelectedPageTypes()
    {
        $selected = [];

        foreach ($this->getSelectedPageTypeIds() as $id) {
            $type = Type::getByID($id);
            if ($type) {
                $selected[] = $type;
            }
        }

        return $selected;
    }

    /**
     * @return string[]
     */
    public function getSelectedPageTypeHandles()
    {
        $handles = [];

        foreach ($this->getSelectedPageTypes() as $type) {
            $handles[] = $type->getPageTypeHandle();
        }

        return $handles;
    }

    /**
     * @return array
     */
    public function getSelectedPageTypeIds()
    {
        $pageTypes = $this->config->get('cache_warmer.settings.page_types');

        return is_array($pageTypes) ? $pageTypes : [];
    }
}