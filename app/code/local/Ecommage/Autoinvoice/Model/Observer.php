<?php

class Ecommage_Autoinvoice_Model_Observer
{
    public function autoInvoiceCreateOrder(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $_helper = Mage::helper('ecommage_autoinvoice');
        if ($_helper->isEnabled()) {
            try {
                if ($order->canInvoice()) {
                    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                    if ($invoice) {
                        $invoice->setRequestedCaptureCase($_helper->getCaptureAmount($order->getStoreId()));
                        $invoice->register();
                        $invoice->getOrder()->setCustomerNoteNotify(false);
                        $invoice->getOrder()->setIsInProcess(true);

                        $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());
                        $transactionSave->save();

                        $order->addStatusHistoryComment('Auto Invoice: Order invoiced.', false)->save();
                    }
                }
            } catch (Exception $e) {
                //echo $e->getMessage();
            }
        }
    }

    public function setStatus()
    {
        $_helper = Mage::helper('ecommage_autoinvoice');
        $status = $_helper->getOrderStatus();
        Mage::dispatchEvent('sales_order_status_after', array('order' => $this, 'state' => $status, 'status' => $status));
    }
}