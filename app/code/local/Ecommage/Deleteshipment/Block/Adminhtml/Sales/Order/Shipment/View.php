<?php
class Ecommage_Deleteshipment_Block_Adminhtml_Sales_Order_Shipment_View extends Mage_Adminhtml_Block_Sales_Order_Shipment_View{

    public function __construct()
    {
        parent::__construct();
        if ($this->getShipment()->getId()) {
            $this->_addButton('delete', array(
                    'label'     => Mage::helper('sales')->__('Delete'),
                    'class'     => 'delete',
                    'onclick'   => 'confirmSetLocation(\''
                        . Mage::helper('catalog')->__('Are you sure?').'\', \''.$this->getDeleteUrl().'\')',
                )
            );
        }
    }
    public function getDeleteUrl(){
        return $this->getUrl('*/sales_order_shipment/delete', array('shipment_id'  => $this->getShipment()->getId()));
    }
}