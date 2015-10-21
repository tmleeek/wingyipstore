<?php
class Wingyip_Shipping_Block_Adminhtml_Sales_Order_View extends Wingyip_Exportorder_Block_Adminhtml_Sales_Order_View
{

    public function __construct()
    { 
        parent::__construct();

        if($order = $this->getOrder()){ 

            //$this->_removeButton('order_edit'); 
            //$this->_removeButton('send_notification');


            /* Show buttons for 'Approve Order' */
            /*if($order->getStatus() == 'pending'){




            $this->_addButton('approve_order', array(
            'label'     => Mage::helper('sales')->__('Approve Order'),
            'onclick'   => 'setLocation(\'' . $this->getApproveOrderUrl() . '\')',
            ));    

            $this->_removeButton('order_invoice');
            $this->_removeButton('order_reorder');
            $this->_removeButton('order_creditmemo');
            $this->_removeButton('order_edit');          

            }*/

            $shippingMethod = Mage::getModel('shippingwing/shipping')->_getShippingMethod($order->getShippingDescription());
          
            if(!$order->getShipperShipmentid() && strtolower($shippingMethod) == "dpd" && $order->getStatus() != 'closed'){
                $this->_addButton('order_ship_status', array(
                'label'     => Mage::helper('sales')->__('Ship this Order'),
                'onclick'   => 'setLocation(\'' . $this->getShipOrderUrl() . '\')',
                'class'     => 'go'
                ));    
            }elseif(strtolower($shippingMethod) == "ups"){
                $this->_addButton('order_shiplabel', array(
                'label'     => Mage::helper('sales')->__('Generate Shipment Label'),
                'onclick' => "popWin('".$this->getShipLabelGenerateUrl()."','shipmentlabel','top:150,left:150,width=420,height=450,resizable=yes,scrollbars=no')",
                'class'     => 'go'
                ));
            }           
        }				        
    }

    public function getShipOrderUrl(){
        return $this->getUrl('shipping/adminhtml_order/ship');
    }

    public function getShipLabelGenerateUrl(){
        return $this->getUrl('shipping/adminhtml_order/generateLabel');
    }

}
