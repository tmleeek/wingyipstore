<?php

$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('recipe_image')};
CREATE TABLE {$this->getTable('recipe_image')} (
 `image_id` int(11) unsigned NOT NULL auto_increment,
 `image` varchar(255) NOT NULL default '',
 `status` tinyint(1) DEFAULT '1',
 `recipe_id` int(11) DEFAULT '0',
 `is_default` int(11) DEFAULT '0',
 PRIMARY KEY (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;    
 ");
$installer->endSetup();
