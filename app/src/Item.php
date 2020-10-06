<?php
declare(strict_types=1);

namespace GuzabaPlatform\Catalog;

use Guzaba2\Orm\Exceptions\RecordNotFoundException;
use Guzaba2\Orm\Exceptions\ValidationFailedException;
use Guzaba2\Orm\Interfaces\ValidationFailedExceptionInterface;
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
class Item extends BaseActiveRecord implements Base\Interfaces\Item
{

    protected const CONFIG_DEFAULTS = [
        'main_table'            => 'catalog_items',
        'route'                 => '/admin/catalog/item',//to be used for editing and deleting

        'object_name_property'  => 'catalog_item_name',//required by BaseActiveRecord::get_object_name_property()

        'store_relative_base'       => '/public/images/products',// this is relative to the application diretory -> ./app/public/assets
        'document_root_assets_dir'  => '/images/products',
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

    public function add_image(string $image_url): void
    {
        $File = File::download_file(self::CONFIG_RUNTIME['store_relative_base'], $image_url);
        $Image = Image::create($this, $File->get_absolute_path());
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