<?php
class Wingyip_Blended_Model_Sales_Order extends Mage_Sales_Model_Order
{
    public function getVatDelivery()
    {
        $varDelivery = $this->getBlendedVatDelivery();
        $varDelivery = round($varDelivery,2);
        return $varDelivery;
    }
    public function getVatRate()
    {
        $varRate = $this->getBlendedVatRate();
        $varRate = round($varRate,2);
        return $varRate;
    }
}
