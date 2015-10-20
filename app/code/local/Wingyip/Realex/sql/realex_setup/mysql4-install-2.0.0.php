<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('realex')};
CREATE TABLE {$this->getTable('realex')} (
  `realex_id` int(11) unsigned NOT NULL auto_increment,
  `order_id` int(11) unsigned NOT NULL DEFAULT '0',
  `timestamp` datetime NULL,
  `merchantid` varchar(255) NOT NULL default '',
  `account` varchar(255) NOT NULL default '',
  `authcode` varchar(255) NOT NULL default '',
  `result` varchar(255) NOT NULL default '',
  `message` varchar(255) NOT NULL default '',
  `pasref` varchar(255) NOT NULL default '',
  `cvnresult` varchar(255) NOT NULL default '',
  `batchid` varchar(255) NOT NULL default '',
  `card_issuer_bank` varchar(255) NOT NULL default '',
  `card_issuer_country` varchar(255) NOT NULL default '',
  `tss_result` varchar(255) NOT NULL default '',
  `avspostcoderesponse` varchar(255) NOT NULL default '',
  `avsaddressresponse` varchar(255) NOT NULL default '',
  `timetaken` varchar(255) NOT NULL default '',
  `authtimetaken` varchar(255) NOT NULL default '',
  `hash` varchar(255) NOT NULL default '',
  `form_key` varchar(255) NOT NULL default '',
  `pas_uuid` varchar(255) NOT NULL default '',
  PRIMARY KEY (`realex_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->createStaticBlocks();

$installer->endSetup();

