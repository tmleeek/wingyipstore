<?php
class Wingyip_Recipe_Block_Adminhtml_Review_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'recipe';
        $this->_controller = 'adminhtml_review';
 
        $this->_updateButton('save', 'label', Mage::helper('recipe')->__('Save Review'));
        $this->_updateButton('delete', 'label', Mage::helper('recipe')->__('Delete Review'));
        
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
        $collection = Mage::getModel('recipe/review')->getCollection();
        $collection->getSelect()
              ->join(
                      array('de'=>Mage::getConfig()->getTablePrefix().'recipe_review_description'),
                     'de.review_id =  main_table.review_id',
                      array('de.*')
                      )
              ->where('de.review_id = ?', Mage::registry('review_data')->getId()) ;
        
        $recipeReview = $collection->getFirstItem();
               
        if( $collection && $collection->getData() ) {
            return Mage::helper('recipe')->__("Edit Review '%s'", $this->htmlEscape($recipeReview->getSubject()));
        } else {
            return Mage::helper('recipe')->__('Add Review');
        }
    }
}
