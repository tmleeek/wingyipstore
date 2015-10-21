<?php

$installer     = new Mage_Sales_Model_Mysql4_Setup;
$installer->startSetup();
$installer->addAttribute('order', 'shipping_sku', array(
    'type'          => 'varchar',
    'label'         => 'Shipping SKU',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
        'user_defined'  =>  true
));
 
$installer->endSetup();