<?php

$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('recipe_review')};
CREATE TABLE {$this->getTable('recipe_review')} (
  `review_id` int(11) unsigned NOT NULL auto_increment,
  `recipe_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `nickname` varchar(255) NOT NULL,
  `store_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `status` smallint(1) NOT NULL,
  `created_at` Datetime NOT NULL,
  `updated_at` Datetime NOT NULL,
  PRIMARY KEY (`review_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
	
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('recipe_review_description')};
CREATE TABLE {$this->getTable('recipe_review_description')} (
  `descr_id` int(11) unsigned NOT NULL auto_increment,
  `review_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`descr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");	

$installer->endSetup();
