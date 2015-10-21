<?php

class MDN_Quotation_QuoteController extends Mage_Core_Controller_Front_Action
{


    /**
     * Check if quote belong to current customer
     * @param <type> $quoteId
     * @return <type>
     */
    protected function checkQuoteOwner($quote)
    {
        $customerId = Mage::Helper('customer')->getCustomer()->getId();
        if ($quote->getcustomer_id() != $customerId)
            $this->_redirect('');
    }

    /**
     * Quote view
     */
    public function viewAction()
    {
        try {
            $QuoteId = $this->getRequest()->getParam('quote_id');
            $Quote = Mage::getModel('Quotation/Quotation')->load($QuoteId);
            Mage::getSingleton('core/session')->setQuotation($Quote);
            $this->checkQuoteOwner($Quote);
            $this->loadLayout();
            $this->renderLayout();
        } catch (Exception $ex) {
            Mage::getSingleton('customer/session')->addError($ex->getMessage());
            $this->_redirect('*/*/List');
        }
    }

    /**
     * Print quote
     */
    public function printAction()
    {
        $QuoteId = $this->getRequest()->getParam('quote_id');
        $quote = Mage::getModel('Quotation/Quotation')->load($QuoteId);
        $this->checkQuoteOwner($quote);
        try {
            $this->loadLayout();
            //$quote->commit();
            $pdf = Mage::getModel('Quotation/quotationpdf')->getPdf(array($quote));
            $name = Mage::helper('quotation')->__('quotation_') . $quote->getincrement_id() . '.pdf';
            $this->_prepareDownloadResponseV2($name, $pdf->render(), 'application/pdf');
        } catch (Exception $ex) {
            Mage::getSingleton('checkout/session')->addError($ex->getMessage());
            $this->_redirect('Quotation/Quote/View', array('quote_id' => $QuoteId));
        }
    }

    /**
     * @param $quote
     * @param $quotation
     * @return add product to quote
     */
    protected function addProducts($quote, $quotation)
    {
        foreach ($quotation->getItems() as $item) {
            if ($item->getproduct_id()) {
                $product = Mage::getModel('catalog/product')->load($item->getproduct_id());
                $qty = array('qty' => $item->getQty());
                $quote->addProduct($product, new Varien_Object($qty));
            }
        }
        return $this;
    }

    /**
     * @param $quote
     * @param $address
     * @return save Shipping method
     */
    protected function saveShippingMethod($quote, $address)
    {

        $shippingQuoteMethod = Mage::helper('quotation')->getShippingQuote();
        $quote->getBillingAddress()->addData($address);
        $shippingAddress = $quote->getShippingAddress()->addData($address);
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
        //Get all Shipping Rate
        foreach ($shippingAddress->getAllShippingRates() as $rate) {
            if ($rate->getCarrier() == $shippingQuoteMethod) {
                //set Shipping Method
                $shippingAddress->setShippingMethod($rate->getCode())
                    ->setLimitCarrier($rate->getCarrier())
                    ->setShippingDescription($rate->getMethodDescription())
                    ->setCollectShippingRates(true)
                    ->collectTotals();
            }
        }
        //Set Payment Method
        $shippingAddress->setPaymentMethod('realex');
        return $this;
    }

    /**
     * @param $quoteId
     * @param $order
     * @return set status order and set session
     */
    protected function saveStatusOrder($quoteId, $order)
    {
        if ($order) {
            Mage::getModel('realex/remote')->authorize($order->getPayment(), $order->getBaseTotalDue());
            //Set status for Order
            $order->setStatus(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW);
            $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW);
            $order->save();

            //Create session for use when redirect success or faild page
            $session = $this->getOnepage()->getCheckout();
            $session->setLastQuoteId($order->getQuote()->getId());
            $session->setLastOrderId($order->getId());
            $session->setLastSuccessQuoteId($order->getQuote()->getId());

            Mage::getSingleton('checkout/session')->setLastRealOrderId($order->getIncrementId());
            Mage::getSingleton('checkout/session')->setQuotationId($quoteId);
        }
        return $this;
    }

    /**
     * Create New Order With Quote
     */
    public function commitAction()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        $data = $this->getRequest()->getParam('payment');
        $data['method'] = 'realex';
        $quotation = Mage::getModel('Quotation/Quotation')->load($quoteId);
        $address = Mage::getModel('customer/address')->load($quotation->getAddressId());
        
        if (!Mage::helper('ecommage_rewriteopc')->validatePostcodeUK($address->getPostcode()) && $address->getCountryId() == 'GB') {
            Mage::getSingleton('customer/session')->addError('Invalid Postcode. For example LE19 3LY or LE193LY.');
            $this->_redirect('customer/address/edit/', array('id' => $quotation->getAddressId()));
            return;
        }
        Mage::getSingleton('core/session')->setQuoteDetails($quoteId);
        Mage::getSingleton("core/session")->setShippingSkuQuote($quotation->getShippingSku());

        $this->checkQuoteOwner($quotation);
        try {

            $customer = Mage::getModel('customer/customer');
            $customer->load($quotation->getCustomerId());
            $quote = Mage::getModel('sales/quote');
            $quote->assignCustomer($customer);

            //Get all item in Quotation
            $this->addProducts($quote, $quotation);

            //Save Shipping Method
            $this->saveShippingMethod($quote, $address);

            //Import credit card to payment method realex
            $quote->getPayment()->importData($data);
            $quote->collectTotals()->save();

            //Create order with quote
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();

            //get order current
            $order = $service->getOrder();
            $this->saveStatusOrder($quoteId, $order);
            if($result=Mage::getSingleton('checkout/session')->getResultPayment()){
                if($result==='APPROVED'){
                    $quotation->setStatus('expired');
                    $quotation->save();
                    Mage::getSingleton('checkout/session')->unsResultPayment();
                    $this->_redirect('checkout/onepage/success');
                }else{
                    $this->_redirect('realex/remote/failure');
                }
            }else{
                $this->_redirect('realex/ACS');
            }
            return $this;

        } catch (Exception $ex) {
            Mage::getSingleton('checkout/session')->addError($ex->getMessage());
            $this->_redirect('realex/remote/failure');
        }
    }


    public function saveShippingMethodAction()
    {
        /* $billingAddress = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();
         $quotation = Mage::getSingleton('core/session')->getQuotation();
         $shipping_sku = $quotation->getShippingSku();

         $zipcodeMaxLength = Mage::getStoreConfig('carriers/productmatrix/zipcode_max_length') ? Mage::getStoreConfig('carriers/productmatrix/zipcode_max_length') : self::ZIP_CODE_MAX_LENGTH;
         if('BR' == $billingAddress->getCountryId()){
             $splitPostcode = explode('-',$billingAddress->getPostcode());
             $postcode = $splitPostcode[0].$splitPostcode[1];
         }else{
             $postcode = substr($billingAddress->getPostcode(), 0, $zipcodeMaxLength);
         }

         $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
         $sql        = "Select * from ".Mage::getSingleton('core/resource')->getTableName('productmatrix_shipping/productmatrix')." Where shipping_sku = '{$shipping_sku}' and dest_zip='{$postcode}'";
         $rows       = $connection->fetchRow($sql);

         $rates = Mage::getModel('sales/quote_address_rate')->getResourceCollection()->addFieldToFilter('method_title',array('like'=>'%'.$rows['delivery_type'].'%'));
         $method = $rates->getFirstItem()->getCode();

         if ($this->getRequest()->isPost()) {
             $data = $this->getRequest()->getPost('shipping_method', '');
             //$result = $this->getOnepage()->saveShippingMethod($data);
             $result = $this->getOnepage()->saveShippingMethod($method);
             // $result will contain error data if shipping method is empty

             //echo get_class($this->getOnepage()->getQuote()->getShippingAddress());

             //$rate = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingRateByCode($method);

             //print_r($rate);
             //die;
             */
        $result = false;
        if (!$result) {
            Mage::dispatchEvent(
                'checkout_controller_onepage_save_shipping_method',
                array(
                    'request' => $this->getRequest(),
                    'quote' => $this->getOnepage()->getQuote()));
            $this->getOnepage()->getQuote()->collectTotals();
            //	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

            $result['goto_section'] = 'payment';
            $result['update_section'] = array(
                'name' => 'payment-method',
                'html' => $this->_getPaymentMethodsHtml()
            );
        }
        $this->getOnepage()->getQuote()->collectTotals()->save();

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        // }
    }

    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    protected function _getPaymentMethodsHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_paymentmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    /**
     * Custom download response method for magento multi version compatibility
     */
    protected function _prepareDownloadResponseV2($fileName, $content, $contentType = 'application/octet-stream')
    {
        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', strlen($content))
            ->setHeader('Content-Disposition', 'attachment; filename=' . $fileName)
            ->setBody($content);
    }

    /**
     * Display quotes grid
     */
    public function ListAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Redirect customer to authentication page if not logged in and action = CreateRequest
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $action = $this->getRequest()->getActionName();
        if ($action == 'RequestFromCart') {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                Mage::getSingleton('customer/session')->addError($this->__('You must be logged in to request for a quotation.'));
                Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('Quotation/Quote/RequestFromCart', array('_current' => true)));
                $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
            }
        }
        if ($action == 'IndividualRequest') {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                Mage::getSingleton('customer/session')->addError($this->__('You must be logged in to request for a quotation.'));
                Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('Quotation/Quote/IndividualRequest', array('_current' => true, 'product_id' => $this->getRequest()->getParam('product_id'))));
                $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
            }
        }

        return $this;
    }

    /**
     * Return an array with quote options seralized for quotation module
     *
     * @param unknown_type $quoteItem
     */
    private function getQuoteOptions($quoteItem)
    {
        $retour = array();

        if ($optionIds = $quoteItem->getOptionByCode('option_ids')) {
            $options = array();
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $quoteItem->getProduct()->getOptionById($optionId)) {

                    $quoteItemOption = $quoteItem->getOptionByCode('option_' . $option->getId());

                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setQuoteItemOption($quoteItemOption);

                    $retour[$option->getId()] = $quoteItemOption->getValue();
                }
            }
        }

        $retour = Mage::helper('quotation/Serialization')->serializeObject($retour);
        return $retour;
    }

    /**
     * Authenticate customer, add quote to cart and redirect to cart
     *
     */
    public function DirectAuthAction()
    {
        $quote_id = $this->getRequest()->getParam('quote_id');
        $security_key = $this->getRequest()->getParam('security_key');
        $helper = Mage::helper('quotation/DirectAuth');
        $quote = $helper->getQuote($quote_id, $security_key);

        try {
            if ($quote == null)
                throw new Exception($this->__('Request invalid'));

            //authenticate customer
            $helper->authenticateCustomer($quote);

            //go in quote
            $this->_redirect('Quotation/Quote/View', array('quote_id' => $quote_id));
        } catch (Exception $ex) {
            Mage::getSingleton('customer/session')->addError($ex->getMessage());
            $this->_redirect('');
        }
    }

    //*********************************************************************************************************************************************************
    //*********************************************************************************************************************************************************
    //Customer REQUEST
    //*********************************************************************************************************************************************************
    //*********************************************************************************************************************************************************

    /**
     * Create a quote inquiry with cart's products
     *
     */
    public function RequestFromCartAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Create a quote inquiry with cart's products
     *
     */
    public function CreateIndividualRequestAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Quote request for one product
     * Disable add to cart button for individual request products : yes/no
     */
    public function IndividualRequestAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Send textual quote request
     *
     */
    public function SendTextualRequestAction()
    {

        //Create new quotation
        $customerId = Mage::Helper('customer')->getCustomer()->getId();
        $NewQuotation = Mage::getModel('Quotation/Quotation')
            ->setcustomer_id($customerId)
            ->setcaption($this->__('New request'))
            ->setcustomer_msg($this->getRequest()->getPost('description'))
            ->setcustomer_request(1)
            ->setstatus(MDN_Quotation_Model_Quotation::STATUS_CUSTOMER_REQUEST)
            ->save();

        //Notify admin
        $notificationModel = Mage::getModel('Quotation/Quotation_Notification');
        $notificationModel->NotifyCreationToAdmin($NewQuotation);

        //confirm & redirect
        Mage::getSingleton('customer/session')->addSuccess(__('You quotation request has been successfully sent. You will be notified once store administrator will have reply to your request'));
        $this->_redirect('Quotation/Quote/List/');
    }

    /**
     *
     */
    public function SendIndividualRequestAction()
    {
        //Create new quotation
        $customerId = Mage::Helper('customer')->getCustomer()->getId();
        $NewQuotation = Mage::getModel('Quotation/Quotation')
            ->setcustomer_id($customerId)
            ->setcaption($this->__('New request'))
            ->setcustomer_msg($this->getRequest()->getPost('description'))
            ->setcustomer_request(1)
            ->setstatus(MDN_Quotation_Model_Quotation::STATUS_CUSTOMER_REQUEST)
            ->save();

        //Notify admin
        $notificationModel = Mage::getModel('Quotation/Quotation_Notification');
        $notificationModel->NotifyCreationToAdmin($NewQuotation);

        //add product
        $productId = $this->getRequest()->getPost('product_id');
        $qty = $this->getRequest()->getPost('qty');
        $options = $this->getRequest()->getPost('options');
        $quoteItem = $NewQuotation->addProduct($productId, $qty);
        $quoteItem->setoptions($options)->save();

        //confirm & redirect
        Mage::getSingleton('customer/session')->addSuccess(__('You quotation request has been successfully sent. You will be notified once store administrator will have reply to your request'));
        $this->_redirect('Quotation/Quote/List/');

    }

    /**
     * Submit request from cart
     */
    public function SendRequestFromCartAction()
    {

        $address_id = $this->getRequest()->getParam('address_id', 0);
        if ($address_id == 0) {
            Mage::getSingleton('customer/session')->addError($this->__('You must have shipping address to request for a quotation.'));
            $this->_redirect('customer/address/new');
            return;
        }
        //Create new quotation
        $customerId = Mage::Helper('customer')->getCustomer()->getId();
        $NewQuotation = Mage::getModel('Quotation/Quotation')
            ->setcustomer_id($customerId)
            ->setcaption($this->__('New request'))
            ->setcustomer_msg($this->getRequest()->getPost('description'))
            ->setAddressId($this->getRequest()->getPost('address_id'))
            ->setcustomer_request(1)
            ->setstatus(MDN_Quotation_Model_Quotation::STATUS_CUSTOMER_REQUEST)
            ->save();

        //add products to quote
        $cartProducts = Mage::helper('checkout/cart')->getCart()->getItems();
        foreach ($cartProducts as $cartProduct) {

            //skip group products
            if (($cartProduct->getProduct()->gettype_id() == 'configurable') || ($cartProduct->getProduct()->gettype_id() == 'bundle') || ($cartProduct->getProduct()->gettype_id() == 'grouped'))
                continue;

            //set qty
            $qty = $cartProduct->getqty();
            if ($cartProduct->getParentItem())
                $qty = $cartProduct->getqty() * $cartProduct->getParentItem()->getqty();

            //add product
            $quoteItem = $NewQuotation->addProduct($cartProduct->getproduct_id(), $qty);

            //set options
            $quoteItem->setoptions($this->setQuotItemOptionFromCartItem($cartProduct))->save();
        }

        //Notify admin
        $notificationModel = Mage::getModel('Quotation/Quotation_Notification');
        $notificationModel->NotifyCreationToAdmin($NewQuotation);

        //empty cart if configured
        if (Mage::getStoreConfig('quotation/cart_options/empty_cart_after_quote_request'))
            Mage::helper('quotation/Cart')->emptyCart(true);

        //confirm & redirect
        Mage::getSingleton('customer/session')->addSuccess(__('You quotation request has been successfully sent. You will be notified once store administrator will have reply to your request'));
        $this->_redirect('Quotation/Quote/List/');
    }

    /**
     *
     */
    protected function setQuotItemOptionFromCartItem($cartProduct)
    {
        $selectedOptions = array();

        if ($optionIds = $cartProduct->getOptionByCode('option_ids')) {
            $options = array();
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $cartProduct->getProduct()->getOptionById($optionId)) {
                    $quoteItemOption = $cartProduct->getOptionByCode('option_' . $option->getId());
                    $group = $option->groupFactory($option->getType())
                        ->setOption($option)
                        ->setQuoteItemOption($quoteItemOption);
                    $selectedOptions[$optionId] = $quoteItemOption->getValue();
                }
            }
        }

        return Mage::helper('quotation/Serialization')->serializeObject($selectedOptions);
    }

    //*********************************************************************************************************************************************************
    //*********************************************************************************************************************************************************
    //ANONYMOUS REQUEST
    //*********************************************************************************************************************************************************
    //*********************************************************************************************************************************************************

    /**
     * Display quote request form for anonymous
     *
     */
    public function anonymousQuoteRequestAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Download attached PDF
     */
    public function DownloadAdditionalPdfAction()
    {
        $QuoteId = $this->getRequest()->getParam('quote_id');
        $quote = Mage::getModel('Quotation/Quotation')->load($QuoteId);
        $this->checkQuoteOwner($quote);
        $filePath = Mage::helper('quotation/Attachment')->getAttachmentPath($quote);
        $content = file_get_contents($filePath);
        $this->_prepareDownloadResponseV2($quote->getadditional_pdf() . '.pdf', $content, 'application/pdf');
    }

}
