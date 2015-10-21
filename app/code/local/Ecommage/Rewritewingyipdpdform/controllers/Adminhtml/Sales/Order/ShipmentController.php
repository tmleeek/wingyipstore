<?php
require_once 'Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php';
class Ecommage_Rewritewingyipdpdform_Adminhtml_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController
{
    // Rewrite again Action New
    public function newAction()
    {
        if ($shipment = $this->_initShipment()) {
            $this->_title($this->__('New Shipment'));

            $comment = Mage::getSingleton('adminhtml/session')->getCommentText(true);
            if ($comment) {
                $shipment->setCommentText($comment);
            }
//            $this->setDeliveryRegistry();
//            $this->setSenderRegistry();
            $this->setPackage();
            $this->setFormRegistry();
            $this->loadLayout()
                ->_setActiveMenu('sales/order')
                ->renderLayout();
        } else {
            $this->_redirect('*/sales_order/view', array('order_id'=>$this->getRequest()->getParam('order_id')));
        }

    }

    // Auto Provider data for form DPD

    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        return $order;
    }


    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $shipment = $this->_initShipment();
            if (!$shipment) {
                $this->_forward('noRoute');
                return;
            }
            /**
             * Add tracking when api DPD
             */

            $shippingMethod = Mage::getModel('shippingwing/shipping')->_getShippingMethod($shipment->getOrder()->getShippingDescription());
            if(strtolower($shippingMethod) == "dpd"){
                $shipmentCarrierCode = 'dpdparcelshops';
                $shipmentCarrierTitle = 'DPD ParcelShops';
            }elseif(strtolower($shippingMethod) == "ups"){
                $shipmentCarrierCode = 'ups';
                $shipmentCarrierTitle = 'United Parcel Service';
            }

            $arrTracking = array(
                'carrier_code' => isset($shipmentCarrierCode) ? $shipmentCarrierCode : $shipment->getShippingCarrier()->getCarrierCode(),
                'title' => isset($shipmentCarrierTitle) ? $shipmentCarrierTitle : $shipment->getShippingCarrier()->getConfigData('title'),
                'number' => $shipment->getOrder()->getConsignmentId(),
            );

            $track = Mage::getModel('sales/order_shipment_track')->addData($arrTracking);
            $shipment->addTrack($track);


            $shipment->register();
            $comment = '';
            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
            }

            if (!empty($data['send_email'])) {
                $shipment->setEmailSent(true);
            }

            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $responseAjax = new Varien_Object();
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

            if ($isNeedCreateLabel && $this->_createShippingLabel($shipment)) {
                $responseAjax->setOk(true);
            }

            $this->_saveShipment($shipment);

            $shipment->sendEmail(!empty($data['send_email']), $comment);

            $shipmentCreatedMessage = $this->__('The shipment has been created.');
            $labelCreatedMessage    = $this->__('The shipping label has been created.');

            $this->_getSession()->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
                : $shipmentCreatedMessage);
            Mage::getSingleton('adminhtml/session')->getCommentText(true);
        } catch (Mage_Core_Exception $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(
                    Mage::helper('sales')->__('An error occurred while creating shipping label.'));
            } else {
                $this->_getSession()->addError($this->__('Cannot save shipment.'));
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }

        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->setBody($responseAjax->toJson());
        } else {
            $this->_redirect('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
        }
    }

    public function setFormRegistry(){
        if ($order = $this->_initOrder()) {

            $orderIncrementId = $order->getIncrementId();
            $orderText = " ";
            if($orderIncrementId){
                $orderText = "Order:".$orderIncrementId;
            }


            $formData =  new Varien_Object();
            $formData
                ->setCollectionOnDelivery(2)
                ->setTotalParcel(1)
                ->setTotalWeight($order->getWeight()/1000)
                ->setShippingref1($orderText)
                ->setShippingref2($orderText);

            Mage::register('form_data',$formData);
            return;
        }
    }

    public function setDeliveryRegistry(){
        if ($order = $this->_initOrder()) {
            $shippingObj = $order->getShippingAddress();

            $deliveryCollection =  new Varien_Object();
            $deliveryCollection
                ->setDeliveryName($shippingObj->getPrefix().' '.$shippingObj->getFirstname().' '.$shippingObj->getLastname())
                ->setDeliveryPhone($shippingObj->getTelephone())
                ->setDeliveryEmail($shippingObj->getEmail())
                ->setDeliveryCompany(substr($shippingObj->getCompany(),0,35))
                ->setDeliveryStreet(substr(implode(',',$shippingObj->getStreet()),0,35) )
                ->setDeliveryTown($shippingObj->getCity())
                ->setDeliveryPostcode($shippingObj->getPostcode())
                ->setDeliveryCountry($shippingObj->getCountryId());

            Mage::register('delivery_data',$deliveryCollection);
            return;
        }
    }

    public function setSenderRegistry(){
        $senderCollection =  new Varien_Object();
        $senderCollection
            ->setSenderName(Mage::getStoreConfig('shipping/dpdclassic/sender_name'))
            ->setSenderPhone(Mage::getStoreConfig('shipping/dpdclassic/sender_number'))
            ->setSenderStreet(Mage::getStoreConfig('shipping/dpdclassic/sender_street'))
            ->setSenderTown(Mage::getStoreConfig('shipping/dpdclassic/sender_city'))
            ->setSenderPostcode(Mage::getStoreConfig('shipping/dpdclassic/sender_zipcode'))
            ->setSenderCountry(Mage::getStoreConfig('shipping/dpdclassic/sender_country'));

        Mage::register('sender_data',$senderCollection);
        return;

    }

    // Auto Provider for Form UPS

    public function setPackage(){
        $order = $this->_initOrder();
        $shippingObj = $order->getShippingAddress();
        $billlingObj = $order->getBillingAddress();

        $packageCollection =  new Varien_Object();
        $commpany = $shippingObj->getCompany();
        $postcode = $billlingObj->getPostcode();
        $streetBilling = $billlingObj->getStreet();
        $streetShipping = $shippingObj->getStreet();
        $reference5 = 'AP';
        if(!empty($commpany)){
            $reference5 .= ' +'.$commpany;
        }

        if (!empty($postcode)) {
            $reference5 .= ' +GB +'.$postcode;
        }
        if (!empty($streetBilling)) {
            $reference5 .= ' +'.$streetBilling[0];
        }
        $delivetyStreet = $shippingObj->getStreet();
        $shippingNumber = '8422E3';

        if(Mage::getStoreConfig('shipping/upsshipping/shippernumber') !=null){
            $shippingNumber = Mage::getStoreConfig('shipping/upsshipping/shippernumber');
        }

        $packageCollection
            ->setCompanyorname($delivetyStreet[0])
            ->setAttention($billlingObj->getFirstname().' '.$billlingObj->getLastname())
            ->setTelephone(Mage::getStoreConfig('shipping/upsshipping/telephone'))
            ->setAddress1(Mage::getStoreConfig('shipping/upsshipping/address').$shippingObj->getCompany())
            ->setAddress2($delivetyStreet[0])
            ->setCountryterritory($shippingObj->getCountryId())
            ->setPostcode($shippingObj->getPostcode())
            ->setCityortown($shippingObj->getCity())
            ->setLocalid(Mage::getStoreConfig('shipping/upsshipping/localid').$shippingObj->getCompany())

            ->setServicetype(Mage::getStoreConfig('shipping/upsshipping/servicetype'))
            ->setDescriptionofgoods(Mage::getStoreConfig('shipping/upsshipping/descriptionofgoods'))
            ->setBilltransportationto(Mage::getStoreConfig('shipping/upsshipping/billtransportationto'))
            ->setProfilename(Mage::getStoreConfig('shipping/upsshipping/profilename'))
            ->setShippernumber($shippingNumber)

            ->setPackagetype(Mage::getStoreConfig('shipping/upsshipping/packagetype'))
            ->setWeight($order->getWeight()/1000)
            ->setReference1($order->getIncrementId())
            ->setReference2($order->getCustomerId())
            ->setReference3($order->getCustomerEmail())
            ->setReference4($shippingObj->getTelephone())
//            AP +$Delivery.Company +GB +$Billing.Podtcode +$Billing.Street
            ->setReference5($reference5);
        Mage::register('package_data',$packageCollection);
    }

}