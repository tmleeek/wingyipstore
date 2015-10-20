<?php 
class Wingyip_Shipping_Model_Shipping extends Mage_Core_Model_Abstract{ 
    public function _construct()
    {
        parent::_construct();
        //$this->_init('recipe/recipe');
    }

    public function _getShippingMethod($label){
        $labelArrs = array_reverse(explode('-',$label));
        foreach($labelArrs as $labelArr){
            if(trim($labelArr) == 'UPS'){
                return trim($labelArr);
            }else if(trim($labelArr) == 'DPD'){
                return trim($labelArr);
            }
        }
        return $labelArrs[0];
    }

    public function getGeoSession()
    {
        $url = $this->getConfigValue('shipping/dpdclassic/webserviceurl')."user/?action=login";
        $host = "Host: api.dpd.co.uk";
        $contentType = "Content-Type: application/json";
        $accept = "Accept: application/json";
        $authVar = $this->getConfigValue('shipping/dpdclassic/userid').':'.Mage::helper('core')->decrypt($this->getConfigValue('shipping/dpdclassic/password'));
        $authorization = "Authorization: Basic ". base64_encode($authVar)."";
        $geoClient = $this->getConfigValue('shipping/dpdclassic/username').'/'.$this->getConfigValue('shipping/dpdclassic/accountnumber');
        $contentLength = "Content-Length:";

        $curl = curl_init($url);    
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);    
        curl_setopt($curl, CURLOPT_URL, $url );    
        curl_setopt($curl, CURLOPT_HTTPHEADER,array($host,$contentType,$accept,$authorization,$geoClient,$contentLength));  
        curl_setopt($curl, CURLOPT_POST, true);    
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //curl error SSL certificate problem, verify that the CA cert is OK
        $result     = curl_exec($curl);
        $response   = json_decode($result);  
        $geoSession = $response->data->geoSession;        
        curl_close($curl);
        return $geoSession;
    }

    public function getConfigValue($valuePath){
        return Mage::getStoreConfig($valuePath);
    }

    public function getShippingLabel($shipmentId){
        $url = $this->getConfigValue('shipping/dpdclassic/webserviceurl')."shipping/shipment/".$shipmentId."/label/";//49123016
        $host = "Host: api.dpd.co.uk";        
        $contentType = "Content-Type: application/json";
        $acceptJson = "Accept: application/json";
        $accepthtml = "Accept: text/html";
        $acceptEltron = "Accept: text/vnd.eltron-epl";
        $acceptCitizen = "Accept: text/vnd.citizen-clp";        
        $geoClient = $this->getConfigValue('shipping/dpdclassic/username').'/'.$this->getConfigValue('shipping/dpdclassic/accountnumber');
        $geoSession = "GEOSession: ".$this->getGeoSession()."";
        $contentLength = "Content-Length:";

        $curl = curl_init($url);    
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);    
        curl_setopt($curl, CURLOPT_URL, $url );    
        curl_setopt($curl, CURLOPT_HTTPHEADER,array($host,$accepthtml,$geoClient,$geoSession));  
        //curl_setopt($curl, CURLOPT_POST, false);    
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //curl error SSL certificate problem, verify that the CA cert is OK
        $result     = curl_exec($curl);
        curl_close($curl);      
        return $result;
    }
    
    public function doDpdShipmentRequest($shippingMethod){
        $data = $this->createDpdShipmentRequest($shippingMethod);
        $jsonData = Mage::helper('core')->jsonEncode($data);
        $dataLength =  strlen($jsonData);

        $url = $this->getConfigValue('shipping/dpdclassic/webserviceurl')."shipping/shipment";
        $host = "Host: api.dpd.co.uk";
        $contentType = "Content-Type: application/json";
        $accept = "Accept: application/json";
        $geoClient = $this->getConfigValue('shipping/dpdclassic/username').'/'.$this->getConfigValue('shipping/dpdclassic/accountnumber');
        $geoSession = "GEOSession: ".$this->getGeoSession()."";
        $contentLength = "Content-Length:".$dataLength."";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url );    
        curl_setopt($curl, CURLOPT_HTTPHEADER,array($host,$contentType,$accept,$geoClient,$geoSession,$contentLength));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //curl error SSL certificate problem, verify that the CA cert is OK
        $result     = curl_exec($curl);

        $response   = json_decode($result); 
	
        curl_close($curl);
        return $response;
    }

    public function createDpdShipmentRequest($shippingMethod){
        $request = Mage::app()->getRequest()->getPost();
        if($request['order_id']){
            $orderCollection = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToFilter('entity_id',$request['order_id']);
            $orderAddress = Mage::getModel('sales/order_address');
            foreach($orderCollection as $order){
                $billingAddress=$orderAddress->load($order->getBillingAddressId());

                $collectionDetails['name']=$billingAddress->getFirstname()." ".$billingAddress->getLastname();
                $collectionDetails['phone']=$billingAddress->getTelephone();
                $collectionDetails['company']=$billingAddress->getCompany();
                $collectionDetails['country']=$billingAddress->getCountryId();
                $collectionDetails['postcode']=$billingAddress->getPostcode();
                $collectionDetails['street']=implode($billingAddress->getStreet());
                $collectionDetails['region']=$billingAddress->getRegion();
                $collectionDetails['town']=$billingAddress->getCity();

                $shippingAddress=$orderAddress->load($order->getShippingAddressId());

                $deliveryDetails['name']=$shippingAddress->getFirstname()." ".$shippingAddress->getLastname();
                $deliveryDetails['phone']=$shippingAddress->getTelephone();
                $deliveryDetails['company']=$shippingAddress->getCompany();
                $deliveryDetails['country']=$shippingAddress->getCountryId();
                $deliveryDetails['postcode']=$shippingAddress->getPostcode();
                $deliveryDetails['street']=implode($shippingAddress->getStreet());
                $deliveryDetails['region']=$shippingAddress->getRegion();
                $deliveryDetails['town']=$shippingAddress->getCity();
                $mobilePhone=$shippingAddress->getTelephone();
                if($shippingAddress->getMobileNumber()){
                    $mobilePhone=$shippingAddress->getMobileNumber();
                }
                $deliveryDetails['phone']=$mobilePhone;
                $deliveryDetails['email']=$shippingAddress->getEmail();
            }
        }

        $data = array();
        $data['jobId'] = null;
        $data['collectionOnDelivery'] = ($request['collection_on_delivery'] == '1')?true:false;
        $data['invoice'] = null;
        $data['collectionDate'] = $request['collection_date'];
        $data['consolidate'] = false;

        $dataConsignment['consignmentNumber'] = null;
        $dataConsignment['consignmentRef'] = null;
        $dataConsignment['parcels'] = array();
        $dataConsignment['collectionDetails']['contactDetails']['contactName'] = $collectionDetails['name'];
        $dataConsignment['collectionDetails']['contactDetails']['telephone'] = $collectionDetails['phone'];
        $dataConsignment['collectionDetails']['address']['organisation'] = $collectionDetails['company'];
        $dataConsignment['collectionDetails']['address']['countryCode'] = $collectionDetails['country'];
        $dataConsignment['collectionDetails']['address']['postcode'] = $collectionDetails['postcode'];
        $dataConsignment['collectionDetails']['address']['street'] = $collectionDetails['street'];
        $dataConsignment['collectionDetails']['address']['locality'] = "";
        $dataConsignment['collectionDetails']['address']['town'] = $collectionDetails['town'];
        $dataConsignment['collectionDetails']['address']['county'] = $collectionDetails['region'];
        $dataConsignment['deliveryDetails']['contactDetails']['contactName'] = $deliveryDetails['name'];
        $dataConsignment['deliveryDetails']['contactDetails']['telephone'] = $deliveryDetails['phone'];
        $dataConsignment['deliveryDetails']['address']['organisation'] = $deliveryDetails['company'];
        $dataConsignment['deliveryDetails']['address']['countryCode'] = $deliveryDetails['country'];
        $dataConsignment['deliveryDetails']['address']['postcode'] = $deliveryDetails['postcode'];
        $dataConsignment['deliveryDetails']['address']['street'] = $deliveryDetails['street'];
        $dataConsignment['deliveryDetails']['address']['locality'] = "";
        $dataConsignment['deliveryDetails']['address']['town'] = $deliveryDetails['town'];
        $dataConsignment['deliveryDetails']['address']['county'] = $deliveryDetails['region'];
        $dataConsignment['deliveryDetails']['notificationDetails']['mobile'] = $deliveryDetails['phone'];
        $dataConsignment['deliveryDetails']['notificationDetails']['email'] = $deliveryDetails['email'];
        $dataConsignment['networkCode'] = $shippingMethod;
        $dataConsignment['numberOfParcels'] = number_format($request['total_parcel']);
        $dataConsignment['totalWeight'] = number_format($request['total_weight']);
        $dataConsignment['shippingRef1'] = $request['shippingref1'];
        $dataConsignment['shippingRef2'] = $request['shippingref2'];
        $dataConsignment['shippingRef3'] = $request['shippingref3'];
        $dataConsignment['customsValue'] = null;
        $dataConsignment['deliveryInstructions'] = $request['delivery_instruction'];
        $dataConsignment['parcelDescription'] = "";
        $dataConsignment['liabilityValue'] = 0;
        $dataConsignment['liability'] = false;
        $data['consignment'] = array($dataConsignment);
        return $data;
    }
}
