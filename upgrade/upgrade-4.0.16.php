<?php

use EffectConnect\Marketplaces\Model\Connection;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_0_16($object)
{
    return Connection::addDbFieldsActiveProcesses();
}
