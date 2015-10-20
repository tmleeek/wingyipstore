<?php

$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('recipe_related')};
CREATE TABLE {$this->getTable('recipe_related')} (
  `related_id` int(11) unsigned NOT NULL auto_increment,
  `recipe_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`related_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
$installer->endSetup();
