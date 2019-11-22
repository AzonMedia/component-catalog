<?php

namespace Azonmedia\Catalog;

use Azonmedia\Tags\Base\Interfaces\TagInterface;

class Item implements Base\Interfaces\Item
{

    /**
     * A collection/array of Category
     * @return iterable
     */
    public function get_categories(): iterable
    {
        // TODO: Implement get_categories() method.
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