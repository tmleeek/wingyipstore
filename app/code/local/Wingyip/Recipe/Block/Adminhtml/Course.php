<?php
class Wingyip_Recipe_Block_Adminhtml_Course extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_course';
        $this->_blockGroup = 'recipe';
        $this->_headerText = Mage::helper('recipe')->__('Course Type Manager');
        $this->_addButtonLabel = Mage::helper('recipe')->__('Add Course');
        parent::__construct();
    }
}
