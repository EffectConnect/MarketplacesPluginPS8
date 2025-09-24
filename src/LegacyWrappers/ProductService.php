<?php

namespace EffectConnect\Marketplaces\LegacyWrappers;

use Product;

/**
 * In future versions of PrestaShop we might have to use other methods of fetching the correct data.
 */
class ProductService
{
    /**
     * @param int $idProduct
     * @param int $idLang
     * @param int $idShop
     * @return Product
     */
    public function getProduct(int $idProduct, int $idLang, int $idShop): Product
    {
        return new Product($idProduct, true, $idLang, $idShop);
    }
}