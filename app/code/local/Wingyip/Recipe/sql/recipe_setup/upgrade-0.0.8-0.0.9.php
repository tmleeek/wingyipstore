<?php
$installer = $this;
$installer->startSetup();
$installer->run("
 
DROP TABLE IF EXISTS {$this->getTable('recipe_category')};
CREATE TABLE {$this->getTable('recipe_category')} (
  `recipe_id` smallint(6) NOT NULL COMMENT 'Recipe ID',
  `recipe_category_id` smallint(5) unsigned NOT NULL COMMENT 'Category ID',
  PRIMARY KEY (`recipe_id`,`recipe_category_id`),
  KEY `IDX_RECIPE_CATEGORY_ID` (`recipe_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Recipe To Recipe Category Linkage Table';   
    ");   

$installer->run(" 

DROP TABLE IF EXISTS {$this->getTable('recipe_ingredients')};
CREATE TABLE {$this->getTable('recipe_ingredients')} (
  `recipe_id` smallint(6) NOT NULL COMMENT 'Recipe ID',
  `recipe_ingredients_id` smallint(5) unsigned NOT NULL COMMENT 'Ingredients ID',
  PRIMARY KEY (`recipe_id`,`recipe_ingredients_id`),
  KEY `IDX_RECIPE_INGREDIENTS_ID` (`recipe_ingredients_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Recipe To Recipe Ingredients Linkage Table';
    ");   

$installer->run(" 

DROP TABLE IF EXISTS {$this->getTable('recipe_cupboard_ing')};
CREATE TABLE {$this->getTable('recipe_cupboard_ing')} (
  `recipe_id` smallint(6) NOT NULL COMMENT 'Recipe ID',
  `recipe_cupboard_id` smallint(5) unsigned NOT NULL COMMENT 'Cupboard Ingredients ID',
  PRIMARY KEY (`recipe_id`,`recipe_cupboard_id`),
  KEY `IDX_RECIPE_CUPBOARD_INGREDIENTS_ID` (`recipe_cupboard_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Recipe To Recipe Cupboard Ingredients Linkage Table';
    ");   

$installer->run(" 

DROP TABLE IF EXISTS {$this->getTable('recipe_cuisine_type')};
CREATE TABLE {$this->getTable('recipe_cuisine_type')} (
  `recipe_id` smallint(6) NOT NULL COMMENT 'Recipe ID',
  `recipe_cuisine_id` smallint(5) unsigned NOT NULL COMMENT 'Cuisine ID',
  PRIMARY KEY (`recipe_id`,`recipe_cuisine_id`),
  KEY `IDX_RECIPE_CUISINE_TYPE_ID` (`recipe_cuisine_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Recipe To Recipe Cuisine Type Linkage Table';
    ");   

$installer->run(" 

DROP TABLE IF EXISTS {$this->getTable('recipe_cooking_method')};
CREATE TABLE {$this->getTable('recipe_cooking_method')} (
  `recipe_id` smallint(6) NOT NULL COMMENT 'Recipe ID',
  `cooking_id` smallint(5) unsigned NOT NULL COMMENT 'Cooking ID',
  PRIMARY KEY (`recipe_id`,`cooking_id`),
  KEY `IDX_RECIPE_COOKING_METHOD_ID` (`cooking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Recipe To Recipe Cooking Method Linkage Table';
    ");   
$installer->endSetup();
