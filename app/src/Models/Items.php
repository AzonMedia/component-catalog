<?php

declare(strict_types=1);

namespace GuzabaPlatform\Catalog\Models;

use Guzaba2\Base\Base;
use GuzabaPlatform\Catalog\Base\Interfaces\CategoryInterface;
use GuzabaPlatform\Catalog\Base\Interfaces\ItemInterface;

/**
 * Class Items
 * @package GuzabaPlatform\Catalog\Models
 */
class Items extends Base
{

    protected const CONFIG_DEFAULTS = [
        //'category_class'            => Category::class,
        'class_dependencies'        => [ //dependencies on other classes
            //intefaces => implementation
            ItemInterface::class        => Item::class,
            CategoryInterface::class    => Category::class,
        ],
    ];

    protected const CONFIG_RUNTIME = [];

    public static function get_by_catalog_category_id(?int $catalog_category_id, int $offset = 0, int $limit = 0): array
    {
        $item_class = static::CONFIG_RUNTIME['class_dependencies'][ItemInterface::class];
        return $item_class::get_data_by( ['catalog_category_id' => $catalog_category_id], $offset, $limit, $use_like = FALSE, 'catalog_item_name');
    }
    public static function get_by_catalog_category_uuid(?string $parent_catalog_category_uuid, int $offset = 0, int $limit = 0): array
    {
        $category_class = static::CONFIG_RUNTIME['class_dependencies'][CategoryInterface::class];
        if ($parent_catalog_category_uuid) {
            $Category = new $category_class($parent_catalog_category_uuid);
            $parent_catalog_category_id = $Category->get_id();
        } else {
            $parent_catalog_category_id = NULL;
        }
        return static::get_by_catalog_category_id($parent_catalog_category_id, $offset, $limit);
    }
}