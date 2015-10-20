<?php
class Wingyip_Recipe_Block_Adminhtml_Recipe extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_recipe';
        $this->_blockGroup = 'recipe';
        $this->_headerText = Mage::helper('recipe')->__('Recipes Manager');
        $this->_addButtonLabel = Mage::helper('recipe')->__('Add Recipe');
        parent::__construct();
    }
}