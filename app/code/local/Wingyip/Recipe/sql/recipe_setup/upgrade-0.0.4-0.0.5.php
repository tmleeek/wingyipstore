<?php
$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('recipe_main_cuisine_type')};
CREATE TABLE {$this->getTable('recipe_main_cuisine_type')} (
  `recipe_cuisine_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `level` int(11) NOT NULL default '0',
  `path` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '1',
  `sort` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`recipe_cuisine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
$installer->endSetup();
