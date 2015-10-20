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

class Wingyip_Shipping_Block_Adminhtml_Dpd_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('other_form', array('legend'=>Mage::helper('adminhtml')->__('Other Information')));

        $fieldset->addField('collection_on_delivery', 'select', array(
        'label'     => Mage::helper('shipping')->__('Collection on Delivery'),
        'title'     => Mage::helper('shipping')->__('Collection on Delivery'),
        'name'      => 'collection_on_delivery',
        'required'  => true,
        'options'   => array(
        '1' => Mage::helper('shipping')->__('Yes'),
        '2' => Mage::helper('shipping')->__('No'),
        ),
        'value'     => '2',
        ));

        $fieldset->addField('total_parcel', 'text', array(
        'name'      => 'total_parcel',
        'label'     => Mage::helper('shipping')->__('Number of Parcel'),
        'title'     => Mage::helper('shipping')->__('Number of Parcel'),
        'required'  => true,
        'class'     => 'validate-number',
        'value'     =>  1,

        ));

        $fieldset->addField('total_weight', 'text', array(
        'name'      => 'total_weight',
        'label'     => Mage::helper('shipping')->__('Total Weight'),
        'title'     => Mage::helper('shipping')->__('Total Weight'),
        'required'  => true,
        'class'     => 'validate-number',

        ));

        $fieldset->addField('shippingref1', 'hidden', array(
        'name'      => 'shippingref1',
        'label'     => Mage::helper('shipping')->__('Shipping Ref 1'),
        'title'     => Mage::helper('shipping')->__('Shipping Ref 1'),
        'required'  => false,
        'value'     => 'Catalogue Batch 1'
        ));

        $fieldset->addField('shippingref2', 'hidden', array(
        'name'      => 'shippingref2',
        'label'     => Mage::helper('shipping')->__('Shipping Ref 2'),
        'title'     => Mage::helper('shipping')->__('Shipping Ref 2'),
        'required'  => false,

        ));

        $fieldset->addField('shippingref3', 'hidden', array(
        'name'      => 'shippingref3',
        'label'     => Mage::helper('shipping')->__('Shipping Ref 3'),
        'title'     => Mage::helper('shipping')->__('Shipping Ref 3'),
        'required'  => false,            
        ));

        $fieldset->addField('delivery_instruction', 'textarea', array(
        'name'      => 'delivery_instruction',
        'label'     => Mage::helper('shipping')->__('Delivery Instruction'),
        'title'     => Mage::helper('shipping')->__('Delivery Instruction'),
        'required'  => false,
        'maxlength' => 50,            
        ));

      /*  $fieldset->addField('parcel_description', 'textarea', array(
        'name'      => 'parcel_description',
        'label'     => Mage::helper('shipping')->__('Parcel Description'),
        'title'     => Mage::helper('shipping')->__('Parcel Description'),
        'required'  => true,
        'maxlength' => 50,
        ));*/
        $dateFormatIso =  'yyyy-MM-ddTHH:mm:ss';   
        $fieldset->addField('collection_date', 'date', array(
        'label' => Mage::helper('shipping')->__('Collection Date'),
        'title' => Mage::helper('shipping')->__('Colection Date'),
        'time'      =>    true,
        'name' => 'collection_date',
        'image' => $this->getSkinUrl('images/grid-cal.gif'),
        'format' => $dateFormatIso,     
        ));


        if ( Mage::registry('form_data') ) {
            $form->setValues(Mage::registry('form_data')->getData());
        }


        return parent::_prepareForm();
    }
}
