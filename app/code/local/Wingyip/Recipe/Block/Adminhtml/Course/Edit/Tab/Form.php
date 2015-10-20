<?php
class Wingyip_Recipe_Block_Adminhtml_Course_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('course_form', array('legend'=>Mage::helper('recipe')->__('Course Information')));
       
        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('recipe')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));
        
        $fieldset->addField('url_key', 'text', array(
            'label'     => Mage::helper('recipe')->__('Url Key'),
            'name'      => 'url_key',
        ));
        
        if ( Mage::getSingleton('adminhtml/session')->getCourseData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCourseData());
            Mage::getSingleton('adminhtml/session')->setCourseData(null);
        } elseif ( Mage::registry('course_data') ) {
            $form->setValues(Mage::registry('course_data')->getData());
        }
        return parent::_prepareForm();
    }
}
