<?php

$installer = $this;
$installer->startSetup();  
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `pares`  TEXT NOT NULL DEFAULT '' AFTER `pasref`");
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `eci`  INT(11) UNSIGNED DEFAULT NULL AFTER `pares`");
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `xid`  TEXT NOT NULL DEFAULT '' AFTER `eci`");
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `cavv`  TEXT NOT NULL DEFAULT '' AFTER `xid`");
$installer->endSetup(); 