<?php


$installer = $this;

$installer->startSetup();

$installer->run("

    ALTER TABLE `{$this->getTable('quotation')}` 
    ADD address_id 	int(10) NOT NULL;

");

$installer->endSetup();
