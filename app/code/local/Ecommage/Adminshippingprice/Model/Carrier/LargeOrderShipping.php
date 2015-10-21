<?php  
    class Ecommage_Adminshippingprice_Model_Carrier_LargeOrderShipping
		extends Mage_Shipping_Model_Carrier_Abstract
		implements Mage_Shipping_Model_Carrier_Interface
	{  
        protected $_code = 'large_order_shipping';  
      
        /** 
        * Collect rates for this shipping method based on information in $request 
        * 
        * @param Mage_Shipping_Model_Rate_Request $data 
        * @return Mage_Shipping_Model_Rate_Result 
        */  
        public function collectRates(Mage_Shipping_Model_Rate_Request $request){
            if (!Mage::getStoreConfig('carriers/'.$this->_code.'/active')) {
                return false;
            }
            $result = Mage::getModel('shipping/rate_result');
            if(Mage::getSingleton('core/session')->getQuoteDetails() != ''){

                $quote_id = Mage::getSingleton('core/session')->getQuoteDetails();
                $shippingSku = Mage::getSingleton("core/session")->getShippingSkuQuote();
                $quote_details = Mage::getModel('Quotation/Quotation')->load($quote_id);
                if($quote_details->getPriceHt()) {
                    $results_query = Mage::helper('adminshippingprice')->getLabelMatrixRate($shippingSku);
                    $method = Mage::getModel('shipping/rate_result_method');
                    $method->setCarrier($this->_code);
                    if ($results_query[0]['delivery_method'] != '') {
                        $method->setCarrierTitle($results_query[0]['delivery_method']);
                    } else {
                        $method->setCarrierTitle('UK');
                    }

                    $method->setMethod($this->_code);
                    if ($results_query[0]['delivery_type'] != '') {
                        $method->setMethodTitle($results_query[0]['delivery_type']);
                    } else {
                        $method->setMethodTitle('Large Order (request a quote)');
                    }
                    $method->setMethodDescription('UK Large Order (request a quote)-UPS');
                    $method->setPrice($quote_details->getShippingCost());
                    $method->setCost($quote_details->getShippingCost());
                    $result->append($method);
                }
            }
            return $result;
        }  

		/**
		 * Get allowed shipping methods
		 *
		 * @return array
		 */
		public function getAllowedMethods()
		{
            $shippingSku = Mage::getSingleton("core/session")->getShippingSkuQuote();
            $results_query = Mage::helper('adminshippingprice')->getLabelMatrixRate($shippingSku);
            if ($results_query[0]['delivery_method'] != '') {
                return array($this->_code=>$results_query[0]['delivery_method']);
            } else {
                return array($this->_code=>'Shipping Price');
            }
		}
    }  
