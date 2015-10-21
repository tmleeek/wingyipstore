<?php

// OLD SETUP CLASS FOR RETRO COMPAT WITH MAGENTO CE VERSIONS <= 1.6
$installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
$installer->startSetup();

$entityTypeId     = $installer->getEntityTypeId('catalog_category');

$attributeSetCandidate = Mage::getModel('eav/entity_attribute_set')->getCollection()
	->addFieldToFilter('entity_type_id', $entityTypeId)
	->addFieldToFilter('attribute_set_name', 'General Information')
	->getFirstItem();

if($attributeSetCandidate->getId()) {
	$attributeSetId   = $attributeSetCandidate->getId();
} else {
	$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
}
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute('catalog_category', 'store_in_nitrogento_cache', array(
    'type'              => 'int',
	'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'label'             => 'Store Entity Content In Nitrogento Cache',
    'input'             => 'select',
    'default'			=> 1,
	'source'   			=> 'eav/entity_attribute_source_boolean',
	'used_in_product_listing' => 1,
));

$installer->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'store_in_nitrogento_cache',
	'100'
);

$entityTypeId     = $installer->getEntityTypeId('catalog_product');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute('catalog_product', 'store_in_nitrogento_cache', array(
	'type'              => 'int',
	'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'label'             => 'Store Entity Content In Nitrogento Cache',
	'input'             => 'select',
	'default'			=> 1,
	'source'   			=> 'eav/entity_attribute_source_boolean',
	'used_in_product_listing' => 1,
));

$installer->addAttributeToGroup(
	$entityTypeId,
	$attributeSetId,
	$attributeGroupId,
	'store_in_nitrogento_cache',
	'100'
);

$installer->endSetup();