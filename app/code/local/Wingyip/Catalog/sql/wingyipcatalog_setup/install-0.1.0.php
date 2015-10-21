<?php
$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('catalog_category', 'banner', array(
    'input'         => 'image', // you can change here
    'type'          => 'varchar',
    'group' => 'General Information',/// Change here whare you want to show this
    'label'         => 'Banner Image',
    'visible'       => 1,
    'backend' => 'catalog/category_attribute_backend_image',
    'required'      => 0,
    'user_defined' => 1,
    'frontend_input' =>'',
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible_on_front'  => 1,
));

$installer->endSetup();  