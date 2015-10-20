<?php

$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('recipe_main_category')} ADD `url_key`  VARCHAR( 255 ) DEFAULT NULL AFTER `code`");
$installer->endSetup();

