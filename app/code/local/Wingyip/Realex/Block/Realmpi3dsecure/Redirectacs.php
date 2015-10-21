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
class Wingyip_Realex_Block_Realmpi3dsecure_Redirectacs extends Mage_Core_Block_Template
{
    
   
    
     public function getEnrollmentdetail(){
         
         
         $RealexEnrollRegister  = Mage::registry('CardenrollmentId');
         $RealexEnrollId  = $RealexEnrollRegister['id'];    
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
         
      public function getRealexEnrolledMD(){  
         $RealexEnrollRegister  = Mage::registry('CardenrollmentId');
         

         $RealexEnrolledMD  = $RealexEnrollRegister['MD'];
         return $RealexEnrolledMD;
            
      }     
}
