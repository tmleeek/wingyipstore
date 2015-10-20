<?php 
class Wingyip_Blended_Block_Sales_Order_Blended extends Mage_Core_Block_Abstract
{
    /*public function initTotals()
    { 
        if($this->vatCalcuateVal() >= 0)
        {
            $vat = new Varien_Object();
            $vat->setCode('blended_vat_delivery');
            $vat->setValue($this->vatCalcuateVal());
            $vat->setBaseValue(0);
            $vat->setLabel('Blended Vat Delivery');
            $parent = $this->getParentBlock();
            $parent->addTotal($vat,'subtotal');
        }
    }
    public function vatCalcuateVal() 
    {    
        $order = $this->getParentBlock()->getOrder();
        $blendedVatDelivery = $order->getBlendedVatDelivery();
        return $blendedVatDelivery;
    }      */
}
