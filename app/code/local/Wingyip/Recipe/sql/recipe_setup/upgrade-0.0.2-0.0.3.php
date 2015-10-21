<?php
$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('recipe_main_ingredients')};
CREATE TABLE {$this->getTable('recipe_main_ingredients')} (
  `recipe_ingredients_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '1',
  `sort` int(11) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`recipe_ingredients_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
$installer->endSetup();
