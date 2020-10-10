<?php
declare(strict_types=1);

namespace GuzabaPlatform\Catalog\Models;


use Guzaba2\Base\Base;
use GuzabaPlatform\Catalog\Base\Interfaces\CategoryInterface;

class Categories extends Base
{

    protected const CONFIG_DEFAULTS = [
        //'category_class'            => Category::class,
        'class_dependencies'        => [ //dependencies on other classes
            //intefaces => implementation
            CategoryInterface::class    => Category::class,
        ],
    ];

    protected const CONFIG_RUNTIME = [];

    /**
     * @param int|null $parent_catalog_category_id
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public static function get_by_catalog_category_id(?int $parent_catalog_category_id, int $offset = 0, int $limit = 0): array
    {
        //the below is also valid...
        //return static::CONFIG_RUNTIME['class_dependencies'][CategoryInterface::class]::get_data_by( ['parent_catalog_category_id' => $parent_catalog_category_id], $offset, $limit, $use_like = FALSE, 'catalog_category_name');
        //but lets do it the traditional way
        $category_class = static::CONFIG_RUNTIME['class_dependencies'][CategoryInterface::class];
        return $category_class::get_data_by( ['parent_catalog_category_id' => $parent_catalog_category_id], $offset, $limit, $use_like = FALSE, 'catalog_category_name');
    }

    /**
     * @param string|null $parent_catalog_category_uuid
     * @param int $offset
     * @param int $limit
     * @return array
     */
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