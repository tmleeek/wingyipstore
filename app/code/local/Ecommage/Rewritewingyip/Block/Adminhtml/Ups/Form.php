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

class Ecommage_Rewritewingyip_Block_Adminhtml_Ups_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $shipto = $form->addFieldset('shipto_form', array('legend'=>Mage::helper('adminhtml')->__('Ship To')));


        $shipto->addField('company_or_name', 'text', array(
            'name'      => 'company_or_name',
            'label'     => Mage::helper('rewritewingyip')->__('Company Or Name'),
            'title'     => Mage::helper('rewritewingyip')->__('Company Or Name'),
        ));

        $shipto->addField('attention', 'text', array(
            'name'      => 'attention',
            'label'     => Mage::helper('rewritewingyip')->__('Attention'),
            'title'     => Mage::helper('rewritewingyip')->__('Attention'),
        ));

        $shipto->addField('telephone', 'text', array(
            'name'      => 'telephone',
            'label'     => Mage::helper('rewritewingyip')->__('Telephone'),
            'title'     => Mage::helper('rewritewingyip')->__('Telephone'),
            'maxlength' => 35,
            'class'     => 'validate-number',
        ));

        $shipto->addField('address1', 'text', array(
            'name'      => 'address1',
            'label'     => Mage::helper('rewritewingyip')->__('Address'),
            'title'     => Mage::helper('rewritewingyip')->__('Address'),
        ));

        $shipto->addField('address2', 'text', array(
            'name'      => 'address2',
            'label'     => Mage::helper('rewritewingyip')->__('Address'),
            'title'     => Mage::helper('rewritewingyip')->__('Address'),
        ));

        $shipto->addField('country_territory', 'select', array(
            'name'      => 'country_territory',
            'label'     => Mage::helper('rewritewingyip')->__('Country Territory'),
            'title'     => Mage::helper('rewritewingyip')->__('Country Territory'),
            'values'    => Mage::getModel('adminhtml/system_config_source_country') ->toOptionArray(),
        ));

        $shipto->addField('postal_code', 'text', array(
            'name'      => 'postal_code',
            'label'     => Mage::helper('rewritewingyip')->__('Postcode'),
            'title'     => Mage::helper('rewritewingyip')->__('Postcode'),
        ));


        $shipto->addField('city_or_town', 'text', array(
            'name'      => 'city_or_town',
            'label'     => Mage::helper('rewritewingyip')->__('City Or Town'),
            'title'     => Mage::helper('rewritewingyip')->__('City Or Town'),

        ));

        $shipto->addField('location_id', 'text', array(
            'name'      => 'location_id',
            'label'     => Mage::helper('rewritewingyip')->__('Location Id'),
        ));

        $shipment = $form->addFieldset('shipment_form', array('legend'=>Mage::helper('adminhtml')->__('Shipment Information')));

        $shipment->addField('service_type', 'text', array(
            'name'      => 'service_type',
            'label'     => Mage::helper('rewritewingyip')->__('ServiceType'),
            'title'     => Mage::helper('rewritewingyip')->__('ServiceType'),
        ));


        $shipment->addField('description_of_goods', 'textarea', array(
            'name'      => 'description_of_goods',
            'label'     => Mage::helper('rewritewingyip')->__('Description Of Goods'),
            'title'     => Mage::helper('rewritewingyip')->__('Description Of Goods'),
            'maxlength' => 500,

        ));

        $shipment->addField('bill_transportation_to', 'text', array(
            'name'      => 'bill_transportation_to',
            'label'     => Mage::helper('rewritewingyip')->__('Bill Transportation To'),
            'title'     => Mage::helper('rewritewingyip')->__('Bill Transportation To'),
        ));

        $shipment->addField('profile_name', 'text', array(
            'name'      => 'profile_name',
            'label'     => Mage::helper('rewritewingyip')->__('Profile Name'),
            'title'     => Mage::helper('rewritewingyip')->__('Profile Name'),
        ));

        $shipment->addField('shipper_number', 'text', array(
            'name'      => 'shipper_number',
            'label'     => Mage::helper('rewritewingyip')->__('Shipper Number'),
            'title'     => Mage::helper('rewritewingyip')->__('Shipper Number'),
        ));



        $package = $form->addFieldset('package_form', array('legend'=>Mage::helper('adminhtml')->__('Package')));

        $package->addField('package_type', 'text', array(
            'name'      => 'package_type',
            'label'     => Mage::helper('shipping')->__('Package Type'),
            'title'     => Mage::helper('shipping')->__('Package Type'),
        ));

        $package->addField('weight', 'text', array(
            'name'      => 'weight',
            'label'     => Mage::helper('shipping')->__('Weight'),
            'title'     => Mage::helper('shipping')->__('Weight'),
        ));

        $package->addField('reference1', 'text', array(
            'name'      => 'reference1',
            'label'     => Mage::helper('shipping')->__('Reference 1'),
            'title'     => Mage::helper('shipping')->__('Reference 1'),
        ));

        $package->addField('reference2', 'text', array(
            'name'      => 'reference2',
            'label'     => Mage::helper('shipping')->__('Reference 2'),
            'title'     => Mage::helper('shipping')->__('Reference 2'),
        ));

        $package->addField('reference3', 'text', array(
            'name'      => 'reference3',
            'label'     => Mage::helper('shipping')->__('Reference 3'),
            'title'     => Mage::helper('shipping')->__('Reference 3'),
        ));

        $package->addField('reference4', 'text', array(
            'name'      => 'reference4',
            'label'     => Mage::helper('shipping')->__('Reference 4'),
            'title'     => Mage::helper('shipping')->__('Reference 4'),
        ));

        $package->addField('reference5', 'text', array(
            'name'      => 'reference5',
            'label'     => Mage::helper('shipping')->__('Reference 5'),
            'title'     => Mage::helper('shipping')->__('Reference 5'),
        ));
        $dataDefault = Mage::registry('package_data');

//        $orderId = $this->getRequest()->getParam('order_id');
//        Mage::helper('rewritewingyip')->getbyOrderId($orderId);
        $formData = array();

        $formData['company_or_name'] = $dataDefault->getCompanyorname();
        $formData['attention'] = $dataDefault->getAttention();
        $formData['telephone'] = $dataDefault->getTelephone();
        $formData['address1'] = $dataDefault->getAddress1();
        $formData['address2'] = $dataDefault->getAddress2();
        $formData['country_territory'] = $dataDefault->getCountryterritory();
        $formData['postal_code'] = $dataDefault->getPostcode();
        $formData['city_or_town'] = $dataDefault->getCityortown();
        $formData['location_id'] = $dataDefault->getLocalid();


        $formData['service_type'] = $dataDefault->getServicetype();
        $formData['description_of_goods'] = $dataDefault->getDescriptionofgoods();
        $formData['bill_transportation_to'] = $dataDefault->getBilltransportationto();
        $formData['profile_name'] = $dataDefault->getProfilename();
        $formData['shipper_number'] = $dataDefault->getShippernumber();


        $formData['package_type'] = $dataDefault->getPackagetype();
        $formData['weight'] = $dataDefault->getWeight();
        $formData['reference1'] = $dataDefault->getReference1();
        $formData['reference2'] = $dataDefault->getReference2();
        $formData['reference3'] = $dataDefault->getReference3();
        $formData['reference4'] = $dataDefault->getReference4();
        $formData['reference5'] = $dataDefault->getReference5();
        $form->setValues($formData);
        return parent::_prepareForm();
    }
}
