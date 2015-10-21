<?php
class Wingyip_Recipe_Block_Adminhtml_Recipe_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
               
        $this->_objectId = 'id';
        $this->_blockGroup = 'recipe';
        $this->_controller = 'adminhtml_recipe';
 
        $this->_updateButton('save', 'label', Mage::helper('recipe')->__('Save Recipe'));
        $this->_updateButton('delete', 'label', Mage::helper('recipe')->__('Delete Recipe'));
        
        $this->_addButton('save_and_continue', array(
             'label' => Mage::helper('recipe')->__('Save And Continue Edit'),
             'onclick' => 'saveAndContinueEdit()',
             'class' => 'save' 
         ), -100);
         $this->_formScripts[] = "
             function saveAndContinueEdit(){
                editForm.submit($('edit_form').action + 'back/edit/');
             }";
    }
    
    protected function _prepareLayout()
    {
        // Load Wysiwyg on demand and Prepare layout
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && ($block = $this->getLayout()->getBlock('head'))) {
            $block->setCanLoadTinyMce(true);
        }
        parent::_prepareLayout();
    }
 
    public function getHeaderText()
    {
        if( Mage::registry('recipe_data') && Mage::registry('recipe_data')->getId() ) {
            return Mage::helper('recipe')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('recipe_data')->getName()));
        } else {
            return Mage::helper('recipe')->__('Add Recipe');
        }
    }
}
