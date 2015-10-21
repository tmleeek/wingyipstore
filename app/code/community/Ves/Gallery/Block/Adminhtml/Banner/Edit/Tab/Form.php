<?php


class Ves_Gallery_Block_Adminhtml_Banner_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_model = Mage::registry('banner_data');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('slider_form', array('legend'=>Mage::helper('ves_gallery')->__('General Information')));
        
		
		$fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('ves_gallery')->__('Is Active'),
            'name'      => 'is_active',
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            //'value'     => $_model->getIsActive()
        ));
		$fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('ves_gallery')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
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
            //'value'     => $_model->getLabel()
        ));

		
		
		$fieldset->addField('position', 'text', array(
            'label'     => Mage::helper('ves_gallery')->__('Position'),
            'class'     => 'required-entry',
            'required'  => false,
            'name'      => 'position',
			//'value'     => $_model->getPosition()
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
		

        
		

        
		if ( Mage::getSingleton('adminhtml/session')->getBannerData() )
		  {
			  $form->setValues(Mage::getSingleton('adminhtml/session')->getBannerData());
			  Mage::getSingleton('adminhtml/session')->getBannerData(null);
		  } elseif ( Mage::registry('banner_data') ) {
			  $form->setValues(Mage::registry('banner_data')->getData());
		  }
        
        return parent::_prepareForm();
    }
}
