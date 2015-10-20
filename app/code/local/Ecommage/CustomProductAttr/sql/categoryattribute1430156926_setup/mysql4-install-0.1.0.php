<?php
$installer = $this;
$installer->startSetup();


$installer->addAttribute("catalog_product", "update_price",  array(
    "type"     => "int",
    "label"    => "Update Price",
    "input"    => "select",
    "class"    => "",
    "source"   => "eav/entity_attribute_source_boolean",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    "user_defined"  => true,
    "default" => "1",
    'required' => 0,
	));
$installer->endSetup();
	 