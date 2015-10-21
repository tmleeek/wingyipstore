<?php

$installer = $this;
$installer->startSetup();
$installer->run(" ALTER TABLE {$this->getTable('recipe_main_category')} ADD `meta_title` Text NOT NULL AFTER `image`  ");
$installer->run(" ALTER TABLE {$this->getTable('recipe_main_category')} ADD `meta_keyword` Text NOT NULL AFTER `meta_title`  ");
$installer->run(" ALTER TABLE {$this->getTable('recipe_main_category')} ADD `meta_description` Text NOT NULL AFTER `meta_keyword`  ");
$installer->endSetup();
