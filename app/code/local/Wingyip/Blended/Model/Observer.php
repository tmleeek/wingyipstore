<?php
class Wingyip_Blended_Model_Observer extends Varien_Event_Observer
{
    public function calculateVat($observer)
    { 
        $blendedVatDelivery = 0.00;
        $blendedVatRate = 0.00;
        $orderDetail = $observer->getEvent()->getOrder();
        //$orderId = $orderDetail->getId(); 
        $grandTotal = $orderDetail->getBaseGrandTotal();
        $subTotal = $orderDetail->getSubtotal();
        $subTotalIncl = $orderDetail->getSubtotalInclTax();
        $shippintRate = $orderDetail->getShippingTaxAmount()+$orderDetail->getShippingAmount();
        $shippintInclTax = $orderDetail->getShippingInclTax();
        
        //  blended vat delivery formula
        $blendedVatDelivery = ((($subTotalIncl - $subTotal)/($subTotalIncl)) * ($shippintRate));
        
        //  blended vat rate formula       
        $blendedVatRate = (($blendedVatDelivery/($shippintRate - $blendedVatDelivery)) * (100));
        
        //$orderModel = Mage::getModel('sales/order')->load($orderId)->setBlendedVatDelivery($blendedVatDelivery)->setBlendedVatRate($blendedVatRate);
        $orderDetail->setBlendedVatDelivery($blendedVatDelivery)->setBlendedVatRate($blendedVatRate);
        
        try{
             $orderDetail->save();    
        }
        catch(Exception $e){
            echo $e->getMessage();
        }
        return ;
    }
}