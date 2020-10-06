<?php
declare(strict_types=1);

namespace GuzabaPlatform\Catalog;

use GuzabaPlatform\Platform\Application\BaseActiveRecord;
use GuzabaPlatform\Tags\Base\Interfaces\TagInterface;

/**
 * Class Category
 * @package GuzabaPlatform\Catalog
 *
 * @property int        catalog_category_id
 * @property int|null   parent_catalog_category_id
 * @property string     catalog_category_name
 */
class Category extends BaseActiveRecord implements Base\Interfaces\Category
{

    protected const CONFIG_DEFAULTS = [
        'main_table' => 'catalog_categories',
        'route' => '/admin/catalog/category',//to be used for editing and deleting

        'object_name_property'  => 'catalog_category_name',//required by BaseActiveRecord::get_object_name_property()
    ];

    protected const CONFIG_RUNTIME = [];

    public static function create(?int $parent_catalog_category_id, string $catalog_category_name): self
    {
        $Category = new static();
        $Category->parent_catalog_category_id = $parent_catalog_category_id;
        $Category->catalog_category_name = $catalog_category_name;
        $Category->write();
        return $Category;
    }

    protected function _before_write(): void
    {
        //check the parent category exists
    }

    public function get_items(): iterable
    {
        // TODO: Implement get_items() method.
    }

    /**
     * Returns a collection\array of Tag.
     * @return iterable
     */
    public function get_tags(): iterable
    {
        // TODO: Implement get_tags() method.
    }

    /**
     * Returns FALSE if the object already has the provided tag.
     * @param TagInterface $TagInterface
     * @return bool
     */
    public function add_tag(TagInterface $TagInterface): bool
    {
        // TODO: Implement add_tag() method.
    }

    /**
     * Returns FALSE if the object does not have the provided tag.
     * @param TagInterface $TagInterface
     * @return bool
     */
    public function remove_tag(TagInterface $TagInterface): bool
    {
        // TODO: Implement remove_tag() method.
    }
}