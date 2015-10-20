<?php
class Wingyip_Shipping_Block_Adminhtml_Shippinglabel extends Mage_Core_Block_Template{

    public function getShippingLabel($shipmentId){
        return Mage::getModel('shippingwing/shipping')->getShippingLabel($shipmentId);      
    }        
}
