<?php

$installer = $this;
$installer->startSetup();
$installer->run(" ALTER TABLE {$this->getTable('recipe_main_category')} ADD `image` VARCHAR( 250 ) NOT NULL AFTER `code` 
    ");
$installer->endSetup();
