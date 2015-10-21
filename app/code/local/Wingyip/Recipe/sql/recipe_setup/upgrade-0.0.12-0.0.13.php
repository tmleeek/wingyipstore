<?php

$installer = $this;
$installer->startSetup();
$installer->run("
 
DROP TABLE IF EXISTS {$this->getTable('recipe_course')};

CREATE TABLE {$this->getTable('recipe_course')} (
  `course_id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `url_key` varchar(255) DEFAULT NULL, 
  `created_at` datetime NULL,
  `updated_at` datetime NULL,
  PRIMARY KEY (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
$installer->endSetup();
