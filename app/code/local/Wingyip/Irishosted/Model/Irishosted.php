<?php 

class Wingyip_Irishosted_Model_Irishosted extends Mage_Payment_Model_Method_Abstract
{
	
	/**
     * Payment method code
     *
     * @var string
     */
    protected $_code = 'irishosted';

    /**
     * payment block paths
     *
     * @var string
     */
    protected $_formBlockType = 'irishosted/form';

	protected $_isInitializeNeeded          = true;
	protected $_canUseInternal              = false;
	protected $_canUseForMultishipping              = false;
	
	 /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
		$this->debugLog("getOrderPlaceRedirectUrl is called");
          return Mage::getUrl('irishosted/standard/redirect', array('_secure' => true));
    }
	
	public function getOrderPlaceResponseUrl()
    {	$this->debugLog("getOrderPlaceResponseUrl is called");
          return Mage::getUrl('irishosted/standard/response', array('_secure' => true));
    }
	
	
	public function getCheckoutRequestFormFields(){
		
		
		$this->debugLog("Hello In getCheckoutRequestFormFields");
		$checkoutSession = Mage::getSingleton('checkout/session');

		$orderIncrementId = $checkoutSession->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
		
		$helper = Mage::helper('irishosted');
		
		$timestamp = strftime("%Y%m%d%H%M%S");
		$merchantId		= $this->getMerchantId();
		$amount			=  $helper->getOrderAmount($order);
		$amount 		= ($amount * 100); 
		$account		= $this->getSubAccountType();
		$currency 		= Mage::app()->getStore()->getCurrentCurrencyCode();
		
		$sha1hash		= $this->getSHA1Hash($timestamp, $merchantId, $orderIncrementId, $amount, $currency);
		/** Where to set Response URL ? */
		
		$formFields = array(
			'MERCHANT_ID'	=> $merchantId,
			'ORDER_ID'		=> $orderIncrementId,
			'ACCOUNT'		=> $account,
			'AMOUNT'		=> $amount,
			'CURRENCY'		=> $currency,
			'TIMESTAMP'		=> $timestamp,
			//'OFFER_SAVE_CARD' => '1',
			//'PAYER_EXIST'	=> $this->getPayerExist(),
			'SHA1HASH'		=> $sha1hash,
			'AUTO_SETTLE_FLAG' => '1',
			'MERCHANT_RESPONSE_URL' => $this->getOrderPlaceResponseUrl(),
		);
		
		return $formFields;
		
		/**
		* AUTO_SETTLE_FLAG ==> 'Used to signify whether or not you wish the transaction to be captured in the next batch. If set to “1” and assuming the transaction is authorised then it will automatically be settled in the next batch. If set to “0” then the merchant must use the RealControl application to manually settle the transaction. This option can be used if a merchant wishes to delay the payment until after the goods have been shipped. Transactions can be settled for up to 115% of the original amount and must be settled within a certain period of time agreed with your issuing bank.'
		*
		*MERCHANT_RESPONSE_URL ==> 'Used to set which URL that Global Iris will send the transaction response to. When you are creating your account with Global Iris , you will be asked to provide a response URL for your account. If this field is sent in the post request, it takes precedence over the response URL set on your Global Iris account. If Global Iris cannot connect to this URL, the response URL set on your account will be attempted.'
		*
		*
		*
		*
		/*
		<input type="hidden" name="MERCHANT_ID" value="merchant-id">
		<input type="hidden" name="ORDER_ID" value="unique order-id">
		<input type="hidden" name="ACCOUNT" value="sub account name">
		<input type="hidden" name="AMOUNT" value="amount">
		<input type="hidden" name="CURRENCY" value="currency code">
		<input type="hidden" name="TIMESTAMP" value="yyyymmddhhmmss">
		<input type="hidden" name="OFFER_SAVE_CARD" value="1">
		<input type="hidden" name="PAYER_REF" value="abc">
		<input type="hidden" name="PMT_REF" value="mycard1">
		<input type="hidden" name="PAYER_EXIST" value="0">
		<input type="hidden" name="SHA1HASH" value="32 character string">
		<input type="hidden" name="AUTO_SETTLE_FLAG" value="1 or 0">
	*/	
	}
	
	
	/******************** */
	/**
    * Check whether there are CC types set in configuration
    * 
    * @param mixed $quote
    * @return string|null
    */
    public function isAvailable($quote = null)
    { 
        return (parent::isAvailable($quote) && ((Mage::getStoreConfig('advanced/modules_disable_output/Wingyip_Irishosted')=="" || Mage::getStoreConfig('advanced/modules_disable_output/Wingyip_Irishosted') == '0')));
    }

    /**
    * Refund specified amount for payment
    *
    * @param Varien_Object $payment
    * @param float $amount
    * @return Mage_Payment_Model_Abstract
    */
    public function refund(Varien_Object $payment, $amount)
    { 
        /* Defind Helper Class to variable  */
        $_helper = Mage::helper('irishosted');

        if (!$this->canRefund()) {
            Mage::throwException($_helper->__('Refund action is not available.'));
        }


        /* Check Refund amount */
        if ($payment->getRefundTransactionId() && $amount > 0) {
            $order = $payment->getOrder();
            if ($order->getId()) {

                /* Gether refund required field from order */
               /* $refundFields = $this->getOrderRefundFields($order, $amount);

                if(!empty($refundFields)){
                    /* Doing Process for refund * /
                    $response = $this->orderRefundProcess($refundFields);
                }else{
                    /* Throw Exception if Refund fieds are blank * /
                    Mage::throwException($_helper->__('Wingyip IrisHosted refund failed.'));
                }

                $_transactionId = (string)$response->ewayTrxnNumber;

                /* check weather refund process is success or not * /
                if(strtolower($response->ewayTrxnStatus)=='true'){
                    $messageArry = explode(",",$response->ewayTrxnError);
                    $message =  $messageArry[1];
                    $payment->setTransactionId($_transactionId);
                    $payment->setIsTransactionClosed(1);
                    $payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,  array('TrxnNumber'=>$_transactionId,'message'=>$message)); 
                    return $this;
                }

                /* Throw Exception if Refund goes fail * /
                if(!$transactionId){
                    Mage::throwException($_helper->__('KGN eWAY-RAPID refund failed.'));
                } */
            }
            else {
                Mage::throwException($_helper->__('Invalid order for refund.'));
            }
        }
        else {
            Mage::throwException($_helper->__('Invalid transaction for refund.'));
        }
        return $this;
    }

    /**
    * Capture payment
    *
    * @param Varien_Object $payment
    * @param float $amount
    * @return Mage_Payment_Model_Abstract
    */
    public function capture(Varien_Object $payment, $amount)
    { 
        /* Defind Helper Class to variable  */
        $_helper = Mage::helper('ewayrapid');

        if ($amount <= 0) {
            Mage::throwException($_helper->__('Invalid amount for capture.'));
        }

        $order = $payment->getOrder();
        /* Get Fields for CreateAccessCode process */
        $accessFields = $this->getCreateAccessCodeFields($order);

        try {  
            /* Generating Access code */
            $response = $this->sendCreateAccessCodeRequest($accessFields);
        }
        catch (Exception $e) {
            Mage::throwException($e->faultstring);
        }

        /* parsing return XML to Object */
        $response = $_helper->XML2Obj($response);

        if (isset($response->AccessCode) && !empty($response->AccessCode)) {
            $_accessCode = (string)$response->AccessCode;
			$url			=(string)$response->FormActionURL;
			$this->formUrl=$url;
			
            $resultFields = array();
            $resultFields['AccessCode'] = $_accessCode;
		
			$this->accessCodeData = $_accessCode;
			
			Mage::getSingleton('core/session')->setAccessCodeData($_accessCode);

            $paymentCardInfo = Mage::app()->getRequest()->getParam('payment');

            $_cardName = ($payment->getCcOwner()  != NULL )? $payment->getCcOwner(): $paymentCardInfo['cc_owner'];

            if($_cardName==""){
                $_cardName = $_helper->__("No Name");    
            }

            $_cardNumber = ($payment->getCcNumber() != NULL )? $payment->getCcNumber(): $paymentCardInfo['cc_number'];
            $_ccExpMonth = ($payment->getCcExpMonth()  != NULL )? $payment->getCcExpMonth(): $paymentCardInfo['cc_exp_month'];
            $_cardExpMonth =  sprintf("%02d",$_ccExpMonth);

            $_ccExpYear = ($payment->getCcExpYear()  != NULL )? $payment->getCcExpYear(): $paymentCardInfo['cc_exp_year'];
            $_cardExpYear = sprintf("%02d",$_ccExpYear);

            $_ccStartMonth = ($payment->getCcStartMonth()  != NULL )? $payment->getCcStartMonth(): @$paymentCardInfo['cc_start_month'];
            $_cardStartMonth= sprintf("%02d",@$_ccStartMonth);
            $_cardStartMonth = ($_cardStartMonth!="00")?$_cardStartMonth:"";

            $_ccStartYear = ($payment->getCcStartYear()  != NULL )? $payment->getCcStartYear(): @$paymentCardInfo['cc_start_year'];
            $_cardStartYear = sprintf("%02d",@$_ccStartYear);
            $_cardStartYear = ($_cardStartYear!="00")?$_cardStartYear:"";

            $_cardIssue = ($payment->getCcIssue()  != NULL )? $payment->getCcIssue(): @$paymentCardInfo['cc_issue'];
            $_cardCVN = ($payment->getCcCid() != NULL )? $payment->getCcCid(): $paymentCardInfo['cc_cid'];

            /* Build array for Process on Access code */
            $feilds = array(
            'EWAY_ACCESSCODE' =>$_accessCode,
            'EWAY_CARDNAME' => $_cardName,
            'EWAY_CARDNUMBER' =>$_cardNumber,
            'EWAY_CARDEXPIRYMONTH' =>$_cardExpMonth,
            'EWAY_CARDEXPIRYYEAR' =>$_cardExpYear,
            'EWAY_CARDSTARTMONTH' =>$_cardStartMonth,
            'EWAY_CARDSTARTYEAR' =>$_cardStartYear,
            'EWAY_CARDISSUENUMBER' =>$_cardIssue,
            'EWAY_CARDCVN' =>$_cardCVN
            );

            /* do process on Access code with given field */ 
            $this->_sendAccessCodeProcessRequest($feilds,$url);

           
        }else {
            Mage::throwException($_helper->__('Failed to access eWAY. Please contact the merchant.'));
        } 
        return $this;
    }

    /**
    * Authorize payment abstract method
    * 
    * @param Varien_Object $payment
    * @param mixed $amount
    */
    public function authorize(Varien_Object $payment, $amount)
    {
    
        $orderId = $payment->getOrder()->getIncrementId();
        $this->totalammount = $amount;
        $this->orderId = $orderId;
        $this->orderCode = $this->orderCode."-".time();

        try {
            $this->StartXML();
            $this->FillDataXML($orderContent);
            $this->FillShopperXML($shopperArray);
            $this->EndXML();
            $this->xml = utf8_encode($this->xml);
            $bibitResult = $this->CreateConnection();

            $resultArray = array(    
            "currentTag"  => "",
            "orderCode"   => "",
            "referenceID" => "",
            "errorcode"   => "",
            "url_togoto"  => ""
            );

            $dt = $this->ParseXML($bibitResult);

        } catch (Exception $e) {
            $payment->setStatus(self::STATUS_ERROR);
            $payment->setAmount($amount);
            $payment->setLastTransId($orderId);
            $this->setStore($payment->getOrder()->getStoreId());
            Mage::throwException($e->getMessage());
        }

        $xmlResponse = new SimpleXmlElement($data); //Simple way to parse xml, Magento might have an equivalent class
        $isPaymentAccepted = $xmlResponse->isPaymentAccepted == 1;
        if ($isPaymentAccepted) {
            $this->setStore($payment->getOrder()->getStoreId());
            $payment->setStatus(self::STATUS_APPROVED);
            $payment->setAmount($amount);
            $payment->setLastTransId($orderId);
        } else {
            $this->setStore($payment->getOrder()->getStoreId());
            $payment->setStatus(self::STATUS_DECLINED);
            $payment->setAmount($amount);
            $payment->setLastTransId($orderId);
        }
        return $this;
    }

/********************** */
	
	
	
	 /**
    * Generate Log to for Server Request & Response
    * 
    * @param mixed $request
    * @param mixed $response
    */
    public function setServerRequestLog($request = "", $response = ""){
        if($this->getConfigData('log_request')){
            $dt  = "\n\n ------------Request ".date('Y-m-d H:i:s')." ------------ \r\n ";
            $dt .= $request;
            $dt .= "\n\n ------------Response ------------\r\n ";
            $dt .= $response;
            $dt .= "\n\n ------------END ------------\r\n ";

            Mage::log($dt, null, Wingyip_Irishosted_Helper_Data::LOG_FILE_NAME);
        }
    }
	
	public function debugLog($comment = ""){
        if($this->getConfigData('log_request')){
			if($comment){
				$dt  = "\n\n ------------".date('Y-m-d H:i:s')." ------------ \r\n ";
				$dt  .=  $comment;
				$dt .= "\n\n ------------END ------------\r\n ";
				
				Mage::log($dt, null, Wingyip_Irishosted_Helper_Data::LOG_FILE_NAME);
			}
           
        }
    }

	public function getMerchantId(){
		return Mage::getStoreConfig('payment/irishosted/merchant_id');
	}
	
	public function getSubAccountType(){
		return Mage::getStoreConfig('payment/irishosted/sub_account');
	}
	
	
	public function getPayerExist(){
		
		$customerSession = Mage::getSingleton('customer/session');
		if($customerSession->isLoggedIn()){
			$customerID = $customerSession->getId();
			$customerModel = Mage::getModel('customer/customer')->load($customerID);
			if($customerIrisPayerRef = $customerModel->getIrisPayerRef()){
				return '1';
			}
		}
		return '0';
		
	}

	public function getMD5Hash($timestamp, $marchentId, $orderId, $amount, $currency){
			$tmp = "$timestamp.$marchentId.$orderId.$amount.$currency";
			$hashing = md5($tmp);
			
			$secret = $this->getShareSecretKey();
			$tmp = "$hashing.$secret";
			$hashing = md5($tmp);
			
			return $hashing;
	}
	public function getSHA1Hash($timestamp, $marchentId, $orderId, $amount, $currency){
			$tmp = "$timestamp.$marchentId.$orderId.$amount.$currency";
			$hashing = sha1($tmp);
			
			$secret = $this->getShareSecretKey();
			$tmp = "$hashing.$secret";
			$hashing = sha1($tmp);
			
			return $hashing;
	}
	
	public function getShareSecretKey(){
			return Mage::getStoreConfig('payment/irishosted/shared_secret_key');
	}
	
	public function getIsTestMode(){
		return Mage::getStoreConfig('payment/irishosted/is_sandbox_mode');
	}
	
	
}
