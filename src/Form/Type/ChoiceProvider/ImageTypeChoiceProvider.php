<?php

namespace EffectConnect\Marketplaces\Form\Type\ChoiceProvider;

use EffectConnect\Marketplaces\Helper\ProductImageTypeHelper;
use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ImageTypeChoiceProvider
 * @package EffectConnect\Marketplaces\Form\Type\ChoiceProvider
 */
final class ImageTypeChoiceProvider implements FormChoiceProviderInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $_translator;

    /**
     * EanChoiceProvider constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->_translator = $translator;
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        $choices = [
            $this->_translator->trans('Use default (image that is displayed on the product page in the backoffice)', [], 'Modules.Effectconnectmarketplaces.Admin') => '',
        ];

        foreach (ProductImageTypeHelper::getProductImagesTypes() as $imageType) {
            $translation = $this->_translator->trans('Use generated image type %s (%s px x %s px)', [$imageType['name'], $imageType['width'], $imageType['height']], 'Modules.Effectconnectmarketplaces.Admin');
            $choices[$translation] = $imageType['id_image_type'];
        }

        return $choices;
    }
}
