<?php
class Wingyip_Recipe_Block_Adminhtml_Cookingmethod_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array('add_variables' => false, 'add_widgets' => false,'files_browser_window_url'=>$this->getBaseUrl().'admin/cms_wysiwyg_images/index/'));
        
        $fieldset = $form->addFieldset('cooking_form', array('legend'=>Mage::helper('recipe')->__('Cooking Method Information')));
       
        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('recipe')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));
        
        /*$fieldset->addField('description', 'editor', array(
            'label'     => Mage::helper('recipe')->__('Description'),
            'name'      => 'description',
            'style'     => 'width:98%; height:200px;',
            'state'     => 'html',
            'wysiwyg'   => true,
            'required'  => false,
            'config'    => $wysiwygConfig,
        ));*/
                
        $fieldset->addField('code', 'text', array(
            'label'     => Mage::helper('recipe')->__('Code'),
            'required'  => true,
            'name'      => 'code',
        ));       
        
        $fieldset->addField('sort', 'text', array(
            'label'     => Mage::helper('recipe')->__('Sort'),
            'class'     => 'validate-number',
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
        
        if ( Mage::getSingleton('adminhtml/session')->getCookingData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCookingData());
            Mage::getSingleton('adminhtml/session')->setCookingData(null);
        } elseif ( Mage::registry('cooking_data') ) {
            $form->setValues(Mage::registry('cooking_data')->getData());
        }
        return parent::_prepareForm();
    }
}
