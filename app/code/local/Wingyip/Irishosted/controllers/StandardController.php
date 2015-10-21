<?php

class Wingyip_Irishosted_StandardController extends Mage_Core_Controller_Front_Action
{
	
	/**
     * Order instance
     */
    protected $_order;

    /**
     *  Get order
     *
     *  @return  Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null) {
        }
        return $this->_order;
    }
	
	 /**
     * When a customer chooses IrisHosted on Checkout/Payment page
     *
     */
    public function redirectAction()
    {
	
		 // Load Order
        $order_id = Mage::getSingleton('checkout/session')->getLastRealOrderId();

        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($order_id);
        if (!$order->getId()) {
            Mage::throwException('No order for processing found');
        }
		
		
	
        $session = Mage::getSingleton('checkout/session');
        $session->setIrishostedStandardQuoteId($session->getQuoteId());
        $this->getResponse()->setBody($this->getLayout()->createBlock('irishosted/standard_redirect')->toHtml());
        $session->unsQuoteId();
        $session->unsRedirectUrl();
    }
	
	
	public function responseAction(){
		
		$response = $_REQUEST;
		
		Mage::getModel('irishosted/irishosted')->debugLog("responseAction is called");	
		$response2 = implode("|", $response);
		Mage::getModel('irishosted/irishosted')->debugLog($response2);
	
	}	
	
	/**
     * When a customer cancel payment from paypal.
     */
    public function cancelAction()
    {
    
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getPaypalStandardQuoteId(true));
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                $order->cancel()->save();
            }
            Mage::helper('paypal/checkout')->restoreQuote();
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * when paypal returns
     * The order information at this point is in POST
     * variables.  However, you don't want to "process" the order until you
     * get validation from the IPN.
     */
    public function  successAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getPaypalStandardQuoteId(true));
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        $this->_redirect('checkout/onepage/success', array('_secure'=>true));
    }
}
