<?php
/**
 * MageRevol
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Magerevol
 * @package    Magerevol_Brands
 * @author     Magerevol Development Team
 * @copyright  Copyright (c) 2012 MageRevol. (http://www.magerevol.com)
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

$installer = $this;

$installer->startSetup();

$setup = Mage::getModel('customer/entity_setup', 'core_setup');
$setup->addAttribute('customer_address', 'mobile_number', array(
    'type' => 'text',
    'input' => 'text',
    'label' => 'Mobile Number',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 1,
    'default' => '',
    'visible_on_front' => 1,
));
    
Mage::getSingleton('eav/config')
->getAttribute('customer_address', 'mobile_number')
->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer_address'))
->save();  

$installer->endSetup(); 