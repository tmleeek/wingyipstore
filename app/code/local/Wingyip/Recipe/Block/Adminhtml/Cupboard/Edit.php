<?php
class Wingyip_Recipe_Block_Adminhtml_Cupboard_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
               
        $this->_objectId = 'id';
        $this->_blockGroup = 'recipe';
        $this->_controller = 'adminhtml_cupboard';
 
        $this->_updateButton('save', 'label', Mage::helper('recipe')->__('Save Cupboard'));
        $this->_updateButton('delete', 'label', Mage::helper('recipe')->__('Delete Cupboard'));
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
 
    public function getHeaderText()
    {
        if( Mage::registry('cupboard_data') && Mage::registry('cupboard_data')->getId() ) {
            return Mage::helper('recipe')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('cupboard_data')->getName()));
        } else {
            return Mage::helper('recipe')->__('Add Cupboard');
        }
    }
}
