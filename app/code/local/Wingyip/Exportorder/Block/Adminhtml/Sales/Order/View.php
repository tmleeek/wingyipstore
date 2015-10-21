<?php 
class Wingyip_Exportorder_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View {

    public function  __construct() {
        $this->_addButton('run_order_export', array(
            'label'     => Mage::helper('Sales')->__('Run Order Export'),
            'onclick'   => 'setLocation(\''.$this->getUrl('exportorder/adminhtml_exportorder/exportorder').'\')',
            
        ), 0, 100, 'header', 'header');
        
        $this->_addButton('download_order_export', array(
            'label'     => Mage::helper('Sales')->__('Download Order Export File'),
            'onclick'   => 'setLocation(\''.$this->getUrl('exportorder/adminhtml_exportorder/download').'\')',
            
        ), 100, 200, 'header', 'header');

        parent::__construct();
    }
}

