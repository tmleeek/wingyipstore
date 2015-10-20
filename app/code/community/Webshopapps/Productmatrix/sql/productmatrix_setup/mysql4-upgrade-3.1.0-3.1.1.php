<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$installer->getTable('shipping_productmatrix')}` ADD `shipping_sku` VARCHAR(255) NOT NULL COMMENT 'ShippingSKU' AFTER `delivery_method`;");
         
$installer->endSetup();