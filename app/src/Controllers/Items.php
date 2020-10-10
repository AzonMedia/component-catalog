<?php

declare(strict_types=1);

namespace GuzabaPlatform\Catalog\Controllers;

use Azonmedia\Utilities\ArrayUtil;
use Guzaba2\Authorization\CurrentUser;
use Guzaba2\Http\Method;
use GuzabaPlatform\Catalog\Models\Categories;
use GuzabaPlatform\Platform\Application\BaseController;
use Psr\Http\Message\ResponseInterface;

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
        ]
    ];

    protected const CONFIG_RUNTIME = [];

    public function main(?string $catalog_category_uuid = null): ResponseInterface
    {
        $struct = [];
        /** @var CurrentUser $CurrentUser */
        $CurrentUser = self::get_service('CurrentUser');
        $date_time_format = $CurrentUser->get()->get_date_time_format();
        $struct['categories'] = ArrayUtil::frontify(Categories::get_by_catalog_category_uuid($catalog_category_uuid),  $date_time_format );
        $struct['items'] = ArrayUtil::frontify( \GuzabaPlatform\Catalog\Models\Items::get_by_catalog_category_uuid($catalog_category_uuid), $date_time_format );
        if ($catalog_category_uuid) {
            $PageGroup = new PageGroup($catalog_category_uuid);
            $struct['category_path'] = $PageGroup->get_path();
        } else {
            $struct['category_path'] = [];
        }

        return self::get_structured_ok_response($struct);
    }

    public function items(?string $catalog_category_uuid = NULL): ResponseInterface
    {
        $struct = [];
        /** @var CurrentUser $CurrentUser */
        $CurrentUser = self::get_service('CurrentUser');
        $date_time_format = $CurrentUser->get()->get_date_time_format();
        $struct['items'] = ArrayUtil::frontify( \GuzabaPlatform\Catalog\Models\Items::get_by_catalog_category_uuid($catalog_category_uuid), $date_time_format );
        return self::get_structured_ok_response($struct);
    }

    public function categories(?string $catalog_category_uuid = NULL): ResponseInterface
    {
        $struct = [];
        /** @var CurrentUser $CurrentUser */
        $CurrentUser = self::get_service('CurrentUser');
        $date_time_format = $CurrentUser->get()->get_date_time_format();
        $struct['categories'] = ArrayUtil::frontify(Categories::get_by_catalog_category_uuid($catalog_category_uuid),  $date_time_format );
        return self::get_structured_ok_response($struct);
    }
}