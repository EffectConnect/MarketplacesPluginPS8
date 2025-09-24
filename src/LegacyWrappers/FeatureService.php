<?php

namespace EffectConnect\Marketplaces\LegacyWrappers;

use Feature;
use FeatureValue;

/**
 * In future versions of PrestaShop we might have to use other methods of fetching the correct data.
 */
class FeatureService
{
    /**
     * @param int $idLang
     * @return array
     */
    public function getFeatures(int $idLang): array
    {
        return Feature::getFeatures($idLang);
    }

    /**
     * @param $idLang
     * @param $idFeature
     * @param $custom
     * @return array
     */
    public static function getFeatureValuesWithLang($idLang, $idFeature, $custom): array
    {
        return FeatureValue::getFeatureValuesWithLang($idLang, $idFeature, $custom);
    }
}