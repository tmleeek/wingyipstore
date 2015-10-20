<?php

class Wingyip_Recipe_Block_Adminhtml_Review extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_review';
        $this->_blockGroup = 'recipe';
        $this->_headerText = Mage::helper('recipe')->__('Review Management');
        //$this->_addButtonLabel = Mage::helper('recipe')->__('Add Review');
        parent::__construct();
        $this->_removeButton('add');
     }
}
