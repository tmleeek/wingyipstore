<?php

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
/* Get the customer entity type Id */
$entity = $setup->getEntityTypeId('customer');
 
$attributeName = 'iris_payer_ref';
 
/* create the new attribute */
$setup->addAttribute($entity, $attributeName, array(
		'type' => 'varchar',				/* input type */
		'label' => 'Iris Payer Ref',		/* Label for the user to read */
		'input' => 'text',			/* input type */
		'visible' => TRUE,				/* users can see it */
		'required' => FALSE,			/* is it required, self-explanatory */
		//'adminhtml_only' => '1'		/* use in admin html only */
		'user_defined' => '1',
));
/* save the setup */

$setup->endSetup();
