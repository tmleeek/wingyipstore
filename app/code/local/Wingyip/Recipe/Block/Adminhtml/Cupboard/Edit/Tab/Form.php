<?php
class Wingyip_Recipe_Block_Adminhtml_Cupboard_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('cupboard_form', array('legend'=>Mage::helper('recipe')->__('Cupboard Information')));
       
        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('recipe')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));
        
        $fieldset->addField('code', 'text', array(
            'label'     => Mage::helper('recipe')->__('Code'),
            'required'  => true,
            'name'      => 'code',
        ));
        
        $fieldset->addField('sort', 'text', array(
            'label'     => Mage::helper('recipe')->__('Sort'),
            'name'      => 'sort',
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
        
        if ( Mage::getSingleton('adminhtml/session')->getCupboardData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCupboardData());
            Mage::getSingleton('adminhtml/session')->setCupboardData(null);
        } elseif ( Mage::registry('cupboard_data') ) {
            $form->setValues(Mage::registry('cupboard_data')->getData());
        }
        return parent::_prepareForm();
    }
}
