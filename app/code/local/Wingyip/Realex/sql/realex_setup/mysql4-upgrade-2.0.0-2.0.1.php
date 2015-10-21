<?php

$installer = $this;
$installer->startSetup();  
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `enrollment_timestamp`  DATETIME NULL AFTER `pas_uuid`");
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `enrollment_response`  TEXT NOT NULL DEFAULT '' AFTER `enrollment_timestamp`");
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `enrollment_cart_quote_id`  INT(11) UNSIGNED DEFAULT NULL AFTER `enrollment_response`");
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `enrollment_message`  VARCHAR(255) NOT NULL DEFAULT '' AFTER `enrollment_cart_quote_id`");
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `enrollment_pareq`  TEXT NOT NULL DEFAULT '' AFTER `enrollment_message`");
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `enrollment_result`  INT(11) UNSIGNED DEFAULT NULL AFTER `enrollment_pareq`");
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `acs_url`  VARCHAR(255) NOT NULL DEFAULT '' AFTER `enrollment_result`");
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `is_enrolled`  VARCHAR(5) NOT NULL DEFAULT '' AFTER `acs_url`");
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `enrollment_transaction_id`  VARCHAR(255) NOT NULL DEFAULT '' AFTER `is_enrolled`");    
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `enrollment_timetaken`  INT(11) UNSIGNED DEFAULT NULL AFTER `enrollment_transaction_id`");
$installer->run("ALTER TABLE {$this->getTable('realex')} ADD  `enrollment_authtimetaken`  INT(11) UNSIGNED DEFAULT NULL AFTER `enrollment_timetaken`");
$installer->endSetup(); 