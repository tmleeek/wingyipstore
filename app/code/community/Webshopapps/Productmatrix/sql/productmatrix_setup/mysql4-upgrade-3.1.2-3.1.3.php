<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$installer->getTable('sales_flat_quote_shipping_rate')}` ADD `shipping_sku` VARCHAR(255) NOT NULL COMMENT 'ShippingSKU';");
         
$installer->endSetup();