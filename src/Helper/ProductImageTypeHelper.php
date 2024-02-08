<?php

namespace EffectConnect\Marketplaces\Helper;

use ImageType;
use PrestaShopDatabaseException;

/**
 * Class ProductImageTypeHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class ProductImageTypeHelper
{
    /**
     * @return array
     */
    public static function getProductImagesTypes()
    {
        try {
            return ImageType::getImagesTypes('products');
        } catch (PrestaShopDatabaseException $e) {
            return [];
        }
    }

    /**
     * @param int $imageTypeId
     * @return string|null
     */
    public static function getProductImageTypeById(int $imageTypeId)
    {
        $imageType = null;
        $types = self::getProductImagesTypes();
        foreach ($types as $type) {
            if (is_array($type) && isset($type['id_image_type']) && intval($type['id_image_type']) === $imageTypeId) {
                $imageType = $type['name'] ?? null;
                break;
            }
        }
        return $imageType;
    }
}