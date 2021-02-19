<?php

namespace Concrete\Package\CacheWarmer\Controller\SinglePage\Dashboard\System\Optimization;

use A3020\CacheWarmer\PageTypes;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Http\ResponseAssetGroup;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Routing\Redirect;

class CacheWarmer extends DashboardPageController
{
    public function on_before_render()
    {
        $ag = ResponseAssetGroup::get();
        $ag->requireAsset('select2');

        parent::on_before_render();
    }

    public function view()
    {
        $this->error = $this->app->make('helper/validation/error');

        $this->set('jobQueueBatch', $this->getConfig()->get('cache_warmer.settings.job_queue_batch'));
        $this->set('maxPages', $this->getConfig()->get('cache_warmer.settings.max_pages'));

        /** @var PageTypes $pageTypes */
        $pageTypes = $this->app->make(PageTypes::class);

        $this->set('pageTypeOptions', $pageTypes->getPageTypeOptions());
        $this->set('selectedPageTypeIds', $pageTypes->getSelectedPageTypeIds());
    }

    public function save()
    {
        if (!$this->token->validate('cache_warmer.settings')) {
            $this->flash('error', $this->token->getErrorMessage());

            return Redirect::to('/dashboard/system/optimization/cache_warmer');
        }

        // Make sure we're working with integers only
        $pageTypeIds = $this->post('page_type_id');
        if ($pageTypeIds && is_array($pageTypeIds)) {
            foreach ($pageTypeIds as $index => $page_type) {
                $page_type_id = filter_var($page_type, FILTER_VALIDATE_INT, [
                    'options' => [
                        'min_range' => 1,
                    ],
                ]);

                $pageTypeIds[$index] = $page_type_id;
            }
        }

        $config = $this->getConfig();

        $queueBatch = (int) $this->post('job_queue_batch') > 0 ? (int) $this->post('job_queue_batch') : null;
        $config->save('cache_warmer.settings.job_queue_batch', $queueBatch);

        $maxPages = (int) $this->post('max_pages') > 0 ? (int) $this->post('max_pages') : null;
        $config->save('cache_warmer.settings.max_pages', $maxPages);

        $config->save('cache_warmer.settings.page_types', $pageTypeIds);

        $this->flash('success', t('Settings have been saved'));

        return Redirect::to('/dashboard/system/optimization/cache_warmer');
    }

    /**
     * @return Repository
     */
    private function getConfig()
    {
        return $this->app->make(Repository::class);
    }
}
