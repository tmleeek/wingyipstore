<?php
/**
 * SF9_Realex extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   SF9
 * @package    SF9_Realex
 * @copyright  Copyright (c) 2011 StudioForty9
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   SF9
 * @package    SF9_Realex
 * @author     Alan Morkan <alan@sf9.ie>
 */
class SF9_Realex_Model_Remote extends Mage_Payment_Model_Method_Ccsave{
    protected $_code = 'realex';
    protected $_formBlockType = 'realex/remote_form';

    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
	protected $_canRefundInvoicePartial = true;
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
     * @return SF9_Realex_Remote_Form
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
     * @return bool|SF9_Realex_Model_Remote
     */
    public function authorize(Varien_Object $payment, $amount){
        if(!$payment->getCcNumber() && !$payment->getCcCid()){
            $data=Mage::app()->getRequest()->getParam('payment');
            $payment->setCcNumber($data['cc_number']);
            $payment->setCcCid($data['cc_cid']);
        }
		$session = Mage::getSingleton('checkout/session');
		$session->setErrorUrl(false);
		$session->setErrorMessage(false);
        $session->set3DSecureRedirectUrl(false);

        try{
            if($amount > 0){
                $verifyEnrolled=$this->_verifyEnrolled($payment, $amount);
                if(!$payment->getOrder()->getRemoteIp()){
                    $payment->getOrder()->setRemoteIp($_SERVER["REMOTE_ADDR"]);
                }
                $remoteip=$payment->getOrder()->getRemoteIp();
                $seucre3d=$this->getConfigData('secure3d');
				if(!$remoteip || !$seucre3d ||!$verifyEnrolled){
                    $request = $this->_buildRequest($payment, $amount, 0);
                    $error = $this->processResponse($this->_postRequest($request, $this->_getUrl()), $payment, 'authorize');

                }else{
					$payment->setIsTransactionPending(true);
                    return $this;
                }
            }else{
                $error = Mage::helper('paygate')->__('Invalid amount for authorization.');
            }

            if($error == false && $this->getConfigData('capture')){
                $payment->capture(null);
            }

            if ($error != false) {
                if (Mage::app()->getWebsite()->getId() == 0) {
                    Mage::throwException($error);
                }
                $session->setErrorUrl(true);
                $session->setErrorMessage($error);
                return false;
            }
        } catch (Exception $e) {
            if (Mage::app()->getWebsite()->getId() == 0) {
                Mage::throwException($e->getMessage());
            }
            $session->setErrorUrl(true);
            $session->setErrorMessage($e->getMessage());
        }
        
        return $this;
    }

    /**
     * @param Varien_Object $payment
     * @param $amount
     * @return SF9_Realex_Model_Remote
     */
    public function capture(Varien_Object $payment, $amount){
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
			Mage::throwException($error);
			//$session->setErrorUrl(true);
			//$session->setErrorMessage($error);
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
     * @return SF9_Realex_Model_Remote
     */
    public function void(Varien_Object $payment){
		$request = $this->_buildAdminRequest($payment, 'void');
		$response = $this->_postRequest($request, $this->_getUrl());
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
     * @return SF9_Realex_Model_Remote
     */
    public function refund(Varien_Object $payment, $amount){
		$request = $this->_buildRebateRequest($payment, $amount);
		$response = $this->_postRequest($request, $this->_getUrl());
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

            $shippingPostcodeNumbers = preg_replace('/[^\d]/', '', $shippingPostcode);

            $shippingStreetName = $shipping->getStreet1();
            preg_match('{(\d+)}', $shippingStreetName, $m);
            if(isset($m[1])){
                $shippingStreetNumber = $m[1];
            }else{
                $shippingStreetNumber = '';
            }

			$shippingCode = $shippingPostcodeNumbers . '|' . $shippingStreetNumber;
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
								<code>$shippingCode</code>  
								<country>$shippingCountry</country>  
						    </address>
						</tssinfo>
                        <sha1hash>$sha1hash</sha1hash>
                        <comments>
                            <comment id='1'></comment>
                            <comment id='2'>Magento</comment>
                        </comments>";

        $info = unserialize($payment->getAdditionalData());
        if(isset($info['eci'])){
            $eci = $info['eci'];
            $xml .= "<mpi><eci>$eci</eci></mpi>";
        }

        $xml .= "</request>";
		    
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
				
		$orderid = $payment->getOrder()->getIncrementId();
    	
    	// This section of code creates the shahash that is needed
        $tmp = "$timestamp.$merchantid.$orderid...";
        $sha1hash = sha1($tmp);
        $tmp = "$sha1hash.$secret";
        $sha1hash = sha1($tmp);
        
        $pasref = $payment->getCcTransId();
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
				
		$orderid = $payment->getOrder()->getIncrementId();
        
        $pasref = $payment->getCcTransId();
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
				
		$orderid = $payment->getOrder()->getIncrementId();
    	
    	// This section of code creates the shahash that is needed
        $tmp = "$timestamp.$merchantid.$orderid...";
        $sha1hash = sha1($tmp);
        $tmp = "$sha1hash.$secret";
        $sha1hash = sha1($tmp);
        
        $pasref = $payment->getCcTransId();
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
        switch($type){
            case 'VI':
            case 'EL':
                $cardtype = "visa";
                break;
	        case  'MC':
	            $cardtype = "mc";
                break;
            case  'AE':
                $cardtype = "amex";
                break;
            case  'SS':
                $cardtype = "switch";
                break;
            case  'LA':
                $cardtype = "laser";
                break;
            case  'DI':
			    $cardtype = "diners";
                break;
		    default:
		   	    Mage::throwException("Incorrect CC Type");
		}
		return $cardtype;
    }

    /**
     * @return string
     */
   	public function getOrderPlaceRedirectUrl(){
        $session = Mage::getSingleton('checkout/session');
        if($this->getConfigData('secure3d')){
            if($session->get3DSecureRedirectUrl()){
                return Mage::getBaseUrl() . 'realex/ACS/';
            }
        }
        if($session->getErrorUrl()){
            return Mage::getUrl('realex/remote/failure');
        }
        return false;
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
		    
	    $payment->setLastTransId(strval($xml->orderid));
	            	    
	    if($xml->result != '501'){
			if ($type == 'authorize') {
				$payment->setCcApproval(strval($xml->authcode))
    	       		->setCcTransId(strval($xml->pasref));
			}

			$payment->getOrder()->addStatusToHistory('processing', 'CVN Result: ' . strval($xml->cvnresult))->save();
			$payment->getOrder()->addStatusToHistory('processing', 'AVS Postcode Response: ' . $this->_getAVSResponse(strval($xml->avspostcoderesponse)))->save();
			$payment->getOrder()->addStatusToHistory('processing', 'AVS Address Response: ' . $this->_getAVSResponse(strval($xml->avsaddressresponse)))->save();

	         if($xml->result == "00"){
                $payment->getOrder()->addStatusToHistory('processing', 'Authcode: ' . strval($xml->authcode))->save();
	          	$result = "APPROVED";                
	         }else if(substr($xml->result,0,1) == '1'){
	         	$result = "DECLINED";
	         }else {
	          	$result = "ERROR";
	         }
                Mage::getSingleton('checkout/session')->unsResultPayment();
                Mage::getSingleton('checkout/session')->setResultPayment($result);
	         switch ($result) {
	         	case self::STATUS_APPROVED:
	            	$payment->setStatus(self::STATUS_APPROVED);
	                break;
	            default:
	                $error = Mage::helper('paygate')->__('Payment authorization error. ' . strval($xml->result) . ' : ' . strval($xml->message));
                    break;
	          }
		}else{
      	    $error = strval($xml->message);
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
                    'EL' => '/^4[0-9]{12}([0-9]{3})?$/', // Visa Electron
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

//                if (!$this->OtherCcType($info->getCcType()) && $ccType!=$info->getCcType()) {
//                    $errorCode = 'ccsave_cc_type,ccsave_cc_number';
//                    $errorMsg = $this->_getHelper()->__('Credit card number mismatch with credit card type');
//                }
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
//            if (!$info->getCcCid() || !$regExp || !preg_match($regExp ,$info->getCcCid())){
//                $errorMsg = $this->_getHelper()->__('Please enter a valid credit card verification number.');
//            }
        }					
        
        }

        if($errorMsg){
            Mage::throwException($errorMsg);
            //throw Mage::exception('Mage_Payment', $errorMsg, $errorCode);
        }

        return $this;
    }

    public function saveRealexTransaction(SimpleXMLElement $response){
        $realex = Mage::getModel('realex/realex');
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
    }

    protected function _getUrl(){
	    	return "https://epage.payandshop.com/epage-remote.cgi";
	    }

		protected function _secure3dApplicableCcType($payment){
			$validCcTypes = array('VI', 'MC', 'SS');
			if(in_array($payment->getCcType(), $validCcTypes)){
				return true;
			}
			return false;
		}

		protected function _verifyEnrolled($payment, $amount){
			if(!$this->_secure3dApplicableCcType($payment)){
				return false;
			}

			$url = 'https://epage.payandshop.com/epage-3dsecure.cgi';
			$request = $this->_buildVerifyEnrolledRequest($payment, $amount);
			$xml = simplexml_load_string($this->_postRequest($request, $url));
			$order = $payment->getOrder();
			$order->addStatusToHistory('pending', '3D Secure Verify Enrolled Result: ' . $xml->result . ': ' . $xml->message);

			if($xml->result == '00'){
				$orderid = $order->getIncrementId();
				if($this->getConfigData('currency') == 'display'){
			        $currency = $order->getOrderCurrencyCode();
		            $amount = $order->getTotalDue();
				}else{
					$currency = $order->getBaseCurrencyCode();
				}
				$cardnumber = $payment->getCcNumber();
				$expdate = sprintf('%02d%02d', $payment->getCcExpMonth(), substr($payment->getCcExpYear(), 2));
				$cvc = $payment->getCcCid();
				$cardtype = $this->convertCcType($payment->getCcType());
				$cardname = $payment->getCcOwner();

				$data = "ORDERID=$orderid&CURRENCY=$currency&AMOUNT=$amount&CARDNUMBER=$cardnumber&CARDNAME=$cardname&CVC=$cvc&EXPDATE=$expdate&CARDTYPE=$cardtype";

				$md = Mage::helper('core')->encrypt($data);

				$payment->setAdditionalData(serialize(array('pareq' => (string) $xml->pareq, 'url' => (string) $xml->url, 'xid' => (string) $xml->xid, 'md' => $md)));
				Mage::getSingleton('checkout/session')->set3DSecureRedirectUrl(true);
				Mage::getSingleton('customer/session')->setOrderId($payment->getOrder()->getIncrementId());
				return true;
			}else if($xml->result == '110'){
				switch($xml->enrolled){
					case 'N':
						$payment->setAdditionalData(serialize(array('eci' => $this->_setECI($this->convertCcType($payment->getCcType())))));
						$order->addStatusToHistory('pending', 'Shift in Liability')->save();
						break;
					case 'U':
						$order->addStatusToHistory('pending', 'No Shift in Liability')->save();
                        $quote = Mage::getModel('sales/quote')->load($order->getPayment()->getOrder()->getQuoteId());
                        Mage::helper('checkout')->sendPaymentFailedEmail($quote, $xml->message);
						Mage::throwException('3D Secure Error: ' . $xml->message);
						break;
				}
			}else{
				$order->addStatusToHistory('pending', 'No Shift in Liability')->save();
                $quote = Mage::getModel('sales/quote')->load($order->getPayment()->getOrder()->getQuoteId());
                Mage::helper('checkout')->sendPaymentFailedEmail($quote, $xml->message);
				Mage::throwException('3D Secure Error: ' . $xml->message);
			}
			return false;
		}

		protected function _buildVerifyEnrolledRequest($payment, $amount){
			$order = $payment->getOrder();
			$timestamp = strftime("%Y%m%d%H%M%S");
			$merchantid = $this->getConfigData('login');
			$account = $this->getConfigData('account');
			$secret = $this->getConfigData('pwd');
			$orderid = $order->getIncrementId();
			if($this->getConfigData('currency') == 'display'){
		        $currency = $order->getOrderCurrencyCode();
	            $amount = $order->getTotalDue();
			}else{
				$currency = $order->getBaseCurrencyCode();
			}
			$amount *= 100;
			$ccnumber = $payment->getCcNumber();
			$expdate = sprintf('%02d%02d', $payment->getCcExpMonth(), substr($payment->getCcExpYear(), 2));
			$cctype = $this->convertCcType($payment->getCcType());
			$ccowner = $payment->getCcOwner();

			$tmp = "$timestamp.$merchantid.$orderid.$amount.$currency.$ccnumber";
	        $sha1hash = sha1($tmp);
	        $tmp = "$sha1hash.$secret";
	        $sha1hash = sha1($tmp);

			$xml = "<request timestamp='$timestamp' type='3ds-verifyenrolled'>
						<merchantid>$merchantid</merchantid>
						<account>$account</account>
						<orderid>$orderid</orderid>
						<amount currency='$currency'>$amount</amount>
						<card>
							<number>$ccnumber</number>
							<expdate>$expdate</expdate>
							<type>$cctype</type>
							<chname>$ccowner</chname>
						</card>
						<sha1hash>$sha1hash</sha1hash>
					</request>";

			if($this->getConfigData('debug')){
				Mage::log($xml);
			}

			return $xml;
		}

		//public access method for postRequest for callback from 3D Secure
		public function postVerifySigRequest($request, $url){
	    	$response = $this->_postRequest($request, $url);
	    	return $response;
	    }

		public function process3DSVerifySigReponse($response, $md){
	    	$session = Mage::getSingleton('checkout/session');
			$xml = simplexml_load_string($response);
			$md = Mage::helper('core')->decrypt($md);

			$md_array = $this->_explodeMD($md);
			$order = Mage::getModel('sales/order')->loadByIncrementId($md_array['ORDERID']);

			$order->addStatusToHistory('processing', '3D Secure Verify Signature Response: ' . $xml->result . ': ' . $xml->message)->save();

		    if($xml->result == '00'){
		    	if($xml->threedsecure->status == 'N'){
			    	$order->addStatusToHistory('processing', '3D Secure Response: Authentication Unsuccessful')->save();
    				$order->getPayment()->setAdditionalData(null)->save();
                    $quote = Mage::getModel('sales/quote')->load($order->getPayment()->getOrder()->getQuoteId());
                    Mage::helper('checkout')->sendPaymentFailedEmail($quote, 'Authentication Unsuccessful');
		    		Mage::throwException("Authentication Unsuccessful");
		    	}else if($xml->threedsecure->status == 'U'){
		    		if($this->getConfigData('secure3d')){
    					$order->getPayment()->setAdditionalData(null)->save();
                        $message = "This website requires 3D Secure authentication of credit card details. Card holder authentication is temporarily unavailable. Please try again later.";
                        $quote = Mage::getModel('sales/quote')->load($order->getPayment()->getOrder()->getQuoteId());
                        Mage::helper('checkout')->sendPaymentFailedEmail($quote, $message);
		    			Mage::throwException($message);
		    		}else{
		    			$request = $this->_buildRequestFromMD($md, '7', $xml->threedsecure->cavv, $xml->threedsecure->xid);

		  				$response = simplexml_load_string($this->_postRequest($request, $this->_getUrl()));


						if($response->result == '00'){
							$order->addStatusToHistory('processing', 'AuthCode: ' . $response->authcode)
							  ->addStatusToHistory('processing', 'AVS Postcode Response: ' . $this->_getAVSResponse($response->avspostcoderesponse))
	   		    			  ->addStatusToHistory('processing', 'AVS Address Response: ' . $this->_getAVSResponse($response->avsaddressresponse))
							  ->addStatusToHistory('processing', 'CVN Response: ' . $this->_getAVSResponse($response->cvnresult))
							  ->save();
							$session = Mage::getSingleton('checkout/session');
							$session->setSuccessUrl(Mage::getUrl('checkout/onepage/success'));
						}else{
							$order->getPayment()->setAdditionalData(null)->save();
                            $quote = Mage::getModel('sales/quote')->load($order->getPayment()->getOrder()->getQuoteId());
                            Mage::helper('checkout')->sendPaymentFailedEmail($quote, $response->message);
							Mage::throwException($response->message);
						}
		    		}
		    	}else if($xml->threedsecure->status == 'Y' || $xml->threedsecure->status == 'A'){
					$request = $this->_buildRequestFromMD($md, $xml->threedsecure->eci, $xml->threedsecure->cavv, $xml->threedsecure->xid);

	  				$response = simplexml_load_string($this->_postRequest($request, $this->_getUrl()));

					if($response->result == '00'){
						$order->getPayment()->setLastTransId($response->orderid)
											->setCcTransId($response->pasref)
											->setCcApproval($response->authcode);

                        $order->sendNewOrderEmail();

						$order->addStatusToHistory('processing', 'AuthCode: ' . $response->authcode)
							  ->addStatusToHistory('processing', 'AVS Postcode Response: ' . $this->_getAVSResponse($response->avspostcoderesponse))
	   		    			  ->addStatusToHistory('processing', 'AVS Address Response: ' . $this->_getAVSResponse($response->avsaddressresponse))
							  ->addStatusToHistory('processing', 'CVN Response: ' . $this->_getAVSResponse($response->cvnresult))
							  ->save();
						if($this->getConfigData('capture')){
							try{
								$order->getPayment()->capture(null);
								$order->save();
							}catch(Exception $e){
								Mage::throwException($e);
							}
						}
						$session = Mage::getSingleton('checkout/session');
						$session->setSuccessUrl(Mage::getUrl('checkout/onepage/success'));

					}else{
					/*
					This merchant is 3d secure enabled but 3d secure has been disabled fort this card type
					This error will be received if MPI data (ECI = 5, 6, 1 or 2) is sent in the authorisation
					message but the card type has been disabled for 3d secure on the merchants Realex account.
					*/
						$order->getPayment()->setAdditionalData(null)->save();
                        $quote = Mage::getModel('sales/quote')->load($order->getPayment()->getOrder()->getQuoteId());
                        Mage::helper('checkout')->sendPaymentFailedEmail($quote, $response->message);
						Mage::throwException($response->message);
					}
				}

	    	}else if($xml->result == '110'){
	    		$order->addStatusToHistory('processing', '3D Secure Response', $xml->result . ': ' . $xml->message.' - No Shift in liability - Treat as Fraudulent Transaction')->save();
   				$order->getPayment()->setAdditionalData(null)->save();
                $quote = Mage::getModel('sales/quote')->load($order->getPayment()->getOrder()->getQuoteId());
                $message = '3D Secure Response: ' . $xml->result.': '.$xml->message.' - No Shift in liability - This is being treated as a Fraudulent Transaction';
                Mage::helper('checkout')->sendPaymentFailedEmail($quote, $message);
				return Mage::throwException($message);
			}else if(strstr($xml->result, '5')){
				$order->addStatusToHistory('processing', 'Authentication failed. Invalid response from ACS.')->save();
				$order->getPayment()->setAdditionalData(null)->save();

                $message = "Authentication failed. Invalid response from ACS. Please contact us.";
                $quote = Mage::getModel('sales/quote')->load($order->getPayment()->getOrder()->getQuoteId());
                Mage::helper('checkout')->sendPaymentFailedEmail($quote, $message);
				Mage::throwException($message);
				/*This scenario is encountered by the Realex Payments MPI while processing the Payer
				Authentication messages (ie while verifying the digital signature in the PARes). See
				Document Realex Payments RealMPI 5xx errors
				ECI should be 7
	 		   */
			}else{
				Mage::log($xml->result);
			}
	    }

	    protected function _explodeMD($data){
		    $values = explode('&', $data);
		    $return = array();
	        foreach($values as $value){
	        	$temp = explode('=', $value);
	        	$return[$temp[0]] = $temp[1];
	        }
	        return $return;
	    }

	     protected function _buildRequestFromMD($md, $eci, $cavv, $xid){
	    	$timestamp = strftime("%Y%m%d%H%M%S");
			mt_srand((double)microtime()*1000000);
			$merchantid = $this->getConfigData('login');
			$account = $this->getConfigData('account');

            $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('customer/session')->getOrderId());
			$payment = $order->getPayment();
			$additionaldata = unserialize($payment->getAdditionalData());
			$md = Mage::helper('core')->decrypt($additionaldata['md']);

			$values = $this->_explodeMD($md);

	        $orderid = $values['ORDERID'];
			$currency = $values['CURRENCY'];
			$amount = $values['AMOUNT'] * 100;
			$cardnumber = $values['CARDNUMBER'];
			$expdate = $values['EXPDATE'];
			$cardtype = $values['CARDTYPE'];
			$cardname = $values['CARDNAME'];
			$cvc = $values['CVC'];

			$secret = $this->getConfigData('pwd');
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

            $productID = '';
            if(!(count($products)>1)){
                $productID = $products[0];
            }

            $ip = Mage::getSingleton('checkout/session')->getQuote()->getRemoteIp();

            $billing = $order->getBillingAddress();
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

	            $shippingPostcodeNumbers = preg_replace('/[^\d]/', '', $shippingPostcode);

	            $shippingStreetName = $shipping->getStreet1();
	            preg_match('{(\d+)}', $shippingStreetName, $m);
	            if(isset($m[1])){
	                $shippingStreetNumber = $m[1];
	            }else{
	                $shippingStreetNumber = '';
	            }

				$shippingCode = $shippingPostcodeNumbers . '|' . $shippingStreetNumber;
            }

	    	$request = "<request type='auth' timestamp='$timestamp'>
	                        <merchantid>$merchantid</merchantid>
							<account>$account</account>
	                        <orderid>$orderid</orderid>
	                        <amount currency='$currency'>$amount</amount>
	                        <card>
	                            <number>$cardnumber</number>
	                            <expdate>$expdate</expdate>
	                            <chname>$cardname</chname>
	                            <type>$cardtype</type>
		                        <cvn>
		                            <number>$cvc</number>
		                            <presind>1</presind>
		                        </cvn>
	                        </card>
	                        <autosettle flag='0'/>
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
                                    <code>$shippingCode</code>
                                    <country>$shippingCountry</country>
                                </address>
                            </tssinfo>
                            <mpi>
                                <cavv>$cavv</cavv>
                                <xid>$xid</xid>
                                <eci>$eci</eci>
                            </mpi>
                            <sha1hash>$sha1hash</sha1hash>
                        </request>";

			if($this->getConfigData('debug')){
				Mage::log($request);
			}

			return $request;
	    }

	    protected function _setECI($cardtype){
		    if($cardtype == 'visa'){
				$eci = "6";
	    	}else if($cardtype == 'mc' || $cardtype == 'switch'){
	    		$eci = "1";
	    	}else{
	    		$eci = "7";
	    	}
	    	return $eci;
	    }


	public function build3DSVerifySigRequest($pares, $md){

			$payment = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('customer/session')->getOrderId())->getPayment();
			$additionaldata = unserialize($payment->getAdditionalData());
			$data = Mage::helper('core')->decrypt($additionaldata['md']);

	        $values = explode('&', $data);
	        foreach($values as $value){
	        	$temp = explode('=', $value);
	        	$$temp[0] = $temp[1];
	        }

	        // The Timestamp is created here and used in the digital signature
	        $timestamp = strftime("%Y%m%d%H%M%S");
	        mt_srand((double)microtime()*1000000);

		    $merchantid = $this->getConfigData('login');
	   	    $secret = $this->getConfigData('pwd');
		    //Get information from order and set the appropriate variables.

	        $orderid = $ORDERID;
			$currency = $CURRENCY;
			$amount = $AMOUNT * 100;
			$ccnumber = $CARDNUMBER;
			$expdate = $EXPDATE;
			$cardtype = $CARDTYPE;
			$cardname = $CARDNAME;

			$timestamp = strftime("%Y%m%d%H%M%S");
	        mt_srand((double)microtime()*1000000);

	   		// This section of code creates the shahash that is needed
	        $tmp = "$timestamp.$merchantid.$orderid.$amount.$currency.$ccnumber";
	        $sha1hash = sha1($tmp);
	        $tmp = "$sha1hash.$secret";
	        $sha1hash = sha1($tmp);

	    	$xml = "<request timestamp='$timestamp' type='3ds-verifysig'>
						<merchantid>$merchantid</merchantid>
						<account />
						<orderid>$orderid</orderid>
						<amount currency='$currency'>$amount</amount>
						<card>
							<number>$ccnumber</number>
							<expdate>$expdate</expdate>
							<type>$cardtype</type>
							<chname>$cardname</chname>
						</card>
						<pares>$pares</pares>
						<sha1hash>$sha1hash</sha1hash>
						</request>";

			if($this->getConfigData('debug')){
				Mage::log($xml);
			}

			return $xml;
	    }

	    protected function _getAVSResponse($code){
	    	switch($code){
	    		case 'M':
	    			return 'Matched';
	    			break;
	    		case 'N':
	    			return 'Not Matched';
					break;
	    		case 'I':
	    			return 'Problem with check';
					break;
	    		case 'U':
	    			return 'Unable to check';
					break;
	    		case 'P':
	    			return 'Partical Matched';
					break;
	    	}
	    }
}
      
?>