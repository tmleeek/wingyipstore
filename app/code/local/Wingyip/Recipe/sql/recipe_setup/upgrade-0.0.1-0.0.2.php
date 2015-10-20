<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('recipe_main_category')} ADD  `code`  VARCHAR( 250 ) NOT NULL AFTER `path`");
$installer->endSetup();
