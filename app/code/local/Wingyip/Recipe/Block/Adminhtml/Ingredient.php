<?php
class Wingyip_Recipe_Block_Adminhtml_Ingredient extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_ingredient';
        $this->_blockGroup = 'recipe';
        $this->_headerText = Mage::helper('recipe')->__('Ingredient Manager');
        $this->_addButtonLabel = Mage::helper('recipe')->__('Add Ingredient');
        parent::__construct();
    }
}
