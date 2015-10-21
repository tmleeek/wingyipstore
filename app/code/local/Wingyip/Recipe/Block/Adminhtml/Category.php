<?php
class Wingyip_Recipe_Block_Adminhtml_Category extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_category';
        $this->_blockGroup = 'recipe';
        $this->_headerText = Mage::helper('recipe')->__('Category Manager');
        $this->_addButtonLabel = Mage::helper('recipe')->__('Add Category');
        parent::__construct();
    }
}
