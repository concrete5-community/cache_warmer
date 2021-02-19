<?php  
namespace Concrete\Package\CacheWarmer\Controller\SinglePage\Dashboard\System\Optimization;

use Concrete\Core\Http\ResponseAssetGroup;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Page\Type\Type;
use Config;
use Core;

class CacheWarmer extends DashboardPageController
{
    public function on_start()
    {
        $ag = ResponseAssetGroup::get();
        $ag->requireAsset('select2');

        $this->error = Core::make('helper/validation/error');

        $this->set('selected_page_types', self::getSelectedPageTypes());
    }

    public function save()
    {
        if (Core::make('token')->validate('cache_warmer.settings') == false) {
            $this->error->add(Core::make('token')->getErrorMessage());

            return;
        }

        /*
         * Make sure we're working with integers only.
         */
        $page_types = $this->post('page_type_id');
        if ($page_types && is_array($page_types)) {
            foreach ($page_types as $index => $page_type) {
                $page_type_id = filter_var($page_type, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
                $page_types[$index] = $page_type_id;
            }
        }
        $max_pages = filter_var($this->post('max_pages'), FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));

        Config::save('cache_warmer.settings.max_pages',  $max_pages);
        Config::save('cache_warmer.settings.page_types', $page_types);

        $this->redirect($this->action('save_success'));
    }

    public function save_success()
    {
        $this->set('message', t('Settings saved'));
    }

    /**
     * @static
     *
     * @return array
     */
    public static function getSelectedPageTypes()
    {
        $types = Config::get('cache_warmer.settings.page_types');
        if ($types && is_array($types)) {
            foreach ($types as $index => $type_id) {
                $type = Type::getByID($type_id);
                if ($type) {
                    $types[$index] = $type;
                }
            }
        }

        return ($types) ? $types : array();
    }

    /**
     * @static
     *
     * @return array
     */
    public static function getSelectedPageTypeHandles()
    {
        $types = self::getSelectedPageTypes();
        foreach ($types as $index => $type) {
            $types[$index] = $type->getPageTypeHandle();
        }

        return $types;
    }
}
