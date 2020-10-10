<?php
declare(strict_types=1);

namespace GuzabaPlatform\Catalog\Models;

use Guzaba2\Orm\Interfaces\ValidationFailedExceptionInterface;
use GuzabaPlatform\Catalog\Base\Interfaces\CategoryInterface;
use GuzabaPlatform\Platform\Application\BaseActiveRecord;
use GuzabaPlatform\Tags\Base\Interfaces\TagInterface;
use GuzabaPlatform\Catalog\Base\Interfaces\ItemInterface;

/**
 * Class Category
 * @package GuzabaPlatform\Catalog
 *
 * @property int        catalog_category_id
 * @property int|null   parent_catalog_category_id
 * @property string     catalog_category_name
 */
class Category extends BaseActiveRecord implements CategoryInterface
{

    protected const CONFIG_DEFAULTS = [
        'main_table'            => 'catalog_categories',
        'route'                 => '/admin/catalog/category',//to be used for editing and deleting

        'object_name_property'  => 'catalog_category_name',//required by BaseActiveRecord::get_object_name_property()

        //'item_class'            => Item::class,
        'class_dependencies'        => [ //dependencies on other classes
            //intefaces => implementation
            ItemInterface::class    => Item::class,
        ],
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

    protected function _validate_parent_catalog_category_id(): ?ValidationFailedExceptionInterface
    {
        //check the parent category exists

        return null;
    }

    protected function _before_delete(): void
    {
        //delete all items in this category
        foreach ($this->get_items() as $Item) {
            $Item->delete();
        }
        //delete all sub-categories
        foreach ($this->get_categories() as $Category) {
            $Category->delete();
        }
    }

    /**
     * @return iterable
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \ReflectionException
     */
    public function get_items(): iterable
    {
        $item_class = static::CONFIG_RUNTIME['class_dependencies'][ItemInterface::class];
        return $item_class::get_by( ['catalog_category_id' => $this->get_id() ] );
    }

    public function get_categories(): iterable
    {
        return static::get_by( ['parent_catalog_category_id' => $this->get_id() ] );
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