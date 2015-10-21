<?php
$installer = $this;
$installer->startSetup();
$installer->run("
 
DROP TABLE IF EXISTS {$this->getTable('recipe_main_cooking_method')};

CREATE TABLE {$this->getTable('recipe_main_cooking_method')} (
  `cooking_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `status` tinyint(1) NOT NULL default '1',
  `code` varchar(255) NOT NULL default '',
  `sort` int(11) NOT NULL default '0',
  `created_at` datetime NULL,
  `updated_at` datetime NULL,
  PRIMARY KEY (`cooking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
$installer->endSetup();
