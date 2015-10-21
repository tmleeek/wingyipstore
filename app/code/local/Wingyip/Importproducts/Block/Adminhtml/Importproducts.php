<?php

class Wingyip_Importproducts_Block_Adminhtml_Importproducts extends Mage_Adminhtml_Block_Widget_Grid_Container

{

  public function __construct()

  {

    $this->_controller = 'adminhtml_importproducts';

    $this->_blockGroup = 'importproducts';

    $this->_headerText = Mage::helper('importproducts')->__('Manage Import Products');

    
	$this->_addButton('importfile', array(
        'label' => $this->__('Import File from Ftp'),
        'onclick' => "setLocation('{$this->getUrl('*/*/importfile')}')",
    ));
    
    $this->_addButton('runimport', array(
        'label' => $this->__('Run Import'),
        'onclick' => "setLocation('{$this->getUrl('*/*/directFileImport')}')",
    ));
	
    parent::__construct();

  }
  

}