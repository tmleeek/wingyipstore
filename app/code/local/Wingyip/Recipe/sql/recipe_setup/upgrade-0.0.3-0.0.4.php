<?php
$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('recipe_main_cupboard_ing')};
CREATE TABLE {$this->getTable('recipe_main_cupboard_ing')} (
  `recipe_cupboard_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `sort` int(11) NOT NULL default '0',
  `status` smallint(6) NOT NULL default '1',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`recipe_cupboard_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
$installer->endSetup();
