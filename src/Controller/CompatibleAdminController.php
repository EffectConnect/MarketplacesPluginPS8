<?php

namespace EffectConnect\Marketplaces\Controller;

if (class_exists(\PrestaShopBundle\Controller\Admin\PrestaShopAdminController::class)) {
    // PS9+
    abstract class CompatibleAdminController extends \PrestaShopBundle\Controller\Admin\PrestaShopAdminController
    {
    }
} else {
    // PS8
    abstract class CompatibleAdminController extends \PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController
    {
    }
}