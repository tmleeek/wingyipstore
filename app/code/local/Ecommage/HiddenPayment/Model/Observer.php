<?php

class Ecommage_HiddenPayment_Model_Observer
{
    public function paymentMethodIsActive(Varien_Event_Observer $observer)
    {
        $isHiddenPaymentCheckMo=Mage::helper('ecommage_hiddenpayment')->isHiddenPaymentCheckMo();
        if($isHiddenPaymentCheckMo){
            $event = $observer->getEvent();
            $method = $event->getMethodInstance();
            $result = $event->getResult();
            if ($method->getCode() == 'checkmo') {
                $result->isAvailable = false;
            }
        }
    }
}