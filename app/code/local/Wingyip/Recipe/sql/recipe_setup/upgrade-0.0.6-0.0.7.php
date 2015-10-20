<?php
$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('recipe_associated')};
CREATE TABLE {$this->getTable('recipe_associated')} (
  `associated_id` int(11) unsigned NOT NULL auto_increment,
  `recipe_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  PRIMARY KEY (`associated_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
$installer->endSetup();
