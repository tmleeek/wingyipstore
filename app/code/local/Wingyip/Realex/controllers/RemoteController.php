<?php
/**
 * Wingyip_Realex extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Wingyip
 * @package    Wingyip_Realex
 
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Wingyip
 * @package    Wingyip_Realex
 * @author     Wingyip
 */
class Wingyip_Realex_RemoteController extends Mage_Core_Controller_Front_Action
{
        /**
     * @return
     */
    public function failureAction(){
		$session = Mage::getSingleton('checkout/session');
        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();

        if (!$lastQuoteId || !$lastOrderId) {
            $this->_redirect('checkout/cart');
            return;
        }

        $order = Mage::getModel('sales/order')->loadByAttribute('entity_id', $lastOrderId);

       	if ($order->getId()) {
        	//$order->cancel()->save();
        	$order->addStatusToHistory('canceled', $session->getErrorMessage())->save();
	    }

        $this->_redirect('checkout/onepage/failure');
        return;

    }
}