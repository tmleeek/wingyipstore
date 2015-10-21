<?php 
/**
 * API model access for SagePay
 *
 * @category   Wingyip
 * @package    Wingyip_Realex
 * @author     Wingyip <info@wingyip.com>
 */
class Wingyip_Realex_Model_Api_Payment extends Mage_Payment_Model_Method_Cc {

    protected $_code = '';
    protected $_canManageRecurringProfiles = false;
    protected $_quote = null;
    protected $_canEdit = TRUE;

    const BASKET_SEP = ':';
    const RESPONSE_DELIM_CHAR = "\r\n";
    const REQUEST_BASKET_ITEM_DELIMITER = ':';
    const RESPONSE_CODE_APPROVED = 'OK';
    const RESPONSE_CODE_REGISTERED = 'REGISTERED';
    const RESPONSE_CODE_DECLINED = 'OK';
    const RESPONSE_CODE_ABORTED = 'OK';
    const RESPONSE_CODE_AUTHENTICATED = 'OK';
    const RESPONSE_CODE_REJECTED = 'REJECTED';
    const RESPONSE_CODE_INVALID = 'INVALID';
    const RESPONSE_CODE_ERROR = 'ERROR';
    const RESPONSE_CODE_NOTAUTHED = 'NOTAUTHED';
    const RESPONSE_CODE_3DAUTH = '3DAUTH';
    const RESPONSE_CODE_MALFORMED = 'MALFORMED';

    const REQUEST_TYPE_PAYMENT = 'PAYMENT';
    const REQUEST_TYPE_VOID = 'VOID';

    const XML_CREATE_INVOICE = 'payment/sagepaydirectpro/create_invoice';

    const REQUEST_METHOD_CC = 'CC';
    const REQUEST_METHOD_ECHECK = 'ECHECK';

    const ACTION_AUTHORIZE_CAPTURE = 'payment';

    protected $ACSURL = NULL;
    protected $PAReq = NULL;
    protected $MD = NULL;

    private $_sharedConf = array(
                                 'sync_mode',
                                 'email_on_invoice',
                                 'trncurrency',
                                 'referrer_id',
                                 'vendor',
                                 'timeout_message',
                                 'connection_timeout',
                                 'send_basket',
                                 'sagefifty_basket',
                                 'basket_format',
                                 'curl_verifypeer',
                                 'layout_rewrites_active',
                                 'layout_rewrites',
                                 'ignore_address_validation',
                                 'send_payment_failed_emails',
    );

    /**
     * Flag to set if request can be retried.
     *
     * @var boolean
     */
    private $_canRetry = true;

    /**
     * BasketXML related error codes.
     *
     * @var type
     */
    private $_basketErrors = array(3021, 3195, 3177);

    /**
     * Can be edit order (renew order)
     *
     * @return bool
     */
    public function canEdit() {
        return $this->_canEdit;
    }

    protected function _getCoreUrl() {
        return Mage::getModel('core/url');
    }
     
    public function getQuote() {
        return $this->_getQuote();
    }
    protected function _getQuote(){

        $opQuote = Mage::getSingleton('checkout/type_onepage')->getQuote();
        $adminQuote = Mage::getSingleton('adminhtml/session_quote')->getQuote();

        $rqQuoteId = Mage::app()->getRequest()->getParam('qid');
        if ($adminQuote->hasItems() === false && (int) $rqQuoteId) {
            $opQuote->setQuote(Mage::getModel('sales/quote')->loadActive($rqQuoteId));
        }

        return ($adminQuote->hasItems() === true) ? $adminQuote : $opQuote;
    }
    public function getQuoteDb($sessionQuote){

        return $sessionQuote;

        /*
        @TODO: work on this further, it causes 0.00 Amount sometimes.
        $resource = $sessionQuote->getResource();
        $dbQuote = new Mage_Sales_Model_Quote;
        $resource->loadActive($dbQuote, $sessionQuote->getId());

        //For MOTO
        if( !((int)$dbQuote->getId()) ) {
            $resource->loadByIdWithoutStore($dbQuote, $sessionQuote->getId());
        }

        if( !((int)$dbQuote->getId()) ) {
            $dbQuote = $sessionQuote;
        }

        return $dbQuote;*/
    }
    /**
     * Format amount based on currency
     *
     * @param float $amount
     * @param string $currency
     * @return float|int
     */
    public function formatAmount($amount, $currency) {
        $_amount = 0.00;

        //JPY, which only accepts whole number amounts
        if ($currency == 'JPY') {
            $_amount = round($amount, 0, PHP_ROUND_HALF_EVEN);
        }
        else {
            $_amount = number_format(Mage::app()->getStore()->roundPrice($amount), 2, '.', '');
        }

        return $_amount;
    }
    public function getRealexSession(){
        return Mage::getSingleton('realex/session');
    } 
    protected function _getBuildPaymentObject($quoteObj, $params = array('payment' => array())){
        $payment = new Varien_Object;
        if (isset($params['payment']) && !empty($params['payment'])) {
            $payment->addData($params['payment']);
        }

        if (Mage::helper('realex')->creatingAdminOrder()) {
            $payment->addData($quoteObj->getPayment()->toArray());
        }

        $payment->setTransactionType(strtoupper($this->getConfigData('payment_action')));
        $payment->setAmountOrdered($this->formatAmount($quoteObj->getGrandTotal(), $quoteObj->getQuoteCurrencyCode()));
        $payment->setRealCapture(true); //To difference invoice from capture
        $payment->setOrder( (clone $quoteObj) );
        $payment->setAnetTransType(strtoupper($this->getConfigData('payment_action')));
        $payment->getOrder()->setOrderCurrencyCode($quoteObj->getQuoteCurrencyCode());
        $payment->getOrder()->setBillingAddress($quoteObj->getBillingAddress());

        if($quoteObj->isVirtual()) {
            $payment->getOrder()->setShippingAddress($quoteObj->getBillingAddress());
        }
        else {
            $payment->getOrder()->setShippingAddress($quoteObj->getShippingAddress());
        }

        return $payment;
    }
    
    
    
    /**
     * Prepare info instance for save
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function prepareSave()
    {
        $info = $this->getInfoInstance();
        if ($this->_canSaveCc) {
            $info->setCcNumberEnc($info->encrypt($info->getCcNumber()));
        }
        $info->setCcCidEnc($info->encrypt($info->getCcCid()));
        $info->setCcNumber(null)
            ->setCcCid(null);
        return $this;
    } 
    
}