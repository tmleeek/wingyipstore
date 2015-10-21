<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE {$this->getTable('sales_flat_order')} ADD  `blended_vat_delivery` DECIMAL( 12, 4 ) NOT NULL AFTER `upload_status`");
$installer->run("ALTER TABLE {$this->getTable('sales_flat_order')} ADD  `blended_vat_rate` DECIMAL( 12, 4 ) NOT NULL AFTER `blended_vat_delivery`");
$installer->endSetup(); 
