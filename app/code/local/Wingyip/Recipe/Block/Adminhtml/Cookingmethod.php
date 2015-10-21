<?php

class Wingyip_Recipe_Block_Adminhtml_Cookingmethod extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_cookingmethod';
        $this->_blockGroup = 'recipe';
        $this->_headerText = Mage::helper('recipe')->__('Cooking Method Management');
        $this->_addButtonLabel = Mage::helper('recipe')->__('Add Cooking Method');
        parent::__construct();
     }
}
