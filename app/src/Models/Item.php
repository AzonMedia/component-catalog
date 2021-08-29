<?php
declare(strict_types=1);

namespace GuzabaPlatform\Catalog\Models;

use Azonmedia\Utilities\GeneralUtil;
use Guzaba2\Authorization\Exceptions\PermissionDeniedException;
use Guzaba2\Base\Exceptions\RunTimeException;
use Guzaba2\Kernel\Kernel;
use Guzaba2\Orm\Exceptions\RecordNotFoundException;
use Guzaba2\Orm\Exceptions\ValidationFailedException;
use Guzaba2\Orm\Interfaces\ValidationFailedExceptionInterface;
use Guzaba2\Orm\Transaction;
use Guzaba2\Transaction\Interfaces\TransactionManagerInterface;
use Guzaba2\Translator\Translator as t;
use GuzabaPlatform\Assets\Models\File;
use GuzabaPlatform\Catalog\Base\Interfaces\CategoryInterface;
use GuzabaPlatform\Images\Image;
use GuzabaPlatform\Images\Interfaces\ImageInterface;
use GuzabaPlatform\Platform\Application\BaseActiveRecord;
use GuzabaPlatform\Tags\Base\Interfaces\TagInterface;
use GuzabaPlatform\Catalog\Base\Interfaces\ItemInterface;
use Psr\Log\LogLevel;

/**
 * Class Item
 * @package GuzabaPlatform\Catalog
 *
 * @property int catalog_item_id
 * @property int catalog_category_id
 * @property string catalog_item_name
 * @property string catalog_item_description
 * @property float catalog_item_price
 */
class Item extends BaseActiveRecord implements ItemInterface
{

    protected const CONFIG_DEFAULTS = [
        'main_table'            => 'catalog_items',
        //'route'                 => '/admin/catalog/item',//to be used for editing and deleting
        //without /admin/ as this is not a strictly admin component
        //also the view item should be public
        'route'                 => '/catalog/item',//to be used for editing and deleting

        'object_name_property'  => 'catalog_item_name',//required by BaseActiveRecord::get_object_name_property()

        'images_dir'            => 'products',//relative to the File::CONFIG_RUNTIME['store_relative_base']

        'automate_slug'         => true,//should a slug (alias) be generated automatically if it is empty

        //'category_class'        => Category::class,
        //'image_class'           => Image::class,
        'class_dependencies'        => [ //dependencies on other classes
            //interface                 => implementation
            CategoryInterface::class    => Category::class,
            ImageInterface::class       => Image::class,
        ],
        'services'             => [
            'GuzabaPlatform',
        ]
    ];

    protected const CONFIG_RUNTIME = [];

    /**
     * Contains an indexed array with all the images.
     * Loaded with get_images() from _after_read()
     * @var array
     */
    public array $images = [];

    /**
     * Contains the primary object alias. A catalog item (and any other object) may have more than one alias.
     * @var ?string
     */
    public ?string $catalog_item_slug = null;

    /**
     * To be used when the category is to be set from public source (front-end)
     * Otherwise catalog_category_id can be used
     * @var ?string
     */
    public ?string $catalog_category_uuid = NULL;

//    /**
//     * Prevents setting the images property. It is readonly.
//     * @param array $images
//     * @return array
//     * @throws RunTimeException
//     * @throws \Azonmedia\Exceptions\InvalidArgumentException
//     */
//    protected function _before_set_images(array $images): array
//    {
//        throw new RunTimeException(sprintf(t::_('It is not supported to set the images property on %1$s.'), get_class($this) ));
//    }

    protected function _after_read(): void
    {


        $category_class = static::CONFIG_RUNTIME['class_dependencies'][CategoryInterface::class];
        if ($this->catalog_category_id && !$this->is_property_modified('catalog_category_uuid') ) {
            $Category = new $category_class($this->catalog_category_id);
            $this->catalog_category_uuid = $Category->get_uuid();
        }

        if (!$this->images) {
            $GuzabaPlatform = self::get_service('GuzabaPlatform');
            $public_dir = $GuzabaPlatform->get_public_dir();
            $images = $this->get_images();
            $images_paths = [];
            foreach ($images as $Image) {
                //$this->images[] = realpath($Image->image_path);
                //better store the public asset path (accessible through browser), not the absolute image path
                //or both - as associative array
                $realpath = realpath($Image->image_path);
                //$this->images[$realpath] = str_replace($public_dir, '', $realpath);
                if ($realpath === false) {
                    $message = sprintf(t::_('realpath() returned false for image %1$s. This can happen when the content has been moved to another directory.'), $Image->image_path);
                    Kernel::log($message, LogLevel::WARNING);
                } else {
                    $this->images[] = str_replace($public_dir, '', $realpath);
                }

            }
        }

        if ($this->catalog_item_slug === null && !$this->is_property_modified('catalog_item_slug')) {
            $this->catalog_item_slug = $this->get_alias();
        }
    }

    /**
     * Sets the images to this object by using the provided array of absolute paths
     * @param string[] $images Array with absolute path to images
     * @throws RunTimeException
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \ReflectionException
     */
    public function set_images(array $images): void
    {
        $GuzabaPlatform = self::get_service('GuzabaPlatform');
        $public_dir = $GuzabaPlatform->get_public_dir();
        foreach ($images as $image) {
            $realpath = realpath($image);
            $this->images[] = str_replace($public_dir, '', $realpath);
        }
    }

    protected function _before_write(): void
    {

        if (!$this->catalog_category_id) {
            if ($this->catalog_category_uuid) {
                if (GeneralUtil::is_uuid($this->catalog_category_uuid)) {
                    try {
                        $category_class = static::CONFIG_RUNTIME['class_dependencies'][CategoryInterface::class];
                        $Category = new $category_class($this->catalog_category_uuid);
                        $this->catalog_category_id = $Category->get_id();
                    } catch (RecordNotFoundException $Exception) {
                        throw new ValidationFailedException($this, 'catalog_category_uuid', sprintf(t::_('There is no category with the provided UUID %s.'), $this->catalog_category_uuid) );
                    } catch (PermissionDeniedException $Exception) {
                        throw new ValidationFailedException($this, 'catalog_category_uuid', sprintf(t::_('You are not allowed to read the category with UUID %s.'), $this->catalog_category_uuid) );
                    }
                    //if (!)
                } else {
                    throw new ValidationFailedException($this, 'catalog_category_uuid', sprintf(t::_('The provided category UUID %s is not a valid UUID.'), $this->catalog_category_uuid) );
                }
            } else {
                $this->catalog_category_id = NULL;
            }
        }
    }

    protected function _after_write(): void
    {

        if (self::CONFIG_RUNTIME['automate_slug']) {
            if ($this->catalog_item_slug === null) {
                $this->catalog_item_slug = self::convert_to_slug($this->catalog_item_name);
            }
        }

        if ($this->is_property_modified('catalog_item_slug')) {
            $original_slug = $this->get_property_original_value('catalog_item_slug');
            if ($original_slug) {
                $this->delete_alias($original_slug);
            }
            if ($this->catalog_item_slug) {
                $this->add_alias($this->catalog_item_slug);
            }
        }

        if (!$this->catalog_item_slug) {
            //generate slug based on the product name
            //$slug = preg_replace('/[^a-zA-Z0-9]/', '-', $this->catalog_item_name);
            $slug = strtolower(preg_replace(['/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'], ['', '-', ''], $this->catalog_item_name));
            $this->add_alias($slug);
        }
    }

    /**
     * @param int $catalog_category_id
     * @param string $catalog_item_name
     * @param string $catalog_item_description
     * @param float $catalog_item_price
     * @return Item
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\LogicException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \Guzaba2\Kernel\Exceptions\ConfigurationException
     * @throws \ReflectionException
     */
    public static function create(int $catalog_category_id, string $catalog_item_name, string $catalog_item_description, float $catalog_item_price): static
    {
        $Item = new static();
        $Item->catalog_category_id = $catalog_category_id;
        $Item->catalog_item_name = $catalog_item_name;
        $Item->catalog_item_description = $catalog_item_description;
        $Item->catalog_item_price = $catalog_item_price;
        $Item->write();
        return $Item;
    }

    /**
     * Returns the images_dir from the class configuration
     * @return string
     */
    public static function get_images_dir(): string
    {
        return self::CONFIG_RUNTIME['images_dir'];
    }

    /**
     * @param string $image_url
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Azonmedia\Exceptions\RunTimeException
     */
    public function add_image(string $image_url): void
    {
        $relative_path = self::CONFIG_RUNTIME['images_dir'].'/'.$this->get_uuid();//lets put the product images in individual subfolder (in case of image name collisions)
        $File = File::download_file($relative_path, $image_url);
        $this->add_image_from_file($File);
    }

    /**
     * @param File $File
     */
    public function add_image_from_file(File $File): void
    {
        //Image::create($this, $File->get_absolute_path());
        //better use relative paths as the project needs to be portable
        //Image::create($this, $File->get_relative_path());
        //use absolute path as the image can not be deleted after that (the Image class has no knowledge of any storage)
        $image_class = static::CONFIG_RUNTIME['class_dependencies'][ImageInterface::class];
        $image_class::create($this, $File->get_absolute_path());
    }

    /**
     * @return array
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \ReflectionException
     */
    public function get_images(): array
    {
        $image_class = static::CONFIG_RUNTIME['class_dependencies'][ImageInterface::class];
        $ret = $image_class::get_by( [ 'image_class_id' => self::get_class_id(), 'image_object_id' => $this->get_id() ] );
        return $ret;
    }

    /**
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \ReflectionException
     */
    public function delete_images(): void
    {
        foreach ($this->get_images() as $Image) {
            $Image->delete();
        }
    }

    /**
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \ReflectionException
     */
    protected function _before_delete(): void
    {
        //if there delete transaction is successful delete the images
        //object the transaction and add a on commit event
        //it is better to do it this way insted of deleting the images in _after_delete but without taking into account the transaction.
        //there is no way to obtain the current transaction (this is intentional)
        //instead a nested one is started
        $Transaction = static::new_transaction($TR);
        $Transaction->begin();
        $Transaction->add_callback('_after_commit', function(): void
        {
            $this->delete_images();
        });
        $Transaction->commit();
    }

    /**
     * @return ValidationFailedExceptionInterface|null
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\RunTimeException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \ReflectionException
     */
    protected function _validate_catalog_category_id(): ?ValidationFailedExceptionInterface
    {
        if (!$this->catalog_category_id) {
            return new ValidationFailedException($this, 'catalog_category_id', sprintf(t::_('No catalog_category_id provided.')));
        }
        try {
            $category_class = static::CONFIG_RUNTIME['class_dependencies'][CategoryInterface::class];
            //$Category = new Category($this->catalog_category_id);
            $Category = new $category_class($this->catalog_category_id);
        } catch (RecordNotFoundException $Exception) {
            $message = sprintf(t::_('There is no %1$s with catalog_category_id %2$s.'), Category::class, $this->catalog_category_id );
            return new ValidationFailedException($this, 'catalog_category_id', $message);
        }
        return null;
    }

    protected function _validate_catalog_item_name(): ?ValidationFailedExceptionInterface
    {
        //TODO - the product name must be unique withing the category
        return null;
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

    public function get_category(): CategoryInterface
    {
        $category_class = static::CONFIG_RUNTIME['class_dependencies'][CategoryInterface::class];
        return new $category_class($this->catalog_category_id);
    }
}