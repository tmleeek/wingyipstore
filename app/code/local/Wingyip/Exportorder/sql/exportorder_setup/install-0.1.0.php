<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('sales_flat_order')} ADD  `export_status` SMALLINT( 1 ) NULL DEFAULT '1' COMMENT '1 = No, 2 = Yes'");
$installer->endSetup();