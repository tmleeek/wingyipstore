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
 
class Wingyip_Realex_Model_Realmpi3dsecure extends Wingyip_Realex_Model_Api_Payment{
    protected $_code = 'realex';
    protected $_formBlockType = 'realex/realmpi3dsecure_form';

    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc = true;
    
     /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setCcType($data->getCcType())
            ->setCcOwner($data->getCcOwner())
            ->setCcLast4(substr($data->getCcNumber(), -4))
            ->setCcNumber($data->getCcNumber())
            ->setCcCid($data->getCcCid())
            ->setCcExpMonth($data->getCcExpMonth())
            ->setCcExpYear($data->getCcExpYear())
            // For Switch/Solo cards
            ->setCcSsIssue($data->getCcSsIssue())
            ->setCcSsStartMonth($data->getCcSsStartMonth())
            ->setCcSsStartYear($data->getCcSsStartYear());
        return $this;
    }

    /**
     * @param $name
     * @return Wingyip_Realex_Remote_Form
     */
    public function createFormBlock($name)
    {   
        $block = $this->getLayout()->createBlock('realex/realmpi3dsecure_form', $name)
            ->setMethod('realex_realmpi3dsecure')
            ->setPayment($this->getPayment())
            ->setTemplate('realex/realmpi3dsecure/form.phtml');

        return $block;
    }

    /**
     * @param $type
     * @return bool
     */
    public function OtherCcType($type){

        return $type=='OT' || $type=='LA';
    }

    /**
     * @param Varien_Object $payment
     * @param $amount
     * @return bool|Wingyip_Realex_Model_Remote
     */
    public function authorize(Varien_Object $payment, $amount){
          
        
        $error = false;
        $session = Mage::getSingleton('checkout/session');
        $session->setErrorUrl(false);
        $session->setErrorMessage(false);
    
        $url = $this->getConfigData('authurl');
        
        if($amount > 0){
            $request = $this->_buildRequest($payment, $amount, 0);
            $error = $this->processResponse($this->_postRequest($request, $url), $payment, 'authorize');
        }else{
            $error = Mage::helper('paygate')->__('Invalid amount for authorization.');
        }
        
        if($error == false && $this->getConfigData('capture')){
            $payment->capture(null);
        }
                
        if ($error != false) {
            if(Mage::getSingleton('checkout/session')->getQuote()->getIsMultiShipping()){
                Mage::throwException($error);
            }
            $session->setErrorUrl(true);
            $block = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId('realex_error');
            $session->setErrorMessage(Mage::helper('realex')->getCustomerMessage($block, $payment->getOrder(), Mage::getModel('realex/realex')->getCollection()->addFieldToFilter('order_id', $payment->getOrder()->getIncrementId())->getFirstItem()));
            return false;
        }
           
        return $this;
    }
    
    
    public function verifySecurityCode(Varien_Object $payment , $pareq, $ACSURL){
       
       
         
        $timestamp = $merchantid = $account = $orderid = $currency = $cardnumber = $expdate = $cardname = $cardtype = $issueno = $cvc = $customerID = $productID = $billingPostcode = $billingCountry =    $shippingPostcode = $shippingCountry = $sha1hash = '';
        
        
        //Get data from Magento Admin Backend for Realex Payment Module    
        $merchantid = $this->getConfigData('login');
        $secret = $this->getConfigData('pwd');
        $account = $this->getConfigData('account');

        //Get information from order and set the appropriate variables.
        $order = $payment->getOrder();
        $billing = $order->getBillingAddress();

        if($this->getConfigData('currency') == 'display'){
            $currency = $order->getOrderCurrencyCode();
            $amount = $order->getTotalDue();
        }else{
            $currency = $order->getBaseCurrencyCode();    
        }
        
        $presind = $this->getConfigData('useccv');

        /* multiplied by 100 because Realex deals in cents as the base unit
         * while magento uses euro
         */
        $amount = $amount * 100;
        
        $cardnumber = $payment->getCcNumber();
        $cardname = $payment->getCcOwner();
        if($payment->getCcCid()){
            $cvc = $payment->getCcCid();
        }else{
            $cvc = '';
        }

        $cardtype = $this->convertCcType($payment->getCcType());
        if($cardtype == 'amex'){
            $account = $this->getConfigData('amexAccount');
        }

        /* Converts expiry date stored by Magento to a two-digit month and two digit
         * year format without a seperator such as a hyphen.
         */
        $expdate = sprintf('%02d%02d', $payment->getCcExpMonth(), substr($payment->getCcExpYear(), 2));

        $issueno = $payment->getCcSsIssue();
        $orderid = $order->getIncrementId();
        $customerID = $order->getCustomerId();
        
        $md ="orderid=$orderid&cardnumber=$cardnumber&cardname=$cardname&cardtype=$cardtype&currency=$currency&amount=$amount&expdate=$expdate";
        
        $createformModel = Mage::getModel('realex/createform');
           
           
        $formUrl = Mage::getUrl()."checkout/onepage/";   
        $createformModel->addHeader('ACS Form',$ACSURL,$formUrl,$md,$pareq);
        // Add the footer to the page
        $createformModel->addFooter(date('Y'), 'ACS Form');

        // Display the page
        echo $createformModel->get();
    }
    

    /**
     * @param Varien_Object $payment
     * @param $amount
     * @return Wingyip_Realex_Model_Remote
     */
    public function capture(Varien_Object $payment, $amount){
        
        if (!$payment->getLastTransId()) {    
            $error = false;
            $session = Mage::getSingleton('checkout/session');
            $session->setErrorUrl(false);
            $session->setErrorMessage(false);
            $url = $this->getConfigData('authurl');
            if($amount > 0){
                $request = $this->_buildRequest($payment, $amount, 0);
                $error = $this->processResponse($this->_postRequest($request, $url), $payment, 'authorize');
            }else{
                $error = Mage::helper('paygate')->__('Invalid amount for authorization.');
            }
            if ($error != false) {
                if(Mage::getSingleton('checkout/session')->getQuote()->getIsMultiShipping()){
                    Mage::throwException($error);
                }
                $session->setErrorUrl(true);
                $block = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId('realex_error');
                $session->setErrorMessage(Mage::helper('realex')->getCustomerMessage($block, $payment->getOrder(), Mage::getModel('realex/realex')->getCollection()->addFieldToFilter('order_id', $payment->getOrder()->getIncrementId())->getFirstItem()));
                return false;
            }       
        }
        
		$error = false;
		$session = Mage::getSingleton('checkout/session');
		$session->setErrorUrl(false);
		$session->setErrorMessage(false);
		$url = "https://remote.globaliris.com/realmpi";
		if($amount > 0){
			
			if ($payment->getLastTransId()) {
				$request = $this->_buildSettleRequest($payment);
			}else{
				$request = $this->_buildRequest($payment, $amount, 1);
			}	    
		    $error = $this->processResponse($this->_postRequest($request, $url), $payment, 'capture');
		}else{
              $error = Mage::helper('paygate')->__('Invalid amount for authorization.');
        }
		if ($error != false) {
			$session->setErrorUrl(true);
			$session->setErrorMessage($error);
        }
        
        return $this;
    }

    /**
     * @param Varien_Object $document
     * @return bool
     */
    public function canVoid(Varien_Object $document){
    	return true;
    }

    /**
     * @param Varien_Object $payment
     * @return void
     */
    public function cancel(Varien_Object $payment){
    	$this->void($payment);
    }

    /**
     * @param Varien_Object $payment
     * @return Wingyip_Realex_Model_Remote
     */
    public function void(Varien_Object $payment){
		$request = $this->_buildAdminRequest($payment, 'void');
		$url = "https://remote.globaliris.com/realmpi";
		$response = $this->_postRequest($request, $url);
	    $xml = new SimpleXMLElement($response);
		if($xml->result == '00'){		
			return $this;
		}else{
			Mage::throwException($xml->result . ': ' . $xml->message);
		}
    }

    /**
     * @param Varien_Object $payment
     * @param $amount
     * @return Wingyip_Realex_Model_Remote
     */
    public function refund(Varien_Object $payment, $amount){
		$request = $this->_buildRebateRequest($payment, $amount);
		$url = "https://remote.globaliris.com/realmpi";
		$response = $this->_postRequest($request, $url);
	    $xml = new SimpleXMLElement($response);
		if($xml->result == '00'){		
			return $this;
		}else{
			Mage::throwException($xml->result . ': ' . $xml->message);
		}
    }

    /**
     * @param Varien_Object $payment
     * @param $amount
     * @param $autosettle
     * @return string
     */
    protected function _buildRequest(Varien_Object $payment, $amount, $autosettle){
		//Get data from Magento Admin Backend for Realex Payment Module
		$merchantid = $this->getConfigData('login');
		$secret = $this->getConfigData('pwd');
		$account = $this->getConfigData('account');

		//Get information from order and set the appropriate variables.
	    $order = $payment->getOrder();
        $billing = $order->getBillingAddress();

		if($this->getConfigData('currency') == 'display'){
	        $currency = $order->getOrderCurrencyCode();
            $amount = $order->getTotalDue();
		}else{
			$currency = $order->getBaseCurrencyCode();	
		}
		
		$presind = $this->getConfigData('useccv');

		/* multiplied by 100 because Realex deals in cents as the base unit
         * while magento uses euro
         */
        $amount = $amount * 100;
        
        $cardnumber = $payment->getCcNumber();
        $cardname = $payment->getCcOwner();
        if($payment->getCcCid()){
            $cvc = $payment->getCcCid();
        }else{
            $cvc = '';
        }

        $cardtype = $this->convertCcType($payment->getCcType());
        if($cardtype == 'amex'){
            $account = $this->getConfigData('amexAccount');
        }

        /* Converts expiry date stored by Magento to a two-digit month and two digit
         * year format without a seperator such as a hyphen.
         */
        $expdate = sprintf('%02d%02d', $payment->getCcExpMonth(), substr($payment->getCcExpYear(), 2));

        $issueno = $payment->getCcSsIssue();
        		
        // The Timestamp is created here and used in the digital signature
        $timestamp = strftime("%Y%m%d%H%M%S");
        mt_srand((double)microtime()*1000000);

        $ccType=$payment->getCcType();
        if($ccType == 'VI'){
            $eci = 6;
        }else if($ccType == 'MC'){
            $eci = 1;
        }
        $realexObject = $this->getRealexObject($this->getCheckout()->getQuote()->getId());
        if(is_object($realexObject)){
            
             $orderid  =  $realexObject->getOrderId();
             $cavv =   $realexObject->getCavv();
             $xid =   $realexObject->getXid();
             $eci =   $realexObject->getEci();
             
        }else{
           $orderid = $this->getCheckout()->getQuote()->getId(); 
        }
        //$orderid = $order->getIncrementId();

        /* This section of code creates the md5hash that is needed
        $tmp = "$timestamp.$merchantid.$orderid.$amount.$currency.$cardnumber";
        $md5hash = md5($tmp);
        $tmp = "$md5hash.$secret";
        $md5hash = md5($tmp);
		*/

		// This section of code creates the shahash that is needed
        $tmp = "$timestamp.$merchantid.$orderid.$amount.$currency.$cardnumber";
        $sha1hash = sha1($tmp);
        $tmp = "$sha1hash.$secret";
        $sha1hash = sha1($tmp);
		
		$customerID = $order->getCustomerId();
	
		//If there's only one product being bought, add it into the XML.
		$products = array();
        foreach ($order->getItemsCollection() as $item) {
            $products[] = $item->getProductId();
        }

		if(!(count($products)>1)){
	    	$productID = $products[0];
		}

		$billingCountry = $billing->getCountry();
		$billingPostcode = $billing->getPostcode();
		$billingPostcodeNumbers = preg_replace('/[^\d]/', '', $billingPostcode);
		
		$billingStreetName = $billing->getStreet1();
		preg_match('{(\d+)}', $billingStreetName, $m); 		
		if(isset($m[1])){
			$billingStreetNumber = $m[1];
		}else{
			$billingStreetNumber = '';
		}
		
		$billingCode = $billingPostcodeNumbers . '|' . $billingStreetNumber;
		
		if($order->getShippingAddress()){
			$shipping = $order->getShippingAddress();
			$shippingCountry = $shipping->getCountry();
			$shippingPostcode = $shipping->getPostcode();	
		}
        $ip = Mage::getSingleton('checkout/session')->getQuote()->getRemoteIp();
        $xml = " <request timestamp='$timestamp' type='auth'>
        <merchantid>$merchantid</merchantid>
        <account>$account</account>
        <orderid>$orderid</orderid>
        <amount currency='$currency'>$amount</amount>
        <card>
            <number>$cardnumber</number>
            <expdate>$expdate</expdate>
            <type>$cardtype</type>
            <chname>$cardname</chname>
        </card>
        <mpi>
        <cavv>$cavv</cavv>
        <xid>$xid</xid>
        <eci>$eci</eci>
        </mpi>
        <autosettle flag='$autosettle'/>
        <sha1hash>$sha1hash</sha1hash>     
        <tssinfo>
            <custnum>$customerID</custnum>
                <prodid>$productID</prodid>
                <varref></varref>
            <custipaddress>$ip</custipaddress>
            <address type='billing'> 
                <code>$billingCode</code>  
                <country>$billingCountry</country>  
            </address> 
            <address type='shipping'> 
                <code>$shippingPostcode</code>  
                <country>$shippingCountry</country>  
            </address>
        </tssinfo>
</request>";
                    
                    
		    
	    //Prints request xml to /var/log/system.log if in Debug mode
	    if($this->getConfigData('debug')){
			Mage::log($xml);
	    }
	    return $xml;
    }

    /**
     * @param $payment
     * @return string
     */
    protected function _buildSettleRequest(Varien_Object $payment){
    	// The Timestamp is created here and used in the digital signature
        $timestamp = strftime("%Y%m%d%H%M%S");
        mt_srand((double)microtime()*1000000);
        
        $merchantid = $this->getConfigData('login');
		$secret = $this->getConfigData('pwd');
		$account = $this->getConfigData('account');
				
		$orderid = $payment->getCcTransId();
        
       /* 
        $realexObject = $this->getRealexObject($this->getCheckout()->getQuote()->getId());
        if(is_object($realexObject)){
            
             $orderid  =  $realexObject->getOrderId();
            // $cavv =   $realexObject->getCavv();
             //$xid =   $realexObject->getXid();
             //$eci =   $realexObject->getEci();
             
        }else{
           $orderid = $this->getCheckout()->getQuote()->getId(); 
        }
         */
    	
    	// This section of code creates the shahash that is needed
        $tmp = "$timestamp.$merchantid.$orderid...";
        $sha1hash = sha1($tmp);
        $tmp = "$sha1hash.$secret";
        $sha1hash = sha1($tmp);
        
        $pasref = $payment->getCcAvsStatus();
        $authcode = $payment->getCcApproval();
           	
    	$xml = "<request timestamp='$timestamp' type='settle'> 
					<merchantid>$merchantid</merchantid>  
					<account>$account</account>  
					<orderid>$orderid</orderid>  
					<pasref>$pasref</pasref>  
					<authcode>$authcode</authcode>
					<sha1hash>$sha1hash</sha1hash>  
				</request>";
	  
	    //Prints request xml to /var/log/system.log if in Debug mode
	    if($this->getConfigData('debug')){
			Mage::log($xml);
	    }

    	return $xml;
    }

    /**
     * @param $payment
     * @param $amount
     * @return string
     */
    protected function _buildRebateRequest(Varien_Object $payment, $amount){
    	// The Timestamp is created here and used in the digital signature
        $timestamp = strftime("%Y%m%d%H%M%S");
        mt_srand((double)microtime()*1000000);
        
        $merchantid = $this->getConfigData('login');
		$secret = $this->getConfigData('pwd');
		$account = $this->getConfigData('account');
				
		$orderid = $payment->getCcTransId();
        
        $pasref = $payment->getCcAvsStatus();
        $authcode = $payment->getCcApproval();
        
        $refundpwd = $this->getConfigData('refundpwd');
        $refundhash = sha1($refundpwd);
        
        $currency = $payment->getOrder()->getBaseCurrencyCode();
        
        $amount = $amount * 100;
        
       	// This section of code creates the shahash that is needed
        $tmp = "$timestamp.$merchantid.$orderid.$amount.$currency.";
        $sha1hash = sha1($tmp);
        $tmp = "$sha1hash.$secret";
        $sha1hash = sha1($tmp);
            	
    	$xml = "<request timestamp='$timestamp' type='rebate'> 
					<merchantid>$merchantid</merchantid>  
					<account>$account</account>  
					<orderid>$orderid</orderid>  
					<pasref>$pasref</pasref>  
					<authcode>$authcode</authcode>
					<amount currency='$currency'>$amount</amount>
					<refundhash>$refundhash</refundhash>
					<autosettle flag='1' />
					<sha1hash>$sha1hash</sha1hash>  
				</request>";
	  
	    //Prints request xml to /var/log/system.log if in Debug mode
	    if($this->getConfigData('debug')){
			Mage::log($xml);
	    }

    	return $xml;
    }

    /**
     * @param $payment
     * @param $type
     * @return string
     */
    protected function _buildAdminRequest(Varien_Object $payment, $type){
    	// The Timestamp is created here and used in the digital signature
        $timestamp = strftime("%Y%m%d%H%M%S");
        mt_srand((double)microtime()*1000000);
        
        $merchantid = $this->getConfigData('login');
		$secret = $this->getConfigData('pwd');
		$account = $this->getConfigData('account');
				
		$orderid = $payment->getCcTransId();
    	
    	// This section of code creates the shahash that is needed
        $tmp = "$timestamp.$merchantid.$orderid...";
        $sha1hash = sha1($tmp);
        $tmp = "$sha1hash.$secret";
        $sha1hash = sha1($tmp);
        
        $pasref = $payment->getCcAvsStatus();
        $authcode = $payment->getCcApproval();
            	
    	$xml = "<request timestamp='$timestamp' type='$type'> 
					<merchantid>$merchantid</merchantid>  
					<account>$account</account>  
					<orderid>$orderid</orderid>  
					<pasref>$pasref</pasref>  
					<authcode>$authcode</authcode>
					<sha1hash>$sha1hash</sha1hash>  
				</request>";
	  
	    //Prints request xml to /var/log/system.log if in Debug mode
	    if($this->getConfigData('debug')){
			Mage::log($xml);
	    }
    	return $xml;
    }

    /**
     * @param $request
     * @param $url
     * @return Zend_Http_Response
     */
    protected function _postRequest($request, $url){
        
        $client = new Varien_Http_Client($url, array('timeout' => 30));
    	$client->resetParameters(); 
		$client->setMethod('POST');
		$client->setRawData($request, 'text/xml');
        
		$i = 0;
		do {
	        $success = true;
	        $i++;
	        try {
			    $response = $client->request('POST');
	        } catch (Zend_Http_Client_Exception $e) {
	            $success = false;
	        }
    	} while (!$success && $i<5);

	    
        $response = $response->getBody();
        
        //Prints request xml to /var/log/system.log if in Debug mode
		if($this->getConfigData('debug')){
	    	Mage::log($response);
		}
                      
		return $response;
    }
        
    /**
     * The abbreviations that Realex uses for credit card types are different
   	 * in most cases to those used by Magento. So here we do the conversion.
     * It might make more sense to do this elsewhere.
     *
     * @param $type
     * @return string
     */
    protected function convertCcType($type){
		$cardtype = "";
		if($type == 'VI'){
    	    $cardtype = "visa";
	    }else if($type == 'MC'){
	        $cardtype = "mc";
    	}else if($type == 'AE'){
        	$cardtype = "amex";
		}else if($type == 'SS'){
	    	$cardtype = "switch";
		}else if($type == 'LA'){
			$cardtype = "laser";
		}else if($type == 'DI'){
			$cardtype = "diners";
		}else{
		   	Mage::throwException("Incorrect CC Type");
		   	return $cardtype;
		}
		return $cardtype;
    }

    /**
     * @return string
     */
   	public function getOrderPlaceRedirectUrl(){
		$session = Mage::getSingleton('checkout/session');
		$return = '';
		if($session->getErrorUrl()){
			$return = Mage::getUrl('realex/remote/failure');
		}		
		return $return;
    }

     /**
     * @param $response
     * @param $payment
     * @return bool|string
     */
    protected function processResponse($response, Varien_Object $payment, $type){
        $error = false;
        $xml = new SimpleXMLElement($response);

        $this->saveRealexTransaction($xml);
            
        $payment->setLastTransId($xml->orderid)
                ->setCcTransId($xml->orderid);
                        
        if($xml->result != '501'){
            if($type == 'authorize'){
                $payment->setCcApproval($xml->authcode)
                           ->setCcAvsStatus($xml->pasref);
            }
            
            $payment->getOrder()->addStatusToHistory('processing', 'CVN Result: ' . $xml->cvnresult)->save();
            $payment->getOrder()->addStatusToHistory('processing', 'AVS Postcode Response: ' . $xml->avspostcoderesponse)->save();
            $payment->getOrder()->addStatusToHistory('processing', 'AVS Address Response: ' . $xml->avsaddressresponse)->save();
            
             if($xml->result == "00"){
                $payment->getOrder()->addStatusToHistory('processing', 'Authcode: ' . $xml->authcode)->save();    
                $result = "APPROVED";         
                if($type=='capture'){
                   $payment->setTransactionId($xml->batchid);
                   $payment->setIsTransactionClosed(1);
                   /*$additionalInfo['response'] = $xml->message;
                   $payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,array('additionalInfo'=>$additionalInfo));
                      */
                }
                           
             }else if(substr($xml->result,0,1) == '1'){
                 $result = "DECLINED";
             }else {
                  $result = "ERROR";
             }
                
             switch ($result) {
                 case self::STATUS_APPROVED:
                    $payment->setStatus(self::STATUS_APPROVED);
                    break;
                default:
                    $error = Mage::helper('paygate')->__('Payment authorization error. ' . $xml->result . ' : ' . $xml->message);
                    break;
              }
        }else{
              $error = $xml->message;
        }
        
        return $error;
    }
	
   /**
     * Validate payment method information object
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate()
    {
        /*
        * calling parent validate function
        */
       // parent::validate();

        $info = $this->getInfoInstance();
        $errorMsg = false;
        $availableTypes = explode(',',$this->getConfigData('cctypes'));

        $ccNumber = $info->getCcNumber();

        // remove credit card number delimiters such as "-" and space
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $info->setCcNumber($ccNumber);

        $ccType = '';

        if (!$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
            $errorCode = 'ccsave_expiration,ccsave_expiration_yr';
            $errorMsg = $this->_getHelper()->__('Incorrect credit card expiration date');
        }

        if (in_array($info->getCcType(), $availableTypes)){
            if ($this->validateCcNum($ccNumber)
                // Other credit card type number validation
                || ($this->OtherCcType($info->getCcType()) && $this->validateCcNumOther($ccNumber))) {

                $ccType = 'OT';
                $ccTypeRegExpList = array(
                    'VI' => '/^4[0-9]{12}([0-9]{3})?$/', // Visa
                    'MC' => '/^5[1-5][0-9]{14}$/',       // Master Card
                    'AE' => '/^3[47][0-9]{13}$/',        // American Express
                    'DI' => '/^6011[0-9]{12}$/',          // Discovery
                    'SS' => '/^((6759[0-9]{12})|(49[013][1356][0-9]{13})|(633[34][0-9]{12})|(633110[0-9]{10})|(564182[0-9]{10}))([0-9]{2,3})?$/'
                );

                foreach ($ccTypeRegExpList as $ccTypeMatch=>$ccTypeRegExp) {
                    if (preg_match($ccTypeRegExp, $ccNumber)) {
                        $ccType = $ccTypeMatch;
                        break;
                    }
                }

                if (!$this->OtherCcType($info->getCcType()) && $ccType!=$info->getCcType()) {
                    $errorCode = 'ccsave_cc_type,ccsave_cc_number';
                    $errorMsg = $this->_getHelper()->__('Credit card number mismatch with credit card type');
                }
            }
            else {
                $errorCode = 'ccsave_cc_number';
                $errorMsg = $this->_getHelper()->__('Invalid Credit Card Number');
            }

        }
        else {
            $errorCode = 'ccsave_cc_type';
            $errorMsg = $this->_getHelper()->__('Credit card type is not allowed for this payment method');
        }
        
        if($info->getCcType() != 'LA'){

		//validate credit card verification number
        if ($errorMsg === false && $this->hasVerification()) {
            $verifcationRegEx = $this->getVerificationRegEx();
            $regExp = isset($verifcationRegEx[$info->getCcType()]) ? $verifcationRegEx[$info->getCcType()] : '';
            if (!$info->getCcCid() || !$regExp || !preg_match($regExp ,$info->getCcCid())){
                $errorMsg = $this->_getHelper()->__('Please enter a valid credit card verification number.');
            }
        }					
        
        }

        if($errorMsg){
            Mage::throwException($errorMsg);
        }

        return $this;
    }

    public function saveRealexTransaction(SimpleXMLElement $response){
        
        $realex = $this->getRealexObject($response->orderid);
         if(!is_object($realex))
            $realex = Mage::getModel('realex/realex');
            
        try{
	        $realex->setOrderId($response->orderid)
                    ->setTimestamp(Mage::helper('realex')->getDateFromTimestamp($response->attributes()->timestamp))
                    ->setMerchantid($response->merchantid)
                    ->setAccount($response->account)
                    ->setAuthcode($response->authcode)
                    ->setResult($response->result)
                    ->setMessage($response->message)
                    ->setPasref($response->pasref)
                    ->setCvnresult($response->cvnresult)
                    ->setBatchid($response->batchid)
                    ->setCardIssuerBank($response->cardissuer->bank)
                    ->setCardIssuerCountry($response->cardissuer->country)
                    ->setTssResult($response->tss->result)
                    ->setAvspostcoderesponse($response->avspostcoderesponse)
                    ->setAvsaddressresponse($response->avsaddressresponse)
                    ->setTimetaken($response->timetaken)
                    ->setAuthtimetaken($response->authtimetaken)
                    ->save();
    	}catch(Exception $e){
    		Mage::logException($e);
    	}
    }  
    public function saveRealexEnrollmentResponse(SimpleXMLElement $response){ 
        
        $realex = Mage::getModel('realex/realex');
        $quoteId = $this->getCheckout()->getQuote()->getId();
        
        $realexAlreadyExist = $realex->getCollection()->addFieldToFilter("enrollment_cart_quote_id",$quoteId)->addFieldToFilter("is_enrolled","Y");
        if($realexAlreadyExist->getSize()==0){
        
        try{
            
           //echo $response->result;exit;
            $realex->setOrderId($response->orderid)
                    ->setEnrollmentTimestamp(Mage::helper('realex')->getDateFromTimestamp($response->attributes()->timestamp))
                    ->setEnrollmentResponse($response)     
                    ->setMerchantid($response->merchantid)
                    ->setAccount($response->account)
                    ->setAuthcode($response->authcode)
                    ->setEnrollmentResult($response->result)
                    ->setEnrollmentMessage($response->message)
                    ->setEnrollmentPareq($response->pareq)
                    ->setAcsUrl($response->url)
                    ->setIsEnrolled($response->enrolled)
                    ->setEnrollmentTransactionId($response->xid)
                    ->setEnrollmentTimetaken($response->timetaken)
                    ->setEnrollmentAuthtimetaken($response->authtimetaken);
            if($quoteId){
                $realex->setEnrollmentCartQuoteId($quoteId);       
                }
                    $realex->save();
        }catch(Exception $e){
            Mage::logException($e);
        }
        if($realex->getId())
            return $realex->getId();
        else
            return false;
        }
    else{
        return $realexAlreadyExist->getFirstItem()->getId();
    }
   }
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    public function enrollCard()
    {
        $error = false;
        $session = Mage::getSingleton('checkout/session');
        $session->setErrorUrl(false);
        $session->setErrorMessage(false);
        $quoteObj = $this->getCheckout()->getQuote();
        
        //$url = "https://epage.payandshop.com/epage-3dsecure.cgi";
        $enrollmenturl = $this->getConfigData('enrollmenturl');
        
        
        $realex = Mage::getModel('realex/realex');
        $quoteId = $this->getCheckout()->getQuote()->getId();
        
        $realexAlreadyExist = $realex->getCollection()->addFieldToFilter("enrollment_cart_quote_id",$quoteId)->addFieldToFilter("is_enrolled","Y");
        $request = $this->_buildEnrollmentRequest($quoteObj,$quoteObj->getGrandTotal(), 0);  
          
        if($realexAlreadyExist->getSize()==0){     
            return $processResponse = $this->processEnrollmentResponse($this->_postRequest($request['xml'],$enrollmenturl),$quoteObj->getPayment(),"enroll",$request['MD']);
        }else{
            return array("id"=>$realexAlreadyExist->getFirstItem()->getId(),"MD"=>$request['MD']);
        }
        
        
    }
    protected function _buildEnrollmentRequest(Varien_Object $quote, $amount, $autosettle){     
        $timestamp = $merchantid = $account = $orderid = $currency = $cardnumber = $expdate = $cardname = $cardtype = $issueno = $cvc = $customerID = $productID = $billingPostcode = $billingCountry =    $shippingPostcode = $shippingCountry = $sha1hash = '';

        //Get data from Magento Admin Backend for Realex Payment Module    
        $merchantid = $this->getConfigData('login');
        $secret = $this->getConfigData('pwd');
        $account = $this->getConfigData('account');


        if($this->getConfigData('currency') == 'display'){
            $currency = $quote->getQuoteCurrencyCode();
            $amount = $order->getGrandTotal();
        }else{
            $currency = $quote->getBaseCurrencyCode();    
        }
        
        $presind = $this->getConfigData('useccv');

        /* multiplied by 100 because Realex deals in cents as the base unit
         * while magento uses euro
         */
        $amount = $amount * 100;
        
        
        $payment = $quote->getPayment();
        $cardnumber = $payment->getCcNumber();
        $cardname =  $payment->getCcOwner();
        if($payment->getCcCid()){
            $cvc = $payment->getCcCid();
        }else{
            $cvc = '';
        }

        $cardtype = $this->convertCcType($payment->getCcType());
        if($cardtype == 'amex'){
            $account = $this->getConfigData('amexAccount');
        }

        /* Converts expiry date stored by Magento to a two-digit month and two digit
         * year format without a seperator such as a hyphen.
         */
        $expdate = sprintf('%02d%02d', $payment->getCcExpMonth(), substr($payment->getCcExpYear(), 2));

        $issueno = $payment->getCcSsIssue();
                
        // The Timestamp is created here and used in the digital signature
        $timestamp = strftime("%Y%m%d%H%M%S");
        //mt_srand((double)microtime()*1000000);

        
        //$last_order_increment_id = Mage::getModel("sales/order")->getCollection()->getLastItem()->getIncrementId();     
        //$orderid = $last_order_increment_id + 1;  
          
        // We are assigning Current QuoteId As a OrderId for Card Verification
          
        //$orderid = $quote->getId().'-'.rand(100000,999999);  
        $date = date('YmdHis');
        $orderid = $quote->getId() . $date;  
        
        
        /* This section of code creates the md5hash that is needed
        $tmp = "$timestamp.$merchantid.$orderid.$amount.$currency.$cardnumber";
        $md5hash = md5($tmp);
        $tmp = "$md5hash.$secret";
        $md5hash = md5($tmp);
        */

        // This section of code creates the shahash that is needed
        $tmp = "$timestamp.$merchantid.$orderid.$amount.$currency.$cardnumber";
        $sha1hash = sha1($tmp);
        $tmp = "$sha1hash.$secret";
        $sha1hash = sha1($tmp);
        
        $customerID = $quote->getCustomerId();
    
        //If there's only one product being bought, add it into the XML.
        $products = array();
        foreach ($quote->getItemsCollection() as $item) {
            $products[] = $item->getProductId();
        }

        $productID;
        if(!(count($products)>1)){
            $productID = $products[0];
        }
        $billing = $quote->getShippingAddress();
        $billingCountry = $billing->getCountry();
        $billingPostcode = $billing->getPostcode();
        $billingPostcodeNumbers = preg_replace('/[^\d]/', '', $billingPostcode);
        
        $billingStreetName = $billing->getStreet1();
        preg_match('{(\d+)}', $billingStreetName, $m);         
        if(isset($m[1])){
            $billingStreetNumber = $m[1];
        }else{
            $billingStreetNumber = '';
        }
        
        $billingCode = $billingPostcodeNumbers . '|' . $billingStreetNumber;
        
        $shipping = $quote->getShippingAddress();       
        if($shipping){
            $shippingCountry = $shipping->getCountry();
            $shippingPostcode = $shipping->getPostcode();    
        }
        $ccType=$payment->getCcType();
        if($ccType == 'VI'){
            $eci = 6;
        }else if($ccType == 'MC'){
            $eci = 1;
        }
        $ip = Mage::getSingleton('checkout/session')->getQuote()->getRemoteIp();
    
        $xml = "<request type='3ds-verifyenrolled' timestamp='$timestamp'>
                    <merchantid>$merchantid</merchantid>
                    <account>$account</account>
                    <orderid>$orderid</orderid>
                    <amount currency='$currency'>$amount</amount>
                    <card> 
                        <number>$cardnumber</number>
                        <expdate>$expdate</expdate>
                        <type>$cardtype</type> 
                        <chname>$cardname</chname> 
                    </card>
                    <mpi>
                    <eci>$eci</eci>
                    </mpi>
                    <autosettle flag='$autosettle'/> 
                    <sha1hash>$sha1hash</sha1hash>
                    <tssinfo>
                        <custnum>$customerID</custnum>
                        <prodid>$productID</prodid>
                        <varref></varref>
                        <custipaddress>$ip</custipaddress>
                        <address type='billing'> 
                            <code>$billingCode</code>  
                            <country>$billingCountry</country>  
                        </address> 
                        <address type='shipping'> 
                            <code>$shippingPostcode</code>  
                            <country>$shippingCountry</country>  
                        </address>
                    </tssinfo> 
                </request>";
                    
                                    
          $MD = "orderid=$orderid&cardnumber=$cardnumber&cardname=$cardname&cardtype=$cardtype&currency=$currency&amount=$amount&expdate=$expdate";   
         
        //Prints request xml to /var/log/system.log if in Debug mode
        if($this->getConfigData('debug')){
            Mage::log($xml);
        }
        return array("xml"=>$xml,"MD"=>$MD);
    } 
    protected function processEnrollmentResponse($response, Varien_Object $payment,$type,$MD=null){
       
      
        $error = false;
        $xml = new SimpleXMLElement($response);
        $RealexId = $this->saveRealexEnrollmentResponse($xml);
        $payment->setLastTransId($xml->orderid)
                ->setCcTransId($xml->orderid);
                       
        if($xml->result != '501'){
            if($type == 'enroll'){
                $payment->setCcApproval($xml->authcode)
                           ->setCcAvsStatus($xml->pasref); 
            }
        } 
        
        return array("id"=>$RealexId,"MD"=>$MD);
    }
    
    public function getverifySig($response3dsecure){
        $error = false;
        $session = Mage::getSingleton('checkout/session');
        $session->setErrorUrl(false);
        $session->setErrorMessage(false);
        $quoteObj = $this->getCheckout()->getQuote();
        $url = $this->getConfigData('verifysigurl');
        
        $request = $this->_buildVerifySigRequest($response3dsecure);     
        return $processResponse = $this->processverifysigResponse($this->_postRequest($request,$url));
    }
    protected function _buildVerifySigRequest($response3dsecure){
        
        $timestamp = $merchantid = $account = $orderid = $currency = $cardnumber = $expdate = $cardname = $cardtype = $issueno = $cvc = $customerID = $productID = $billingPostcode = $billingCountry =    $shippingPostcode = $shippingCountry = $sha1hash = '';
        
        $pasres = $response3dsecure['PaRes'];
        $md = $response3dsecure['MD'];
        

        $valuearray = explode("&",$md);


        foreach ($valuearray as $postvalue) {
           list($field,$content) = explode("=",$postvalue);
           $formatarray[$field] = $content;
        }
        $parentElements = array();
        $TSSChecks = array();
        $currentElement = 0;
        $currentTSSCheck = "";

        $currency = $formatarray['currency'];
        $amount = $formatarray['amount'];
        $cardnumber = $formatarray['cardnumber'];
        $cardname = $formatarray['cardname'];
        $cardtype = $formatarray['cardtype'];
        $expdate = $formatarray['expdate'];
        $orderid = $formatarray['orderid'];
        
        //Get data from Magento Admin Backend for Realex Payment Module    
        $merchantid = $this->getConfigData('login');
        $secret = $this->getConfigData('pwd');
        $account = $this->getConfigData('account');
       
        // The Timestamp is created here and used in the digital signature
        $timestamp = strftime("%Y%m%d%H%M%S");
        mt_srand((double)microtime()*1000000);
        
        //$orderid = $orderid .'-'. rand(11111,99999);

        // This section of code creates the shahash that is needed
        $tmp = "$timestamp.$merchantid.$orderid.$amount.$currency.$cardnumber"; 
        $sha1hash = sha1($tmp);
        $tmp = "$sha1hash.$secret";
        $sha1hash = sha1($tmp);
        

        // generate the request xml.
        $xml = "<request type='3ds-verifysig' timestamp='$timestamp'>
            <merchantid>$merchantid</merchantid>
            <account>$account</account>
            <orderid>$orderid</orderid>
            <amount currency='$currency'>$amount</amount>
            <card> 
                <number>$cardnumber</number>
                <expdate>$expdate</expdate>
                <type>$cardtype</type> 
                <chname>$cardname</chname> 
            </card>
            <autosettle flag='1'/>
            <sha1hash>$sha1hash</sha1hash>     
            <tssinfo>
                <address type=\"billing\">
                    <country>ie</country>                       
                </address>
            </tssinfo>
                <pares>$pasres</pares>
        </request>";
                    
                    
          //$MD = "orderid=$orderid&cardnumber=$cardnumber&cardname=$cardname&cardtype=$cardtype&currency=$currency&amount=$amount&expdate=$expdate";   
         
        //Prints request xml to /var/log/system.log if in Debug mode
        if($this->getConfigData('debug')){
            Mage::log($xml);
        }
        return $xml;
    }
    
    public function getRealexObject($quoteId=null){
        
        if($quoteId==null)
            $quoteId = $this->getCheckout()->getQuote()->getId(); 
               
        $realex = Mage::getModel('realex/realex')->getCollection()->addFieldToFilter("enrollment_cart_quote_id",$quoteId)
                    ->addFieldToFilter("is_enrolled","Y")
                    ->addFieldToFilter("order_id",$quoteId)
                    ->getFirstItem();
        if(is_object($realex) && ($realex->getId()))return $realex;else return;
           
    }
     
    protected function processverifysigResponse($response){
       
          
        $error = false;
        $xml = new SimpleXMLElement($response);
        
        $quoteId = $this->getCheckout()->getQuote()->getId(); 
        $realex = Mage::getModel('realex/realex')->getCollection()->addFieldToFilter("enrollment_cart_quote_id",$quoteId)
                                ->addFieldToFilter("is_enrolled","Y")
                                //->addFieldToFilter("order_id",$xml->orderid)
                                ->getFirstItem();            
                                
        try{
            if($xml->threedsecure->cavv)
                $realex->setCavv($xml->threedsecure->cavv);
            
            if($xml->threedsecure->cavv)
                $realex->setXid($xml->threedsecure->xid);

            if($xml->threedsecure->eci)
                $realex->setEci($xml->threedsecure->eci);            
            
            $realex->save();
            
        }catch(Exception $e){
            Mage::logException($e);
        }
        
        return $xml;   
        
    }
    
    /**
     * Register DIRECT transation.
     *
     * @param array $params
     * @param bool $onlyToken
     * @param float $macOrder MAC single order
     */
    public function registerTransaction($params = null, $onlyToken = false, $macOrder = null){
        $quoteObj = $this->_getQuote();
        $quoteObj2 = $this->getQuoteDb($quoteObj);

        if (is_null($macOrder)) {
            $amount = $this->formatAmount($quoteObj2->getGrandTotal(), $quoteObj2->getCurrencyCode());
        }
        else {

            $amount = $this->formatAmount($macOrder->getGrandTotal(), $macOrder->getCurrencyCode());

            $baseAmount = $this->formatAmount($macOrder->getBaseGrandTotal(), $macOrder->getQuoteCurrencyCode());

            $quoteObj->setMacAmount($amount);
            $quoteObj->setBaseMacAmount($baseAmount);
        }

        if (!is_null($params)) {
            $payment = $this->_getBuildPaymentObject($quoteObj2, $params);
        }
        else {
            $payment = $this->_getBuildPaymentObject($quoteObj2);
        }

        
        /*
        if ($onlyToken) {
            return $this->registerToken($payment);
        } 
        */
        
        //$_rs  = $this->directRegisterTransaction($payment, $amount);

        //$_req = $payment->getRealexResult()->getRequest();
        $_res = $payment->getRealexResult();

       /*
        #Last order vendortxcode
        $this->getSageSuiteSession()->setLastVendorTxCode($_req->getData('VendorTxCode'));
        if ($this->isMsOnOverview()) {
            $tx = array();
            $regTxCodes = Mage::registry('sagepaysuite_ms_txcodes');
            if ($regTxCodes) {
                $tx += $regTxCodes;
                Mage::unregister('sagepaysuite_ms_txcodes');
            }
            $tx [] = $_req->getData('VendorTxCode');
            Mage::register('sagepaysuite_ms_txcodes', $tx);
        }

        Mage::getModel('sagepaysuite2/sagepaysuite_transaction')
                ->loadByVendorTxCode($_req->getData('VendorTxCode'))
                ->setVendorTxCode($_req->getData('VendorTxCode'))
                ->setToken($_req->getData('Token'))
                ->setTrnCurrency($_req->getData('Currency'))
                ->setTrnAmount($_req->getData('Amount'))
                ->setTxType($_req->getData('Txtype'))
                ->setMode($this->getConfigData('mode'))
                ->setVendorname($_req->getData('Vendor'))
                ->setVpsProtocol($_res->getData('VPSProtocol'))
                ->setSecurityKey($_res->getData('SecurityKey'))
                ->setVpsTxId($_res->getData('VPSTxId'))
                ->setTxAuthNo($_res->getData('TxAuthNo'))
                ->setAvscv2($_res->getData('AVSCV2'))
                ->setPostcodeResult($_res->getData('PostCodeResult'))
                ->setAddressResult($_res->getData('AddressResult'))
                ->setCv2result($_res->getData('CV2Result'))
                ->setThreedSecureStatus($_res->getData('3DSecureStatus'))
                ->setCavv($_res->getData('CAVV'))
                ->setRedFraudResponse($_res->getData('FraudResponse'))
                ->setBankAuthCode($_res->getData('BankAuthCode'))
                ->setDeclineCode($_res->getData('DeclineCode'))
                ->save();
        */
        return $_res;
    }
    public function directRegisterTransaction(Varien_Object $payment, $amount) {
        #Process invoice
        
        /*
        if (!$payment->getRealCapture()) {
            return $this->captureInvoice($payment, $amount);
        }
        */

        /**
         * Token Transaction
         */
        if (true === $this->_tokenPresent()){

            $_info = new Varien_Object(array('payment' => $payment));
            $result = $this->getTokenModel()->tokenTransaction($_info);

            if ($result['Status'] != self::RESPONSE_CODE_APPROVED
                    && $result['Status'] != self::RESPONSE_CODE_3DAUTH
                    && $result['Status'] != self::RESPONSE_CODE_REGISTERED) {
                Mage::throwException(Mage::helper('sagepaysuite')->__($result['StatusDetail']));
            }

            if (strtoupper($this->getConfigData('payment_action')) == self::REQUEST_TYPE_PAYMENT) {
                $this->getSageSuiteSession()->setInvoicePayment(true);
            }

            $this->setSagePayResult($result);

            if ($result['Status'] == self::RESPONSE_CODE_3DAUTH) {
                $payment->getOrder()->setIsThreedWaiting(true);

                $this->getSageSuiteSession()->setSecure3dMethod('directCallBack3D');

                Mage::getModel('sagepaysuite2/sagepaysuite_transaction')
                        ->loadByVendorTxCode($payment->getVendorTxCode())
                        ->setVendorTxCode($payment->getVendorTxCode())
                        ->setMd($result['MD'])
                        ->setPareq($result['PAReq'])
                        ->setAcsurl($result['ACSURL'])
                        ->save();

                $this->getSageSuiteSession()
                        ->setAcsurl($result['ACSURL'])
                        ->setEmede($result['MD'])
                        ->setPareq($result['PAReq']);
                $this->setVndor3DTxCode($payment->getVendorTxCode());
            }

            return $this;
        }
        /**
         * Token Transaction
         */
        if ($this->_getIsAdmin() && (int) $this->_getAdminQuote()->getCustomerId() === 0) {
            //$cs = Mage::getModel('customer/customer')->setWebsiteId($this->_getAdminQuote()->getStoreId())->loadByEmail($this->_getAdminQuote()->getCustomerEmail());
            $cs = Mage::helper('sagepaysuite')->existsCustomerForEmail($this->_getAdminQuote()->getCustomerEmail(), $this->_getAdminQuote()->getStore()->getWebsite()->getId());
            if ($cs) {
                Mage::throwException($this->_SageHelper()->__('Customer already exists.'));
            }
        }
        if ($this->_getIsAdmin()) {
            $payment->setRequestVendor($this->getConfigData('vendor', $this->_getAdminQuote()->getStoreId()));
        }
        
        if ($this->getSageSuiteSession()->getSecure3d()) {
            $this->directCallBack3D(
                    $payment, $this->getSageSuiteSession()->getPares(), $this->getSageSuiteSession()->getEmede());
            $this->getSageSuiteSession()->setSecure3d(null);
            return $this;
        }
        $this->getSageSuiteSession()->setMd(null)
                ->setAcsurl(null)
                ->setPareq(null);

        $error = false;

        $payment->setAnetTransType(strtoupper($this->getConfigData('payment_action')));

        $payment->setAmount($amount);

        $request = $this->_buildRequest($payment);

        Mage::dispatchEvent('sagepaysuite_direct_request_post_before', array('request' => $request, 'payment' => $this));

        $result = $this->_postRequest($request);
         
        $dbTrn = Mage::getModel('sagepaysuite2/sagepaysuite_transaction')
                ->loadByVendorTxCode($request->getData('VendorTxCode'))
                ->setVendorTxCode($request->getData('VendorTxCode'))
                ->setCustomerContactInfo($request->getData('ContactNumber'))
                ->setCustomerCcHolderName($request->getData('CustomerName'))
                ->setVendorname($request->getData('Vendor'))
                ->setTxType($request->getData('InternalTxtype'))
                ->setTrnCurrency($request->getCurrency())
                ->setIntegration('direct')
                ->setCardType($request->getData('CardType'))
                ->setCardExpiryDate($request->getData('ExpiryDate'))
                ->setLastFourDigits(substr($request->getData('CardNumber'), -4))
                ->setToken($request->getData('Token'))
                ->setTrnCurrency($request->getData('Currency'))
                ->setMode($this->getConfigData('mode'))
                ->setTrndate($this->getDate())
                ->setStatus($result->getResponseStatus())
                ->setStatusDetail($result->getResponseStatusDetail())
                ->save();

        switch ($result->getResponseStatus()) {
            case 'FAIL':
                $error = $result->getResponseStatusDetail();
                $payment
                        ->setStatus('FAIL')
                        ->setCcTransId($result->getVPSTxId())
                        ->setLastTransId($result->getVPSTxId())
                        ->setCcApproval('FAIL')
                        ->setAddressResult($result->getAddressResult())
                        ->setPostcodeResult($result->getPostCodeResult())
                        ->setCv2Result($result->getCV2Result())
                        ->setCcCidStatus($result->getTxAuthNo())
                        ->setSecurityKey($result->getSecurityKey())
                        ->setAdditionalData($result->getResponseStatusDetail());
                break;
            case 'FAIL_NOMAIL':
                $error = $result->getResponseStatusDetail();
                break;
            case self::RESPONSE_CODE_APPROVED:
            case self::RESPONSE_CODE_REGISTERED:

                $payment->setSagePayResult($result);

                $payment
                        ->setStatus(self::RESPONSE_CODE_APPROVED)
                        ->setCcTransId($result->getVPSTxId())
                        ->setLastTransId($result->getVPSTxId())
                        ->setCcApproval(self::RESPONSE_CODE_APPROVED)
                        ->setAddressResult($result->getAddressResult())
                        ->setPostcodeResult($result->getPostCodeResult())
                        ->setCv2Result($result->getCV2Result())
                        ->setCcCidStatus($result->getTxAuthNo())
                        ->setSecurityKey($result->getSecurityKey());

                if (strtoupper($this->getConfigData('payment_action')) == self::REQUEST_TYPE_PAYMENT) {
                    $this->getSageSuiteSession()->setInvoicePayment(true);
                }

                break;
            case self::RESPONSE_CODE_3DAUTH:

                $payment->setSagePayResult($result);

                $payment->getOrder()->setIsThreedWaiting(true);

                $this->getSageSuiteSession()->setSecure3dMethod('directCallBack3D');

                $this->getSageSuiteSession()
                        ->setAcsurl($result->getData('a_cs_ur_l'))
                        ->setEmede($result->getData('m_d'))
                        ->setPareq($result->getData('p_areq'));

                $dbTrn->setMd($result->getData('m_d'))
                        ->setPareq($result->getData('p_areq'))
                        ->setAcsurl($result->getData('a_cs_ur_l'))
                        ->save();

                $this->setVndor3DTxCode($payment->getVendorTxCode());

                break;
            default:
                if ($result->getResponseStatusDetail()) {
                    $error = '';
                    if ($result->getResponseStatus() == self::RESPONSE_CODE_NOTAUTHED) {

                        $this->getSageSuiteSession()
                                ->setAcsurl(null)
                                ->setEmede(null)
                                ->setPareq(null);

                        $error = $this->_SageHelper()->__('Your credit card can not be authenticated: ');
                    } else if ($result->getResponseStatus() == self::RESPONSE_CODE_REJECTED) {
                        $this->getSageSuiteSession()
                                ->setAcsurl(null)
                                ->setEmede(null)
                                ->setPareq(null);
                        $error = $this->_SageHelper()->__('Your credit card was rejected: ');
                    }
                    $error .= $result->getResponseStatusDetail();
                } else {
                    $error = $this->_SageHelper()->__('Error in capturing the payment');
                }
                break;
        }

        if ($error !== false) {

            if (Mage::helper('adminhtml')->getCurrentUserId() !== FALSE) {
                Mage::getSingleton('adminhtml/session')->addError($error);
            }

            Mage::throwException($error);
        }

        return $this;
    }
    
}
      
?>