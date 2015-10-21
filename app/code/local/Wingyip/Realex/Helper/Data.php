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
class Wingyip_Realex_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Prepare array with information about used product qty and product stock item
     * result is:
     * array(
     *  $productId  => array(
     *      'qty'   => $qty,
     *      'item'  => $stockItems|null
     *  )
     * )
     * @param array $relatedItems
     * @return array
     */
    private function _getProductsQty($relatedItems)
    {
        $items = array();
        foreach ($relatedItems as $item) {
            $productId  = $item->getProductId();
            if (!$productId) {
                continue;
            }
            $children = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $this->_addItemToQtyArray($childItem, $items);
                }
            } else {
                $this->_addItemToQtyArray($item, $items);
            }
        }
        return $items;
    }
    
    
    
        /**
     * Adds stock item qty to $items (creates new entry or increments existing one)
     * $items is array with following structure:
     * array(
     *  $productId  => array(
     *      'qty'   => $qty,
     *      'item'  => $stockItems|null
     *  )
     * )
     *
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @param array &$items
     */
    private function _addItemToQtyArray($quoteItem, &$items)
    {
        $productId = $quoteItem->getProductId();
        if (!$productId)
            return;
        if (isset($items[$productId])) {
            $items[$productId]['qty'] += $quoteItem->getTotalQty();
        } else {
            $stockItem = null;
            if ($quoteItem->getProduct()) {
                $stockItem = $quoteItem->getProduct()->getStockItem();
            }
            $items[$productId] = array(
                'item' => $stockItem,
                'qty'  => $quoteItem->getTotalQty()
            );
        }
    }
    
    
    /**
     * @param $orderIncrementId
     * @return string
     */
    public function createInvoice(string $orderIncrementId){
  		$_order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

		if($_order->canInvoice()) {
			$invoiceId = Mage::getModel('sales/order_invoice_api')->create($_order->getIncrementId(), array(), 'Invoice Created', true, true);

			$invoice = Mage::getModel('sales/order_invoice_api')->capture($invoiceId);
		}
    }

    public function getDateFromTimestamp($timestamp){
        $year = substr($timestamp, 0, 4);
        $month = substr($timestamp, 4, 2);
        $day = substr($timestamp, 6, 2);
        $hour = substr($timestamp, 8, 2);
        $minutes = substr($timestamp, 10, 2);
        $seconds = substr($timestamp, 12, 2);
        $date = $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minutes . ':' . $seconds;
        Mage::log($date);
        return strtotime($date);
    }
    
    public function getCustomerMessage($block, $order, $response){
    	$variables = array();
    	$variables['order'] = $order;
    	$variables['response'] = $response;
    	$filter = Mage::getModel('core/email_template_filter');
    	$filter->setVariables($variables); 
    	return $filter->filter($block->toHtml());
    }
    
    public function validateQuote() {
        $quote = Mage::getSingleton('realex/api_payment')->getQuote();

        if (!$quote->isVirtual()) {
            $address = $quote->getShippingAddress();
            $addressValidation = $address->validate();
            if ($addressValidation !== true) {
                Mage::throwException($this->__("\nPlease check shipping address information. \n%s", implode("\n", $addressValidation)));
            }
            $method = $address->getShippingMethod();
            $rate = $address->getShippingRateByCode($method);
            if (!$quote->isVirtual() && (!$method || !$rate)) {
                Mage::throwException($this->__('Please specify shipping method.'));
            }
        }

        $addressValidation = $quote->getBillingAddress()->validate();
        if ($addressValidation !== true) {
            Mage::throwException($this->__("\nPlease check billing address information. \n%s", implode("\n", $addressValidation)));
        }

        if (!($quote->getPayment()->getMethod())) {
            Mage::throwException($this->__('Please select valid payment method.'));
        }

        //Stock check, redirect to cart if can't continue
        if (!$quote->getInventoryProcessed()) {

            $items = $this->_getProductsQty($quote->getAllItems());

            try {

                Mage::getModel('cataloginventory/stock')->registerProductsSale($items);
                //Set flag
                $quote->setInventoryProcessed(true);

                //Rollback if all is OKAY
                Mage::getModel('cataloginventory/stock')->revertProductsSale($items);
                // Clear flag
                $quote->setInventoryProcessed(false);

            }catch(Exception $ex) {
                throw new Exception('REDIRECT_CART');
            }

        }

    }
    public function getOnepage() {
        return Mage::getSingleton('checkout/type_onepage');
    }
    public function deleteQuote() {
        $quoteID = $this->getOnepage()->getQuote()->getId();

        if ($quoteID) {
            try {
                Mage::getModel('sales/quote')->load($quoteID)->setIsActive(false)
                        ->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }
    
    
    /**
     * Make sure addresses will be saved without validation errors
     */
    public function ignoreAddressValidation($quote) {

        $ignoreAddressValidation = (int)Mage::getStoreConfig('payment/realex/ignore_address_validation');

        if(1 === $ignoreAddressValidation) {
            $quote->getBillingAddress()->setShouldIgnoreValidation(true);
            if (!$quote->getIsVirtual()) {
                $quote->getShippingAddress()->setShouldIgnoreValidation(true);
            }
        }
    }
    public function creatingAdminOrder() {
        $controllerName = Mage::app()->getRequest()->getControllerName();
        return ($controllerName == 'sales_order_create' || $controllerName == 'adminhtml_sales_order_create' || $controllerName == 'sales_order_edit' || $controllerName == 'orderspro_order_edit');
    }
      
}


