<?php

$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('recipe_related')} ADD `relrecipe_id` INT( 11 ) NOT NULL AFTER `recipe_id`");
$installer->endSetup();

