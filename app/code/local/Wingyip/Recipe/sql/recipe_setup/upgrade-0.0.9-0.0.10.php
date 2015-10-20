<?php
$installer = $this;
$installer->startSetup();
$installer->run("
 
DROP TABLE IF EXISTS {$this->getTable('recipe_special_dietary_tags')};
CREATE TABLE {$this->getTable('recipe_special_dietary_tags')} (
  `tag_id` int(11) unsigned NOT NULL auto_increment,
  `recipe_id` int(11) NOT NULL,
  `special_diet_tag` varchar(255) NOT NULL,
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");   
$installer->endSetup();

