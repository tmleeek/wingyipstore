<?php

class MDN_Quotation_Model_Quotation_Cart extends Mage_Core_Model_Abstract {

	  const ZIP_CODE_MAX_LENGTH = 2;
    /**
     * Add quote to cart
     */
    public function addToCart($quote, $controller) {
 
        //Create bundle & promotion
        //$quote->commit();
        
        //empty cart before adding quote (if configured)
        if (Mage::getStoreConfig('quotation/cart_options/empty_cart_before_adding_quote') == 1)
            Mage::helper('quotation/Cart')->emptyCart();

        //add bundle product
        $cart = Mage::getSingleton('checkout/cart');
		
        /*
        $product = $quote->GetLinkedProduct();
        if ($product != null) {
            //add options
            $option_array = array();
            $options = Mage::getModel('bundle/option')->getCollection()->addFilter('parent_id', $product->getid());
            foreach ($options as $option) {
                $selection_array = array();
                $selections = Mage::getModel('bundle/selection')->getCollection()->addFilter('option_id', $option->getId());
                foreach ($selections as $selection) {
                    if ($selection->getOption_id() == $option->getId())
                    {
                        $selection_array[] = $selection->getselection_id();
                    }
                }
                $option_array[$option->getId()] = $selection_array;
            }

            //add custom options
            $custom_option_array = array();
            $CustomOptions = $product->getProductOptionsCollection();
            foreach ($CustomOptions as $CustomOption) {
                $selection_array = array();
                $values = $CustomOption->getValues();
                foreach ($values as $value) {
                    $selection_array[] = $value->getid();
                }
                $custom_option_array[$CustomOption->getId()] = $selection_array;
            }

            //array for events
            $eventArgs = array(
                'qty' => '1',
                'additional_ids' => array(),
                'request' => $controller->getRequest(),
                'response' => $controller->getResponse(),
                'bundle_option' => $option_array,
                'options' => $custom_option_array,
                'product' => $product->getId()
            );

            //add product to cart
            Mage::dispatchEvent('checkout_cart_before_add', $eventArgs);
            $cart->addProduct($product, $eventArgs);
            Mage::dispatchEvent('checkout_cart_after_add', $eventArgs);
        }
        */

        //add excluded (optional) products
        foreach ($quote->getItems() as $item) {
            //if ($item->getexclude() == 1) {
                if ($item->getproduct_id() != '') {
                    $product = Mage::getModel('catalog/product')->load($item->getproduct_id());
                    if ($product->getId() == $item->getproduct_id()) {
                        $info = array();
                        $info['qty'] = $item->getqty();
                        $info['custom_price'] = $item->getPriceIncludingDiscount();
                        $info['price'] = $item->getPriceIncludingDiscount();
				
                        //get propduct options
                        $info['options'] = $item->getOptionsForAddToCart();
                                             
                        Mage::dispatchEvent('checkout_cart_before_add', $info);
                        $cart->addProduct($product, $info);
                        Mage::dispatchEvent('checkout_cart_after_add', $info);
                    }

                    //retrieve added item to customize price
                    foreach ($cart->getQuote()->getItemsCollection() as $cartItem) {
                        if ($cartItem->getproduct_id() == $item->getproduct_id()) {
                            $cartItem->setCustomPrice($item->getPriceIncludingDiscount());
                            $cartItem->setPrice($item->getPriceIncludingDiscount());
                            $cartItem->setOriginalCustomPrice($item->getPriceIncludingDiscount());
                        }
                    }
                }
            //}
        }
		
		$cart->save();
		
      
		$salesQuote=$cart->getQuote();//Mage::getSingleton('sales/quote');
		
		$customerAddressId=$quote->getAddressId();
		$customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
		//$quoteShippingAddress = new Mage_Sales_Model_Quote_Address();
		$address = $salesQuote->getBillingAddress();//->setData($customer_address->getData()); //pull the data from one and put it in the other.
		$address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
		$address->implodeStreetAddress();
		
		$billing = clone $address;
		//$billing->unsAddressId()->unsAddressType();
 		
		
		$setBillingAddress=Mage::getModel('sales/quote_address')
							->setData($salesQuote->getBillingAddress()->getData())
							->save();

		$addressItems = Mage::getModel('sales/quote_address')->getCollection()
				->addFieldToFilter('quote_id',$salesQuote->getId())
				->addFieldToFilter('address_type','shipping')
				->getFirstItem()
				;
		
		$shippingMethod=$this->getShippingMethod($billing,$quote);
				
		$setBillingAddress=Mage::getModel('sales/quote_address')
							->setData($billing->getData())
							->setAddressType('shipping')
                            ->setId($addressItems->getId())
                            ->setShippingMethod($shippingMethod['code'])
							->setShippingDescription($shippingMethod['method'])
							->setShippingAmount($quote->getShippingCost())
							->setBaseShippingAmount($quote->getShippingCost())
							->save();					
     
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$writeConnection = $resource->getConnection('core_write');
		$tableName = $resource->getTableName('sales_flat_quote_shipping_rate');
		
		$query = 'SELECT * FROM ' . $tableName.' where address_id='.$addressItems->getId().' and code="'.$shippingMethod['code'].'"';
		$results = $readConnection->fetchOne($query);
		
		if(!$results){
			
			//$updateQuery = "UPDATE {$tableName} SET sort_order = '{$sort}' WHERE option_id = ". (int)$optionId." and product_id=".$id;
			$insertQuery="INSERT INTO `{$tableName}` (`address_id` ,`created_at` ,`updated_at` ,`carrier` ,`carrier_title`,`code`,`method`,`method_description`,`price`,`error_message`,`method_title`,`shipping_sku`)VALUES (".$addressItems->getId()." , NOW(), NOW(), 'productmatrix', 'Shipping Option', '".$shippingMethod['code']."', '".str_replace("productmatrix","",$shippingMethod['method'])."', '', '".$quote->getShippingCost()."', '', '".$shippingMethod['method']."', '".$quote->getShippingSku()."')";
			
			
			$writeConnection->query($insertQuery);
		}
		
		
		$salesQuote->collectTotals();
		$salesQuote->save();
		
		
		
		//$salesQuote->getShippingAddress()->setCollectShippingRates(true);   
    }
	
	public function getShippingMethod($billing,$quote)
    {
        $billingAddress = $billing;//Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();
        $quotation = $quote;//Mage::getSingleton('core/session')->getQuotation();
        $shippingSku = $quotation->getShippingSku();
       // echo "<pre>"; print_r($shippingSku); exit;
        
		$zipcodeMaxLength = Mage::getStoreConfig('carriers/productmatrix/zipcode_max_length') ? Mage::getStoreConfig('carriers/productmatrix/zipcode_max_length') : self::ZIP_CODE_MAX_LENGTH;
        
		if('BR' == $billingAddress->getCountryId()){
            $splitPostcode = explode('-',$billingAddress->getPostcode());
            $postcode = $splitPostcode[0].$splitPostcode[1];
        }else{
            $postcode = substr($billingAddress->getPostcode(), 0, $zipcodeMaxLength);
        }
        $resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$writeConnection = $resource->getConnection('core_write');
		
        $sql        = "Select * from ".Mage::getSingleton('core/resource')->getTableName('productmatrix_shipping/productmatrix')." Where shipping_sku = '{$shippingSku}' and dest_zip='{$postcode}'";
        $rows       = $readConnection->fetchRow($sql);
        
		
		$$shipping=array();	
		$allowedMethod=Mage::getModel('productmatrix/carrier_source_freemethod')->toOptionArray();
		;
		foreach($allowedMethod as $_method)
		{
			if($rows['delivery_type']==$_method['label']){
				$shipping['code']='productmatrix_'.$_method['value'];
				$shipping['method']=$_method['label'];
				break;
			}
		} 
		
		return $shipping;
        /*$rates = Mage::getModel('sales/quote_address_rate')->getResourceCollection()->addFieldToFilter('method_title',array('like'=>'%'.$rows['delivery_type'].'%'));
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
            
            if (!$result) {
                Mage::dispatchEvent(
                    'checkout_controller_onepage_save_shipping_method',
                     array(
                          'request' => $this->getRequest(),
                          'quote'   => $this->getOnepage()->getQuote()));
                $this->getOnepage()->getQuote()->collectTotals();
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
                );
            }
            $this->getOnepage()->getQuote()->collectTotals()->save();
            echo 444;die;
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }*/
    }
}