<?php

$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('recipe')} CHANGE `course` `course` INT( 11 ) NOT NULL DEFAULT '0'");
$installer->endSetup();

