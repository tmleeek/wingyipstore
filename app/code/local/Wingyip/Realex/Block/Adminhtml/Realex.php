<?php
class Wingyip_Realex_Block_Adminhtml_Realex extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_realex';
    $this->_blockGroup = 'realex';
    $this->_headerText = Mage::helper('realex')->__('Realex Transactions');
    parent::__construct();
    $this->_removeButton('add');
  }
}