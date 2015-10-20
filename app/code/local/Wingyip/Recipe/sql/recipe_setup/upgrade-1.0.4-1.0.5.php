 <?php

$installer = $this;
$installer->startSetup();
$installer->run(" ALTER TABLE {$this->getTable('recipe')} ADD `ingredients_description` text AFTER `short_description`");
$installer->endSetup();