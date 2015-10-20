<?php

class Ecommage_RewriteRealex_Helper_Data extends Mage_Core_Helper_Abstract
{
    const IS_ENABLED_AUTO_GENERAL_INVOICE_REALEX = 'payment/realex/auto_general_invoice';

    public function isEnabledAutoGeneralInvoice()
    {
        return Mage::getStoreConfig(self::IS_ENABLED_AUTO_GENERAL_INVOICE_REALEX);
    }
}