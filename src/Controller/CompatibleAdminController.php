<?php

namespace EffectConnect\Marketplaces\Controller;

if (class_exists(\PrestaShopBundle\Controller\Admin\PrestaShopAdminController::class)) {
    // PS9+
    abstract class CompatibleAdminController extends \PrestaShopBundle\Controller\Admin\PrestaShopAdminController
    {
        /**
         * @param string $id
         * @param array $parameters
         * @return string
         */
        public function translate(string $id, array $parameters = []): string
        {
            return $this->trans($id, $parameters, 'Modules.Effectconnectmarketplaces.Admin');
        }
    }
} else {
    // PS8
    abstract class CompatibleAdminController extends \PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController
    {
        /**
         * @param string $id
         * @param array $parameters
         * @return string
         */
        public function translate(string $id, array $parameters = []): string
        {
            return $this->trans($id, 'Modules.Effectconnectmarketplaces.Admin', $parameters);
        }
    }
}