<?php

declare(strict_types=1);

namespace GuzabaPlatform\Catalog\Controllers;

use Azonmedia\Utilities\ArrayUtil;
use CacheGoldStore\Store\Models\Category;
use Guzaba2\Authorization\CurrentUser;
use Guzaba2\Http\Method;
use GuzabaPlatform\Catalog\Base\Interfaces\CategoriesInterface;
use GuzabaPlatform\Catalog\Base\Interfaces\CategoryInterface;
use GuzabaPlatform\Catalog\Base\Interfaces\ItemInterface;
use GuzabaPlatform\Catalog\Base\Interfaces\ItemsInterface;
use GuzabaPlatform\Catalog\Models\Categories;
use GuzabaPlatform\Catalog\Models\Item;
use GuzabaPlatform\Platform\Application\BaseController;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Items
 * @package GuzabaPlatform\Catalog\Controllers
 */
class Items extends BaseController
{
    protected const CONFIG_DEFAULTS = [
        'routes'        => [
            '/admin/catalog' => [
                Method::HTTP_GET => [self::class, 'main']
            ],
            '/admin/catalog/{catalog_category_uuid}' => [
                Method::HTTP_GET => [self::class, 'main']
            ],
            '/admin/catalog/categories' => [
                Method::HTTP_GET => [self::class, 'categories'] //not used by the front end
            ],
            '/admin/catalog/items' => [
                Method::HTTP_GET => [self::class, 'items'] // not used by the front end
            ],
        ],
        'services'      => [
            'CurrentUser'
        ],
        'class_dependencies'        => [ //dependencies on other classes
            //intefaces => implementation
            ItemInterface::class        => Item::class,
            CategoryInterface::class    => Category::class,
            ItemsInterface::class       => Items::class,
            CategoriesInterface::class  => Categories::class,
        ],
    ];

    protected const CONFIG_RUNTIME = [];

    /**
     * @param string|null $catalog_category_uuid
     * @return ResponseInterface
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \ReflectionException
     */
    public function main(?string $catalog_category_uuid = null): ResponseInterface
    {
        $categories_class = self::CONFIG_RUNTIME['class_dependencies'][CategoriesInterface::class];
        $items_class = self::CONFIG_RUNTIME['class_dependencies'][ItemsInterface::class];
        $category_class = self::CONFIG_RUNTIME['class_dependencies'][CategoryInterface::class];

        $struct = [];
        /** @var CurrentUser $CurrentUser */
        $CurrentUser = self::get_service('CurrentUser');
        $date_time_format = $CurrentUser->get()->get_date_time_format();

        //lets expose the products & Categories IDs as these are needed for the settings (which product ID is tobe featured)
        //$struct['categories'] = ArrayUtil::frontify($categories_class::get_by_catalog_category_uuid($catalog_category_uuid),  $date_time_format );
        //$struct['items'] = ArrayUtil::frontify( $items_class::get_by_catalog_category_uuid($catalog_category_uuid), $date_time_format );
        $struct['categories'] = $categories_class::get_by_catalog_category_uuid($catalog_category_uuid);
        $struct['items'] = $items_class::get_by_catalog_category_uuid($catalog_category_uuid);
        if ($catalog_category_uuid) {
            $Category = new $category_class($catalog_category_uuid);
            $struct['category_path'] = $Category->get_path();
        } else {
            $struct['category_path'] = [];
        }

        return self::get_structured_ok_response($struct);
    }

    /**
     * @param string|null $catalog_category_uuid
     * @return ResponseInterface
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \ReflectionException
     */
    public function items(?string $catalog_category_uuid = NULL): ResponseInterface
    {
        $items_class = self::CONFIG_RUNTIME['class_dependencies'][ItemsInterface::class];

        $struct = [];
        /** @var CurrentUser $CurrentUser */
        $CurrentUser = self::get_service('CurrentUser');
        $date_time_format = $CurrentUser->get()->get_date_time_format();
        $struct['items'] = ArrayUtil::frontify( $items_class::get_by_catalog_category_uuid($catalog_category_uuid), $date_time_format );
        return self::get_structured_ok_response($struct);
    }

    /**
     * @param string|null $catalog_category_uuid
     * @return ResponseInterface
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \ReflectionException
     */
    public function categories(?string $catalog_category_uuid = NULL): ResponseInterface
    {
        $categories_class = self::CONFIG_RUNTIME['class_dependencies'][CategoriesInterface::class];

        $struct = [];
        /** @var CurrentUser $CurrentUser */
        $CurrentUser = self::get_service('CurrentUser');
        $date_time_format = $CurrentUser->get()->get_date_time_format();
        $struct['categories'] = ArrayUtil::frontify($categories_class::get_by_catalog_category_uuid($catalog_category_uuid),  $date_time_format );
        return self::get_structured_ok_response($struct);
    }
}