<?php

/**
 * Magento Webshopapps Module
 *
 * @category   Webshopapps
 * @package    Webshopapps Wsacommon
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license    www.webshopapps.com/license/license.txt
 * @author     Karen Baker <sales@webshopapps.com>
*/

class Webshopapps_Productmatrix_Model_Observer extends Mage_Core_Model_Abstract
{
	public function postError($observer) {
		if (!Mage::helper('wsacommon')->checkItems('Y2FycmllcnMvcHJvZHVjdG1hdHJpeC9zaGlwX29uY2U=',
        	'dXBzaWRlZG93bg==','Y2FycmllcnMvcHJvZHVjdG1hdHJpeC9zZXJpYWw=')) {
				$session = Mage::getSingleton('adminhtml/session');
				$session->addError(Mage::helper('adminhtml')->__(base64_decode('U2VyaWFsIEtleSBJcyBOT1QgVmFsaWQgZm9yIFdlYlNob3BBcHBzIFByb2R1Y3RNYXRyaXg=')));
     	}
	}

    public function addTracking($observer) {
       try {

            $trackCode = null;
            $order = $observer->getEvent()->getOrder();
            
            if ($order->getIsVirtual()) {
               return;
            }
            $shippingMethodBefor = $order['shipping_method'];
            $shippingMethod = str_replace('productmatrix_', '', $shippingMethodBefor);

            $shippingId = $order['shipping_address_id'];
            $address = Mage::getModel('sales/order_address')->load($shippingId)->getData();
            
            // start of  shipping_sku
            $quoteId = $order->getQuoteId();
            $shippingQuoteItems = Mage::getModel('sales/quote_address')->getCollection()
            ->addFieldToFilter('quote_id',$quoteId)
            ->addFieldToFilter('address_type','shipping')
            ->getFirstItem(); 
            
            $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
            $select = $connection->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('sales_flat_quote_shipping_rate'), array('*')) 
            ->where('address_id=?',$shippingQuoteItems->getId())               
            ->where('code=?',$shippingMethodBefor);               
            
            $shippingRow =$connection->fetchRow($select);
            
            if($shippingRow['shipping_sku']){
                $observer->getEvent()->getOrder()
                    ->setShippingSku($shippingRow['shipping_sku'])
                    ->save();
            }
            // end of shipping_sku
            
            if (!array_key_exists('country_id',$address)) {
                return;    
            }
                
            $collection = Mage::getResourceModel('productmatrix_shipping/carrier_productmatrix_collection')
                ->setDeliveryTypeFilter($shippingMethod)
                //->setZipCodeFilter($address['postcode'])
                ->setWeightRange($order['weight'])
                ->setPriceRange($order['base_subtotal'])
                ->setQuantityRange($order['total_qty_ordered'])
                ->setCountryFilter($address['country_id'])
                ->load(); 
            
            foreach ($collection->getItems() as $item) {
                $data = $item->getData();
                
                $algorithm_array=explode("&",$data['algorithm']);
                reset($algorithm_array);

                foreach ($algorithm_array as $algorithm_single) {
                    $algorithm=explode("=",$algorithm_single,2);
                    $algKey = strtolower($algorithm[0]);

                    switch ($algKey) {
                        case "tracker":
                            $trackCode=explode("@",$algorithm[1]);
                            break;
                    }
                }
            }
            
            if(isset($trackCode[0])){
                $observer->getEvent()->getOrder()
                    ->setTracking($trackCode[0])
                    ->save();
            }
        }
        catch (Exception $e) {
            Mage::logException($e);
        }
    }
}