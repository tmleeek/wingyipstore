<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$installer->getTable('shipping_productmatrix')}` ADD `delivery_method` VARCHAR(255) NOT NULL COMMENT 'Delivery Method' AFTER `notes`;");


$installer->endSetup();
