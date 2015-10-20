<?php

/**
 * Checkout events observer
 *
 * @category   Wingyip
 * @package    Wingyip_Realex
 * @author     Wingyip <info@wingyip.com>
 */
class Wingyip_Realex_Model_Observer_Checkout extends Wingyip_Realex_Model_Observer {

    protected function _getLastOrderId() {
        return (int) (Mage::getSingleton('checkout/type_onepage')->getCheckout()->getLastOrderId());
    }

    /**
     * Save Magemaven Order Comments
     * @param  $observer
     * @return Wingyip_Realex_Model_Observer_Checkout
     */
    public function saveMagemavenOrderComment($observer) {

        //Magemaven_OrderComment
        $comment = $this->getSession()->getOrderComments(true);
        if($comment) {

            $order = $observer->getEvent()->getOrder();

            if(is_object($order)) {
                $order->setCustomerComment($comment);
                $order->setCustomerNoteNotify(true);
                $order->setCustomerNote($comment);
            }
        }
        //Magemaven_OrderComment

        return $this;
    }

    /**
     * Clear Realex session when loading onepage checkout
     */
    public function controllerOnePageClear($o) {

        /**
         * Delete register and guest cards when loading checkout
         */
        try {
            $sessionCards = Mage::helper('realex/token')->getSessionTokens();
            if ($sessionCards->getSize() > 0) {
                foreach ($sessionCards as $_c) {
                    if ($_c->getCustomerId() == 0) {

                        $delete = Mage::getModel('realex/sagePayToken')
                                ->removeCard($_c->getToken(), $_c->getProtocol());
                        if ($delete['Status'] == 'OK') {
                            $_c->delete();
                        }
                    }
                }
            }
        } catch (Exception $ex) {
            Mage::logException($ex);
        }
        /**
         * Delete register and guest cards when loading checkout
         */

        $this->getSession()->clear();
    }

	public function controllerMultishippingClear($o)
	{
		$this->getSession()->clear();
	}

	public function controllerOnePageSuccess($o)
	{

        //Capture data from Sage Pay API
        $orderId = $this->_getLastOrderId();

        $this->_getTransactionsModel()->addApiDetails($orderId);

        /**
         * Delete session tokencards if any
         */
        $vdata = Mage::getSingleton('core/session')->getVisitorData();

        $sessionCards = Mage::getModel('realex2/realex_tokencard')->getCollection()
                ->addFieldToFilter('visitor_session_id', (string) $vdata['session_id']);

        if ($sessionCards->getSize() > 0) {
            foreach ($sessionCards as $_c) {
                if ($_c->getCustomerId() == 0) {
                    $_c->delete();
                }
            }
        }

        //Associate Customer ID for DIRECT transactions without 3D and REGISTER checkout
        $tokenId = $this->getSession()->getLastSavedTokenccid(true);
        if((int)$tokenId) {
            $token = Mage::getModel('realex2/realex_tokencard')->load($tokenId);
            if($token->getId() && ($token->getId() == $tokenId) && !$token->getCustomerId()) {

                $customerId = Mage::getModel('sales/order')->load($orderId)->getCustomerId();

                $token->setCustomerId($customerId)
                        ->save();
            }
        }

        /**
         * Delete session tokencards if any
         */
        $this->getSession()->clear();
    }

    public function sendPaymentFailedEmail($observer) {
        //Check if enabled in config.
        if(0 === (int)Mage::getStoreConfig('payment/realex/send_payment_failed_emails')) {
            return $this;
        }

        $quote   = $observer->getEvent()->getQuote();
        $message = $observer->getEvent()->getMessage();

        try {

            Mage::helper('realex/checkout')->sendPaymentFailedEmail($quote, $message);

        } catch(Exception $ex) {
            Mage_Log::logException($ex);
        }

        return $this;
    }
}
