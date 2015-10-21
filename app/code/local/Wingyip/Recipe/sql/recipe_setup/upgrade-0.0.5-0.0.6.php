<?php
$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('recipe')};
CREATE TABLE {$this->getTable('recipe')} (
  `recipe_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `short_description` text,
  `image` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `cooking_time` varchar(255) DEFAULT NULL,
  `course` varchar(255) DEFAULT NULL,
  `occasion` varchar(255) DEFAULT NULL,
  `no_of_ingredients` int(11) DEFAULT NULL,
  `serving_size` int(11) DEFAULT NULL,
  `heat_spice_level` int(11) NOT NULL,
  `meta_title` text,
  `meta_description` text,
  `meta_keyword` text,
  `url_key` varchar(255) DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT '1',
  `sort` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`recipe_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    ");
$installer->endSetup();
