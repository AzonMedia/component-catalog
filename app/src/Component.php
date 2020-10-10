<?php

declare(strict_types=1);

namespace GuzabaPlatform\Catalog;

use Guzaba2\Base\Exceptions\RunTimeException;
use GuzabaPlatform\Catalog\Models\Category;
use GuzabaPlatform\Components\Base\BaseComponent;
use GuzabaPlatform\Components\Base\Interfaces\ComponentInitializationInterface;
use GuzabaPlatform\Components\Base\Interfaces\ComponentInterface;
use GuzabaPlatform\Platform\Application\ModelFrontendMap;
use GuzabaPlatform\Platform\Application\VueComponentHooks;

/**
 * Class Component
 * @package Azonmedia\Tags
 */
class Component extends BaseComponent implements ComponentInterface, ComponentInitializationInterface
{

    protected const CONFIG_DEFAULTS = [
        'services'      => [
            'FrontendRouter',
            'FrontendHooks',
            'ModelFrontendMap'
        ],
    ];

    protected const CONFIG_RUNTIME = [];

    protected const COMPONENT_NAME = "Catalog";
    //https://components.platform.guzaba.org/component/{vendor}/{component}
    protected const COMPONENT_URL = 'https://components.platform.guzaba.org/component/guzaba-platform/catalog';
    //protected const DEV_COMPONENT_URL//this should come from composer.json
    protected const COMPONENT_NAMESPACE = __NAMESPACE__;
    protected const COMPONENT_VERSION = '0.0.1';//TODO update this to come from the Composer.json file of the component
    protected const VENDOR_NAME = 'Azonmedia';
    protected const VENDOR_URL = 'https://azonmedia.com';
    protected const ERROR_REFERENCE_URL = 'https://github.com/AzonMedia/component-catalog/tree/master/docs/ErrorReference/';

    /**
     * Must return an array of the initialization methods (method names or description) that were run.
     * @return array
     * @throws RunTimeException
     */
    public static function run_all_initializations(): array
    {
        self::register_routes();
        self::register_frontend_hooks();
        self::register_model_frontend_mappings();
        return ['register_routes','register_frontend_hooks','register_model_frontend_mappings'];
    }


    /**
     * @throws RunTimeException
     */
    public static function register_routes(): void
    {
        $FrontendRouter = self::get_service('FrontendRouter');
        $additional = [
            'name'  => 'Catalog',
            'meta' => [
                'in_navigation' => TRUE, //to be shown in the admin navigation
                //'additional_template' => '@GuzabaPlatform.Catalog/CatalogNavigationHook.vue',//here the list of classes will be expanded
            ],
        ];
        $FrontendRouter->{'/admin'}->add('catalog', '@GuzabaPlatform.Catalog/CatalogAdmin.vue' ,$additional);

        $additional = [
            'name'  => 'Catalog Group',
        ];
        //$FrontendRouter->{'/admin'}->add('catalog/*', '@GuzabaPlatform.Catalog/CatalogAdmin.vue', $additional);// use with this.$route.params.pathMatch
        $FrontendRouter->{'/admin'}->add('catalog/:catalog_category_uuid', '@GuzabaPlatform.Catalog/CatalogAdmin.vue', $additional);// use with this.$route.params.catalog_category_uuid
    }

    /**
     * Adds a hook to Navigation/AddLink
     * @throws RunTimeException
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     */
    public static function register_frontend_hooks(): void
    {
        /** @var VueComponentHooks $FrontendHooks */
        $FrontendHooks = self::get_service('FrontendHooks');
        $FrontendHooks->add(
            '@GuzabaPlatform.Navigation/components/AddLink.vue',
            'AfterTabs',
            '@GuzabaPlatform.Catalog/components/hooks/guzaba-platform/navigation/AddLinkCategory.vue'
        );
    }

    public static function register_model_frontend_mappings(): void
    {
        /** @var ModelFrontendMap $ModelFrontendMap */
        $ModelFrontendMap = self::get_service('ModelFrontendMap');
        $ModelFrontendMap->add_view_mapping(Category::class, '@GuzabaPlatform.Catalog/ViewCategory.vue');
    }
}