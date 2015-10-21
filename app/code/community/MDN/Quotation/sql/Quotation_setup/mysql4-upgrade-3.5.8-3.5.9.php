<?php


$installer = $this;

$installer->startSetup();

$installer->run("

    ALTER TABLE `{$this->getTable('quotation')}` 
    ADD shipping_sku   VARCHAR(255);
    
    ALTER TABLE `{$this->getTable('quotation')}` 
    ADD shipping_label VARCHAR(255);
    
    ALTER TABLE `{$this->getTable('quotation')}` 
    ADD shipping_cost  decimal(10,2);

");

$installer->endSetup();
