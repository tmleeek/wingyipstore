<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
ALTER TABLE wy_sales_flat_quote MODIFY items_count INT(10) DEFAULT 0 COMMENT 'Items Count' 
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 