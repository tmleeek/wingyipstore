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
class Wingyip_Realex_RealmpiController extends Mage_Core_Controller_Front_Action
{
    
    protected function _expireAjax() {
        if (!Mage :: getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
            exit;
        }
    }
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
    
    public function enrollcardAction(){        
         
       
        $TermUrl = Mage::getUrl("")."realex/realmpi/response3dsecure";
        
        $RealexObj  = Mage::getModel('realex/realex'); 
        $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
        $RealexEnrollData = Mage::getModel('realex/realmpi3dsecure')->enrollCard();
        
        //echo "<pre>";
        //print_r($RealexEnrollData);
        //exit;                          
                 
        $Enrollmentdetail =  $this->getEnrollmentdetail($RealexEnrollData['id']);
        $RealexEnrolledMD = $RealexEnrollData['MD'];
        //echo "comming0";exit;
        $htmlForClosePopup  = "</br></br><button class='button' onclick=\"parent.closePopup()\">".$this->__("Click here to try again!")."</button>";
        if($Enrollmentdetail->getAcsUrl() && $RealexEnrolledMD != ''){
              
               $html ='<html><body>'; 
               
               $html.='<form action="' . $Enrollmentdetail->getAcsUrl() . '" id="acsform" name="acsform" method="POST">';
               $html.='<input name="form_key" type="hidden" value="' . Mage::getSingleton('core/session')->getFormKey() . '" /></div><input id="PaReq" name="PaReq" value="'.$Enrollmentdetail->getEnrollmentPareq().'" type="hidden"/>';
               $html.='<input id="TermUrl" name="TermUrl" value="'.$TermUrl.'" type="hidden"/>';
               $html.='<input id="MD" name="MD" value="'.$RealexEnrolledMD .'" type="hidden"/>';
               $html.= ' </form>';
               $html.= ' <script type="text/javascript">document.getElementById("acsform").submit();</script>';
               $html.= '</body></html>';
               
               
              
               return $this->getResponse()->setBody($html);     
           
          
          // Design for Pop up
           
           }
//        elseif($Enrollmentdetail->getEnrollmentResult() == '110'){
//
//
//                 $html ='<html><body>';
//
//                 $html.= $this->__("The Cardholder is not enrolled");
//                 $html.= $htmlForClosePopup;
//                 $html.= '</body></html>';
//                 return $this->getResponse()->setBody($html);
//           }
        elseif($Enrollmentdetail->getEnrollmentResult() == '220'){
               
                 $html ='<html><body>';
                 $html.= $this->__("Card scheme directory server may be unavailable."); 
                 $html.= $htmlForClosePopup;
                 $html.= '</body></html>';   
                 return $this->getResponse()->setBody($html);     
           }else{
               
                 $html ='<html><body>';
                 $html.= $this->__($Enrollmentdetail->getEnrollmentMessage()); 
                 $html.= $htmlForClosePopup;
                 $html.= '</body></html>';   
                 return $this->getResponse()->setBody($html);     
               
           }
    }

    public function invoice($orderId){
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        if($order->canInvoice() && $orderId){
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            $invoice->register();
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();
            $invoice->sendEmail();
            $invoice->setEmailSent(true);
            $invoice->save();
            $order->addStatusHistoryComment(Mage::helper('paypal')->__('Notified customer about invoice #%s.', $invoice->getIncrementId()));
            $order->save();
        }


    }
    
      public function response3dsecureAction(){ 
        
        
        $paymentResponse = $this->getRequest()->getParams(); 
        $RealexverifysigData = Mage::getModel('realex/realmpi3dsecure')->getverifySig($paymentResponse);
              
        /*echo "<pre>";
        print_r($RealexverifysigData);*/   
        
        $this->_expireAjax();
        $resultData = array();
        
        $this->writeStatus($RealexverifysigData);
        if($RealexverifysigData->result == "00"){
        try {

                Mage::helper('realex')->validateQuote();
           
                $result = $this->getRealex3dModel()->registerTransaction();
                $op = Mage :: getSingleton('checkout/type_onepage');
                $op->getQuote()->collectTotals();

                Mage::helper('realex')->ignoreAddressValidation($op->getQuote());
                $op->saveOrder();
                $resultData = array(
                    'success' => 'true',
                    'response_status' => 'OK'
                );                      
                Mage::helper('realex')->deleteQuote();           
        } catch (Exception $e) {
        
            $Error = array($RealexverifysigData,$e);
            $this->writeStatus($Error);
            $resultData['response_status'] = 'ERROR';
            $resultData['response_status_detail'] = $e->getMessage();
            Mage::getSingleton("checkout/session")->addError($e->getMessage()); 

           // Mage::dispatchEvent('realex_payment_failed', array('quote' => Mage::getSingleton('checkout/type_onepage')->getQuote(), 'message' => $e->getMessage()));

        }   
      }else{
            
            //Mage::getSingleton("checkout/session")->addError($RealexverifysigData->message);
            $htmlForClosePopup  = "</br></br><button class='button' onclick=\"parent.closePopup()\">".$this->__("Click here to try again!")."</button>"; 
            $html ='<html><body><style>.button {background-color: #f1cb00;border: medium none;color: #000;font-family: "aachen_btroman";font-size: 14px;font-weight: normal;text-transform: uppercase;transition: all 0.4s ease 0s;}</style>';
               
            $html.= $RealexverifysigData->result." ".$RealexverifysigData->message; 
            $html.= $htmlForClosePopup;
            $html.= '</body></html>';   
            return $this->getResponse()->setBody($html);
            exit;         
      }
      
      if($resultData["response_status"]=='OK' && $resultData["success"]==true){
              $session = Mage::getSingleton('checkout/session');
              $orderId = $session->getLastRealOrderId();
              $this->invoice($orderId);
            $redirectUrl = Mage::getUrl('checkout/onepage/success');
        }else{ 
            $redirectUrl = Mage::getUrl('checkout/onepage/failure'); 
       } 
         
      $html = '<html><body>';
      $html.= '<script type="text/javascript">(parent.location == window.location)? window.location.href="' . $redirectUrl . '" : window.parent.setLocation("' . $redirectUrl . '");</script>';
        //$html.= '<script type="text/javascript">window.parent.setLocation("' . $successUrl . '");</script>';
      $html.= '</body></html>';
    
        return $this->getResponse()->setBody($html);              
    }
    public function getRealex3dModel() {
        return Mage :: getModel('realex/realmpi3dsecure');
    }
    
    public function getEnrollmentdetail($RealexEnrollId){
   
         $RealexObj  = Mage::getModel('realex/realex');
         $realexEnrollmentData = '';   
         if(!$RealexEnrollId){
               $quoteId = Mage::getSingleton('checkout/session')->getQuote()->getId();
               $realexEnrollmentData = $RealexObj->getCollection()->addFieldToFilter("enrollment_cart_quote_id",$quoteId)->addFieldToFilter("is_enrolled","Y")->getFirstItem();
         }else{
              //echo "-->".$RealexEnrollId;exit;  
             $realexEnrollmentData =  $RealexObj->load($RealexEnrollId);
         }

            return $realexEnrollmentData;
         }
         
   public function writeStatus($PostedData){
        Mage::log(json_encode($PostedData), null,"realmpi-".date("Y-m-d-H-i-s").'.log');
   }
}
