<?php
declare(strict_types=1);

namespace GuzabaPlatform\Catalog\Models;

use Azonmedia\Utilities\GeneralUtil;
use Guzaba2\Authorization\Exceptions\PermissionDeniedException;
use Guzaba2\Base\Exceptions\InvalidArgumentException;
use Guzaba2\Orm\Exceptions\RecordNotFoundException;
use Guzaba2\Orm\Exceptions\ValidationFailedException;
use Guzaba2\Orm\Interfaces\ValidationFailedExceptionInterface;
use Guzaba2\Translator\Translator as t;
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
        //'route'                 => '/admin/catalog/category',//to be used for editing and deleting
        'route'                 => '/catalog/category',//to be used for editing and deleting

        'object_name_property'  => 'catalog_category_name',//required by BaseActiveRecord::get_object_name_property()

        'automate_slug'         => true,//should a slug (alias) be generated automatically if it is empty

        //'item_class'            => Item::class,
        'class_dependencies'        => [ //dependencies on other classes
            //intefaces => implementation
            ItemInterface::class    => Item::class,
        ],
    ];

    protected const CONFIG_RUNTIME = [];

    /**
     * To be used when the parent page group is to be set from public source (front-end)
     * Otherwise parent_catalog_category_id can be used
     * @var ?string
     */
    public ?string $parent_catalog_category_uuid = NULL;

    /**
     * Contains the primary object alias. An object may have more than one alias.
     * @var ?string
     */
    public ?string $catalog_category_slug = NULL;

    protected function _after_read(): void
    {
        if ($this->parent_catalog_category_id) {
            $ParentCategory = new static($this->parent_catalog_category_id);
            $this->parent_catalog_category_uuid = $ParentCategory->get_uuid();
        }

        //the property may be NULL not because it is not set/initialized but because it was explicitely set to NULL on another instance
        if ($this->catalog_category_slug === null && !$this->is_property_modified('catalog_category_slug')) {
            $this->catalog_category_slug = $this->get_alias();
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws ValidationFailedException
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\LogicException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \Guzaba2\Kernel\Exceptions\ConfigurationException
     * @throws \ReflectionException
     */
    protected function _before_write(): void
    {

        if (!$this->parent_catalog_category_id) {
            if ($this->parent_catalog_category_uuid) {
                if (GeneralUtil::is_uuid($this->parent_catalog_category_uuid)) {
                    try {
                        $ParentCategory = new static($this->parent_catalog_category_uuid);
                        $this->parent_catalog_category_id = $ParentCategory->get_id();
                    } catch (RecordNotFoundException $Exception) {
                        throw new ValidationFailedException($this, 'parent_catalog_category_uuid', sprintf(t::_('There is no category with the provided UUID %s.'), $this->parent_catalog_category_uuid) );
                    } catch (PermissionDeniedException $Exception) {
                        throw new ValidationFailedException($this, 'parent_catalog_category_uuid', sprintf(t::_('You are not allowed to read the category with UUID %s.'), $this->parent_catalog_category_uuid) );
                    }
                } else {
                    throw new ValidationFailedException($this, 'parent_catalog_category_uuid', sprintf(t::_('The provided parent category UUID %s is not a valid UUID.'), $this->parent_catalog_category_uuid) );
                }
            } else {
                $this->parent_catalog_category_id = NULL;
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws RecordNotFoundException
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\LogicException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \Guzaba2\Kernel\Exceptions\ConfigurationException
     * @throws \ReflectionException
     */
    protected function _after_write(): void
    {
        if (self::CONFIG_RUNTIME['automate_slug']) {
            if ($this->catalog_category_slug === null) {
                $this->catalog_category_slug = self::convert_to_slug($this->catalog_category_name);
            }
        }


        if ($this->is_property_modified('catalog_category_slug')) {
            $original_slug = $this->get_property_original_value('catalog_category_slug');
            if ($original_slug) {
                $this->delete_alias($original_slug);
            }
            if ($this->catalog_category_slug) {
                $this->add_alias($this->catalog_category_slug);
            }
        }
    }

    /**
     * @param int|null $parent_catalog_category_id
     * @param string $catalog_category_name
     * @return Category
     * @throws InvalidArgumentException
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\LogicException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \Guzaba2\Kernel\Exceptions\ConfigurationException
     * @throws \ReflectionException
     */
    public static function create(?int $parent_catalog_category_id, string $catalog_category_name): self
    {
        $Category = new static();
        $Category->parent_catalog_category_id = $parent_catalog_category_id;
        $Category->catalog_category_name = $catalog_category_name;
        $Category->write();
        return $Category;
    }

    /**
     * Returns an associative array with category UUID=>name with the path to this category
     * @return array
     */
    public function get_path(): array
    {
        $path = [];
        $Category = $this;
        do {
            $path[$Category->get_uuid()] = $Category->catalog_category_name;
            $Category = $Category->get_parent_category();
        } while ($Category);
        $path = array_reverse($path);
        return $path;
    }

    /**
     * @return CategoryInterface|null
     * @throws InvalidArgumentException
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\LogicException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \Guzaba2\Kernel\Exceptions\ConfigurationException
     * @throws \ReflectionException
     */
    public function get_parent_category(): ?CategoryInterface
    {
        $ret = null;
        if ($this->parent_catalog_category_id) {
            $ret = new static($this->parent_catalog_category_id);
        }
        return $ret;
    }


    protected function _validate_parent_catalog_category_id(): ?ValidationFailedExceptionInterface
    {
        //check the parent category exists

        return null;
    }

    /**
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \ReflectionException
     */
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
     * @return ItemInterface[]
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

    /**
     * @return CategoryInterface[]
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \ReflectionException
     */
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