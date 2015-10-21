<?php

class Ecommage_Rewritewingyip_Block_Adminhtml_Sales_Order_View extends Wingyip_Shipping_Block_Adminhtml_Sales_Order_View
{
    public function __construct()
    {
        parent::__construct();
        $order = $this->getOrder();
        if ($order->hasInvoices()) {
            $this->_addButton('print_invoice', array(
                'label' => Mage::helper('sales')->__('Print Invoice'),
                'onclick' => 'setLocation(\'' . $this->getPrintInvoiceUrl($order) . '\')',
                'class' => 'go'
            ));
        }
        $this->_removeButton('order_shiplabel');
        $this->_removeButton('order_ship_status');
    }

    public function getPrintInvoiceUrl($order)
    {
        foreach($order->getInvoiceCollection() as $invoice){
            $invoiceId=$invoice->getId();
        }
        return Mage::helper('adminhtml')->getUrl('adminhtml/sales_order_invoice/print/index/invoice_id/', array('invoice_id' => $invoiceId));
    }

    public function getShipOrderUrl()
    {
        return $this->getUrl('shipping/adminhtml_order/ship');
    }

    public function getExportXmlUrl()
    {
        return $this->getUrl('shipping/adminhtml_order/exportxml');
    }

    public function getShipLabelGenerateUrl()
    {
        return $this->getUrl('shipping/adminhtml_order/generateLabel');
    }
}
			