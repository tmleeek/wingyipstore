<?php
class Ecommage_HiddenPayment_Helper_Data extends Mage_Core_Helper_Abstract
{
    const PAYMENT_CHECKOUT_MONEY_ORDER_HIDDEN_FRONTEND = 'payment/checkmo/hiddenfrontend';

    public function isHiddenPaymentCheckMo(){
        return Mage::getStoreConfig(self::PAYMENT_CHECKOUT_MONEY_ORDER_HIDDEN_FRONTEND);
    }
}