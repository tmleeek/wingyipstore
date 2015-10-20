<?php

class Wingyip_Shipping_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
     */
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

    public function shipAction()
    {
        if ($order = $this->_initOrder()) {
            $shippingMethod = Mage::getModel('shippingwing/shipping')->_getShippingMethod($order->getShippingDescription());
            if (isset($shippingMethod) && $shippingMethod != " ") {
                $this->_forward(strtolower($shippingMethod));
            }
        }
    }

    public function dpdAction()
    {
        $this->setDeliveryRegistry();
        $this->setSenderRegistry();
        $this->setFormRegistry();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function setFormRegistry()
    {
        if ($order = $this->_initOrder()) {

            $orderIncrementId = $order->getIncrementId();
            $orderText = " ";
            if ($orderIncrementId) {
                $orderText = "Order:" . $orderIncrementId;
            }


            $formData = new Varien_Object();
            $formData
                ->setCollectionOnDelivery(2)
                ->setTotalParcel(1)
                ->setTotalWeight($order->getWeight() / 1000)
                ->setShippingref1($orderText)
                ->setShippingref2($orderText);

            Mage::register('form_data', $formData);
            return;
        }
    }

    public function setDeliveryRegistry()
    {
        if ($order = $this->_initOrder()) {
            $shippingObj = $order->getShippingAddress();

            $deliveryCollection = new Varien_Object();
            $deliveryCollection
                ->setDeliveryName($shippingObj->getPrefix() . ' ' . $shippingObj->getFirstname() . ' ' . $shippingObj->getLastname())
                ->setDeliveryPhone($shippingObj->getTelephone())
                ->setDeliveryEmail($shippingObj->getEmail())
                ->setDeliveryCompany(substr($shippingObj->getCompany(), 0, 35))
                ->setDeliveryStreet(substr(implode(',', $shippingObj->getStreet()), 0, 35))
                ->setDeliveryTown($shippingObj->getCity())
                ->setDeliveryPostcode($shippingObj->getPostcode())
                ->setDeliveryCountry($shippingObj->getCountryId());

            Mage::register('delivery_data', $deliveryCollection);
            return;
        }
    }

    public function setSenderRegistry()
    {
        // $this->generateShippingLabel();

        $senderCollection = new Varien_Object();
        $senderCollection
            ->setSenderName($this->getConfigValue('shipping/dpdclassic/sender_name'))
            ->setSenderPhone($this->getConfigValue('shipping/dpdclassic/sender_number'))
            ->setSenderStreet($this->getConfigValue('shipping/dpdclassic/sender_street'))
            ->setSenderTown($this->getConfigValue('shipping/dpdclassic/sender_city'))
            ->setSenderPostcode($this->getConfigValue('shipping/dpdclassic/sender_zipcode'))
            ->setSenderCountry($this->getConfigValue('shipping/dpdclassic/sender_country'));

        Mage::register('sender_data', $senderCollection);
        return;

    }

    public function upsAction()
    {
        echo 'ups';
        exit;
    }

    public function freeAction()
    {
        echo 'free';
        exit;
    }

    public function getConfigValue($valuePath)
    {
        return Mage::getStoreConfig($valuePath);
    }

    public function insertShipmentAction()
    {
        $order = $this->_initOrder();
        if ($order) {
            $digitCodePdp = Mage::helper('shippingwing')->getdigitCodeDpd($order->getShippingMethod());
        }
        $response = Mage::getModel('shippingwing/shipping')->doDpdShipmentRequest($digitCodePdp);

        if (!$response->error) {
            $dpdShipmentId = $response->data->shipmentId;
            $consignmateDetail = $response->data->consignmentDetail;
            if ($consignmateDetail) {
                $consignmentNumber = $consignmateDetail[0]->parcelNumbers[0];
            }
            if ($dpdShipmentId) {
                $order->setShipperShipmentid($dpdShipmentId);
            }
            if ($consignmentNumber) {
                $order->setConsignmentId($consignmentNumber);
            }

            $order->save();
            $response = array(
                'success' => true,
                'consignmentNumber' => $consignmentNumber,
                'dpdShipmentId' => $dpdShipmentId
            );
        } else {
            $errors = $response->error[0];
            if($errors){
                Mage::getModel('core/session')->addError($errors->errorType.": ".$errors->errorMessage." ".$errors->obj);
                $response=array(
                    'success'=>false
                );
            }
        }

        $jsonData = json_encode($response);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }


    public function generatelabelAction()
    {
        if ($order = $this->_initOrder()) {
            $shipmentId = $order->getShipperShipmentid();
        }
        if ($shipmentId) {
            Mage::register('shipment_id', $shipmentId);
        }
        $this->loadLayout();
        $this->renderLayout();
    }


}