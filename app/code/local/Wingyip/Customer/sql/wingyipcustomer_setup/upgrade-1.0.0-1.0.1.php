<?php
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE  ".$this->getTable('sales/quote_address')." ADD  `mobile_number` varchar(255) NOT NULL
");
 

$installer->run("
ALTER TABLE  ".$this->getTable('sales/order_address')." ADD  `mobile_number` varchar(255) NOT NULL
");

$installer->endSetup();