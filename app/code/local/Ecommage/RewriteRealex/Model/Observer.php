<?php

class Ecommage_RewriteRealex_Model_Observer extends Mage_Core_Model_Observer
{
    public function autoGeneralInvoice()
    {
        $session = Mage::getSingleton('checkout/session');
        $orderId = $session->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

        $helper = Mage::helper('ecommage_rewriterealex')->isEnabledAutoGeneralInvoice();
        $getPaymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        if ($helper && $getPaymentMethod == 'realex') {
            if ($order) {
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transactionSave->save();
                $invoice->sendEmail();
                $invoice->setEmailSent(true);
                $invoice->save();
                $order->addStatusHistoryComment(Mage::helper('ecommage_rewriterealex')->__('Notified customer about invoice #%s.', $invoice->getIncrementId()));

            }
        }

        $order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
        $order->save();
        
    }
}