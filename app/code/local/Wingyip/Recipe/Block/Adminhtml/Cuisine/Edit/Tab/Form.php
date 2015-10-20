<?php
class Wingyip_Recipe_Block_Adminhtml_Cuisine_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('cuisine_form', array('legend'=>Mage::helper('recipe')->__('Cuisine Type Information')));
       
        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('recipe')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));
        /*$fieldset->addField('level', 'text', array(
            'label'     => Mage::helper('recipe')->__('Level'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'level',
        ));
        $fieldset->addField('path', 'text', array(
            'label'     => Mage::helper('recipe')->__('Path'),
            'name'      => 'path',
        ));*/
        $fieldset->addField('code', 'text', array(
            'label'     => Mage::helper('recipe')->__('Code'),
            'required'  => true,
            'name'      => 'code',
        ));
        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('recipe')->__('Status'),
            'name'      => 'status',
            'required'  => true,
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('recipe')->__('Active'),
                ),
                array(
                    'value'     => 2,
                    'label'     => Mage::helper('recipe')->__('Inactive'),
                ),
            ),
        ));
        $fieldset->addField('sort', 'text', array(
            'label'     => Mage::helper('recipe')->__('Sort'),
            'name'      => 'sort',
        ));
        if ( Mage::getSingleton('adminhtml/session')->getCuisineData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCuisineData());
            Mage::getSingleton('adminhtml/session')->setCuisineData(null);
        } elseif ( Mage::registry('cuisine_data') ) {
            $form->setValues(Mage::registry('cuisine_data')->getData());
        }
        return parent::_prepareForm();
    }
}
