<?php

class Ves_Gallery_Block_Adminhtml_Banner_Add_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('slider_form', array('legend'=>Mage::helper('ves_gallery')->__('General Information')));
       
		$fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('ves_gallery')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
        ));
		
		$fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('ves_gallery')->__('Enable'),
            'class'     => 'required-entry',
            'required'  => false,
            'name'      => 'is_active',
			'values' 	  =>array('0'=>'No', '1'=>'Yes')
        ));
		
		$fieldset->addField('file', 'image', array(
            'label'     => Mage::helper('ves_gallery')->__('Image'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'file',
        ));
		
		$fieldset->addField('label', 'text', array(
            'label'     => Mage::helper('ves_gallery')->__('Group'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'label',
        ));

		$fieldset->addField('position', 'text', array(
            'label'     => Mage::helper('ves_gallery')->__('Position'),
            'class'     => 'required-entry',
            'required'  => false,
            'name'      => 'position',
        ));
		
	
		 
		
		
		$fieldset->addField('description', 'editor', array(
            'label'     => Mage::helper('ves_gallery')->__('Description'),
            'class'     => 'required-entry',
            'required'  => false,
            'name'      => 'description',
			'style'     => 'width:600px;height:300px;',
            'wysiwyg'   => false,
			//'value'     => $_model->getDescription()
			//'config'    => Mage::getVersion() > '1.4' ? @Mage::getSingleton('cms/wysiwyg_config')->getConfig() : false,
        ));
        
        return parent::_prepareForm();
    }
}
