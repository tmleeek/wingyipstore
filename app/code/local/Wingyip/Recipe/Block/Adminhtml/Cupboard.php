<?php
class Wingyip_Recipe_Block_Adminhtml_Cupboard extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_cupboard';
        $this->_blockGroup = 'recipe';
        $this->_headerText = Mage::helper('recipe')->__('Cupboard Ingredients Type Manager');
        $this->_addButtonLabel = Mage::helper('recipe')->__('Add Cupboard Ingredients Type');
        parent::__construct();
    }
}
