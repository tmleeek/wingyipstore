<?php
$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('shipping_ups')};
CREATE TABLE {$this->getTable('shipping_ups')} (
  ups_id int(10) unsigned NOT NULL auto_increment,
  order_id int(11) NOT NULL default '0',
  company_or_name varchar (255) NOT NULL default '',
  attention varchar(255) NOT NULL default '',
  address1 varchar(255) NOT NULL default '',
  address2 varchar(255) NOT NULL default '',
  country_territory varchar(255) NOT NULL default '',
  postal_code varchar(255) NOT NULL default '',
  city_or_town varchar(255) NOT NULL default '',
  telephone int(10) NOT NULL default '0',
  location_id varchar(255) NOT NULL default '',
  service_type varchar(255) NOT NULL default '',
  description_of_goods varchar(500) NOT NULL default '',
  bill_transportation_to varchar(255) NOT NULL default '',
  profile_name varchar(255) NOT NULL default '',
  shipper_number varchar(255) NOT NULL default '',
  package_type varchar(255) NOT NULL default '',
  weight varchar(255) NOT NULL default '',
  reference1 varchar(255) NOT NULL default '',
  reference2 varchar(255) NOT NULL default '',
  reference3 varchar(255) NOT NULL default '',
  reference4 varchar(255) NOT NULL default '',
  reference5 varchar(255) NOT NULL default '',
  PRIMARY KEY(`ups_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
   ");
$installer->endSetup();