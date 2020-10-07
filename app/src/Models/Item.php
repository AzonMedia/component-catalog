<?php
declare(strict_types=1);

namespace GuzabaPlatform\Catalog\Models;

use Guzaba2\Orm\Exceptions\RecordNotFoundException;
use Guzaba2\Orm\Exceptions\ValidationFailedException;
use Guzaba2\Orm\Interfaces\ValidationFailedExceptionInterface;
use Guzaba2\Orm\Transaction;
use Guzaba2\Transaction\Interfaces\TransactionManagerInterface;
use Guzaba2\Translator\Translator as t;
use GuzabaPlatform\Assets\Models\File;
use GuzabaPlatform\Images\Image;
use GuzabaPlatform\Platform\Application\BaseActiveRecord;
use GuzabaPlatform\Tags\Base\Interfaces\TagInterface;

/**
 * Class Item
 * @package GuzabaPlatform\Catalog
 *
 * @property int catalog_item_id
 * @property int catalog_category_id
 * @property string catalog_item_name
 * @property float catalog_item_price
 */
class Item extends BaseActiveRecord implements \GuzabaPlatform\Catalog\Base\Interfaces\Item
{

    protected const CONFIG_DEFAULTS = [
        'main_table'            => 'catalog_items',
        'route'                 => '/admin/catalog/item',//to be used for editing and deleting

        'object_name_property'  => 'catalog_item_name',//required by BaseActiveRecord::get_object_name_property()

        'images_dir'            => 'products',//relative to the File::CONFIG_RUNTIME['store_relative_base']
    ];

    protected const CONFIG_RUNTIME = [];


    public static function create(int $catalog_category_id, string $catalog_item_name, float $catalog_item_price): self
    {
        $Item = new static();
        $Item->catalog_category_id = $catalog_category_id;
        $Item->catalog_item_name = $catalog_item_name;
        $Item->catalog_item_price = $catalog_item_price;
        $Item->write();
        return $Item;
    }

    public static function get_images_dir(): string
    {
        return self::CONFIG_RUNTIME['images_dir'];
    }

    public function add_image(string $image_url): void
    {
        $relative_path = self::CONFIG_RUNTIME['images_dir'].'/'.$this->get_uuid();//lets put the product images in individual subfolder (in case of image name collisions)
        $File = File::download_file($relative_path, $image_url);
        $this->add_image_from_file($File);
    }

    public function add_image_from_file(File $File): void
    {
        //Image::create($this, $File->get_absolute_path());
        //better use relative paths as the project needs to be portable
        //Image::create($this, $File->get_relative_path());
        //use absolute path as the image can not be deleted after that (the Image class has no knowledge of any storage)
        Image::create($this, $File->get_absolute_path());
    }

    public function get_images(): array
    {
        return Image::get_by( ['image_class_id' => self::get_class_id(), 'image_object_id' => $this->get_id() ] );
    }

    public function delete_images(): void
    {
        foreach ($this->get_images() as $Image) {
            $Image->delete();
        }
    }

    protected function _before_delete(): void
    {
        //if there delete transaction is successful delete the images
        //object the transaction and add a on commit event
        //it is better to do it this way insted of deleting the images in _after_delete but without taking into account the transaction.
        //there is no way to obtain the current transaction (this is intentional)
        //instead a nested one is started
        $Transaction = ActiveRecord::new_transaction($TR);
        $Transaction->begin();
        $Transaction->add_callback('_after_commit', function(): void
        {
            $this->delete_images();
        });
        $Transaction->commit();
    }

    protected function _validate_catalog_category_id(): ?ValidationFailedExceptionInterface
    {
        if (!$this->catalog_category_id) {
            return new ValidationFailedException($this, 'catalog_category_id', sprintf(t::_('No catalog_category_id provided.')));
        }
        try {
            $Category = new Category($this->catalog_category_id);
        } catch (RecordNotFoundException $Exception) {
            $message = sprintf(t::_('There is no %1$s with catalog_category_id %2$s does not exist.'), Category::class, $this->catalog_category_id );
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
}