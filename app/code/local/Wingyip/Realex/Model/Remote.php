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
class Wingyip_Realex_Model_Remote extends Mage_Payment_Model_Method_Ccsave{
    protected $_code = 'realex';
    protected $_formBlockType = 'realex/remote_form';

    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc = false;
    
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
        $block = $this->getLayout()->createBlock('realex/remote_form', $name)
            ->setMethod('realex_remote')
            ->setPayment($this->getPayment())
            ->setTemplate('realex/remote/form.phtml');

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
	
		$url = "https://epage.payandshop.com/epage-remote.cgi";
		
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

    /**
     * @param Varien_Object $payment
     * @param $amount
     * @return Wingyip_Realex_Model_Remote
     */
    public function capture(Varien_Object $payment, $amount){
		$error = false;
		
		$session = Mage::getSingleton('checkout/session');
		$session->setErrorUrl(false);
		$session->setErrorMessage(false);
		
		$url = "https://epage.payandshop.com/epage-remote.cgi";
	
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
		$url = "https://epage.payandshop.com/epage-remote.cgi";
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
		$url = "https://epage.payandshop.com/epage-remote.cgi";
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
		$timestamp = $merchantid = $account = $orderid = $currency = $cardnumber = $expdate = $cardname = $cardtype = $issueno = $cvc = $customerID = $productID = $billingPostcode = $billingCountry =	$shippingPostcode = $shippingCountry = $sha1hash = '';

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

        $orderid = $order->getIncrementId();

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

		$productID;
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
    
        //Use the variables set above to fill in the request xml that is sent to Realex Payments.
        $xml = "<request timestamp='$timestamp' type='auth' >
                        <merchantid>$merchantid</merchantid>
						<account>$account</account>
                        <orderid>$orderid</orderid>
                        <amount currency='$currency'>$amount</amount>
                        <card> 
                            <number>$cardnumber</number>
                            <expdate>$expdate</expdate>
                            <chname>$cardname</chname> 
                            <type>$cardtype</type> 
                            <issueno>$issueno</issueno>
	                        <cvn>
    	                        <number>$cvc</number>
        	                    <presind>$presind</presind>
            	            </cvn>
                        </card>
                        <autosettle flag='$autosettle'/>
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
                        <sha1hash>$sha1hash</sha1hash>
                        <comments>
                            <comment id='1'></comment>
                            <comment id='2'>Magento</comment>
                        </comments>
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
}
      
?>
