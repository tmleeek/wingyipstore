<?php

class Ecommage_Autoinvoice_Helper_Data extends Mage_Core_Helper_Abstract
{

    private $_configPrefix = 'autoinvoice/';

    const XML_IS_ENABLED_AUTO_INVOICE = 'general/enabled';
    const XML_GET_CAPTURE_AMOUNT = 'general/capture';
    const XML_GET_ORDER_STATUS = 'general/orderstatus';
    const XML_SEND_INVOICE_EMAIL_TO_CUSTOMER = 'general/sendemail';

    public function isEnabled($storeId = null)
    {
        return Mage::getStoreConfigFlag($this->_configPrefix . self::XML_IS_ENABLED_AUTO_INVOICE, $storeId);
    }

    public function sendInvoiceEmail($storeId = null)
    {
        return Mage::getStoreConfigFlag($this->_configPrefix . self::XML_SEND_INVOICE_EMAIL_TO_CUSTOMER, $storeId);
    }

    public function getOrderStatus($storeId = null)
    {
        return Mage::getStoreConfig($this->_configPrefix . self::XML_GET_ORDER_STATUS, $storeId);
    }

    public function getCaptureAmount($storeId = null)
    {
        $value = Mage::getStoreConfig($this->_configPrefix . self::XML_GET_CAPTURE_AMOUNT, $storeId);
        if (!$value) {
            $value = Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE;
        }
        return $value;
    }
}