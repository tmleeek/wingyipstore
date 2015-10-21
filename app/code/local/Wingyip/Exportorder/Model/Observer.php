<?php
class Wingyip_Exportorder_Model_Observer extends Varien_Event_Observer
{
    public function exportOrder($observer)
    {
        $orderId=Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $orderDetail = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        if($orderDetail->getStatus()=='processing'){
            Mage::getSingleton("core/session")->setExportOrderWhenCheckout('true');
            Mage::getModel('exportorder/exportorder')->exportOrder(array($orderDetail->getId()));   
        }
        return $this;
    }
}