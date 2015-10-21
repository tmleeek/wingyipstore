<?php
class Wingyip_Recipe_Block_Adminhtml_Category_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('category_form', array('legend'=>Mage::helper('recipe')->__('Category Information')));
       
        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('recipe')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));
        
        $fieldset->addField('description', 'editor', array(
            'name'      => 'description',
            'label'     => Mage::helper('recipe')->__('Description'),
            'title'     => Mage::helper('recipe')->__('Description'),
            'style'     => 'width:91%; height:150px;',
            'wysiwyg'   => false,
            'required'  => true,
        ));
        
        $fieldset->addField('image', 'image', array(
            'label'     => Mage::helper('recipe')->__('Image'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'image',
        ));
        
        /*$fieldset->addField('path', 'text', array(
            'label'     => Mage::helper('recipe')->__('Path'),
            'name'      => 'path',
        ));*/
        
         $fieldset->addField('code', 'text', array(
            'label'     => Mage::helper('recipe')->__('Code'),
            'required'  => true,
            'name'      => 'code',
        ));
        
        $fieldset->addField('url_key', 'text', array(
            'label'     => Mage::helper('recipe')->__('Url Key'),
            'name'      => 'url_key',
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
        
        if ( Mage::getSingleton('adminhtml/session')->getCategoryData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCategoryData());
            Mage::getSingleton('adminhtml/session')->setCategoryData(null);
        } elseif ( Mage::registry('category_data') ) {
            $form->setValues(Mage::registry('category_data')->getData());
        }
        return parent::_prepareForm();
    }
}
