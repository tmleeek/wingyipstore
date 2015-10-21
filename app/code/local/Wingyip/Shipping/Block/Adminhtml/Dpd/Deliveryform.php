<?php
/**
* Magento
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@magentocommerce.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade Magento to newer
* versions in the future. If you wish to customize Magento for your
* needs please refer to http://www.magentocommerce.com for more information.
*
* @category    Mage
* @package     Mage_Adminhtml
* @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
* @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
* Poll edit form
*
* @category   Mage
* @package    Mage_Adminhtml
* @author      Magento Core Team <core@magentocommerce.com>
*/

class Wingyip_Shipping_Block_Adminhtml_Dpd_Deliveryform extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('delivery_form', array('legend'=>Mage::helper('adminhtml')->__('Delivery Information')));

        $fieldset->addField('delivery_name', 'text', array(
        'name'      => 'delivery[name]',
        'label'     => Mage::helper('shipping')->__('Contact Name'),
        'title'     => Mage::helper('shipping')->__('Contact Name'),
        'required'  => true,
        'maxlength' => 35,            

        ));

        $fieldset->addField('delivery_phone', 'text', array(
        'name'      => 'delivery[phone]',
        'label'     => Mage::helper('shipping')->__('Phone'),
        'title'     => Mage::helper('shipping')->__('Phone'),
        'required'  => true,
        'maxlength' => 35,            

        ));

        $fieldset->addField('delivery_email', 'text', array(
        'name'      => 'delivery[email]',
        'label'     => Mage::helper('shipping')->__('Email'),
        'title'     => Mage::helper('shipping')->__('Email'),
        'required'  => true,                      
        ));        

        $fieldset->addField('delivery_company', 'text', array(
        'name'      => 'delivery[company]',
        'label'     => Mage::helper('shipping')->__('Company'),
        'title'     => Mage::helper('shipping')->__('Company'), 
        'maxlength' => 35,                                   
        ));

        $fieldset->addField('delivery_street', 'text', array(
        'name'      => 'delivery[street]',
        'label'     => Mage::helper('shipping')->__('Street'),
        'title'     => Mage::helper('shipping')->__('Street'),
        'required'  => true,
        'maxlength' => 35,            
        ));

        $fieldset->addField('delivery_town', 'text', array(
        'name'      => 'delivery[town]',
        'label'     => Mage::helper('shipping')->__('Town'),
        'title'     => Mage::helper('shipping')->__('Town'),
        'required'  => true,

        ));

        $fieldset->addField('delivery_postcode', 'text', array(
        'name'      => 'delivery[postcode]',
        'label'     => Mage::helper('shipping')->__('Postcode'),
        'title'     => Mage::helper('shipping')->__('Postcode'),
        'required'  => true,

        ));

        $fieldset->addField('delivery_country', 'select', array(
        'name'      => 'delivery[country]',
        'label'     => Mage::helper('shipping')->__('Country'),
        'title'     => Mage::helper('shipping')->__('Country'),
        'values'    => Mage::getModel('adminhtml/system_config_source_country') ->toOptionArray(),
        'required'  => true,

        ));

        if ( Mage::registry('delivery_data') ) {
            $form->setValues(Mage::registry('delivery_data')->getData());
        }

        return parent::_prepareForm();
    }
}


