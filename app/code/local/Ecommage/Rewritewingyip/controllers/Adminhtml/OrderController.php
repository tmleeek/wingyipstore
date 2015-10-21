<?php
require_once 'Wingyip/Shipping/controllers/Adminhtml/OrderController.php';

class Ecommage_Rewritewingyip_Adminhtml_OrderController extends Wingyip_Shipping_Adminhtml_OrderController
{
    public function shipAction()
    {
        if ($order = $this->_initOrder()) {
            $shippingMethod = Mage::getModel('shippingwing/shipping')->_getShippingMethod($order->getShippingDescription());
            if (isset($shippingMethod) && $shippingMethod != " ") {
                $this->_forward(strtolower($shippingMethod));
            }
        }
    }


    public function upsAction()
    {
        $this->setPackage();
        $this->loadLayout();
        $this->renderLayout();
    }


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

        if($this->getConfigValue('shipping/upsshipping/shippernumber') !=null){
            $shippingNumber = $this->getConfigValue('shipping/upsshipping/shippernumber');
        }

        $packageCollection
            ->setCompanyorname($delivetyStreet[0])
            ->setAttention($billlingObj->getFirstname().' '.$billlingObj->getLastname())
            ->setTelephone($this->getConfigValue('shipping/upsshipping/telephone'))
            ->setAddress1($this->getConfigValue('shipping/upsshipping/address').$shippingObj->getCompany())
            ->setAddress2($delivetyStreet[0])
            ->setCountryterritory($shippingObj->getCountryId())
            ->setPostcode($shippingObj->getPostcode())
            ->setCityortown($shippingObj->getCity())
            ->setLocalid($this->getConfigValue('shipping/upsshipping/localid').$shippingObj->getCompany())

            ->setServicetype($this->getConfigValue('shipping/upsshipping/servicetype'))
            ->setDescriptionofgoods($this->getConfigValue('shipping/upsshipping/descriptionofgoods'))
            ->setBilltransportationto($this->getConfigValue('shipping/upsshipping/billtransportationto'))
            ->setProfilename($this->getConfigValue('shipping/upsshipping/profilename'))
            ->setShippernumber($shippingNumber)

            ->setPackagetype($this->getConfigValue('shipping/upsshipping/packagetype'))
            ->setWeight($order->getWeight()/1000)
            ->setReference1($order->getIncrementId())
            ->setReference2($order->getCustomerId())
            ->setReference3($order->getCustomerEmail())
            ->setReference4($shippingObj->getTelephone())
//            AP +$Delivery.Company +GB +$Billing.Podtcode +$Billing.Street
            ->setReference5($reference5);
        Mage::register('package_data',$packageCollection);
    }

    public function exportUpsAction(){
        try {
            $order = $this->_initOrder();
            $data = $this->getRequest()->getParams();
            $formData = Mage::helper('rewritewingyip')->getbyOrderId($data['order_id']);
            if ($formData['ups_id']) {
                $updateData = Mage::getModel('rewritewingyip/ups')->load($formData['ups_id']);
                $updateData->addData($data);
                $updateData->save();
            } else {
                $insertData = Mage::getModel('rewritewingyip/ups');
                $insertData->setData($data);
                $insertData->save();
            }
            $getHelper = Mage::helper('rewritewingyip');
            $rerultXml = $getHelper->exportXml($data,$order->getIncrementId());
            if ($rerultXml) {
                $message = $this->__('Export xml file success.');
                Mage::getSingleton('core/session')->addSuccess($message);
                $result = $getHelper->sendFileToFtp($order->getIncrementId());
                //set status and state for this order
                if($result) {
                    $orderModel = Mage::getModel('sales/order')->load($data['order_id']);
                    $orderModel->setData('state', "complete");
                    $orderModel->setStatus("complete");
                    $orderModel->save();
                    echo true;
                }else{
                    echo 'false';
                }
            } else {
                $message = $this->__('Export xml file is failed.');
                Mage::getSingleton('core/session')->addError($message);
            }
        }catch (Exception $e){
            return Mage::getSingleton('core/session')->addError($e);
        }
    }
    public function saveUpsAction(){
        try {
            $order = $this->_initOrder();
            $data = $this->getRequest()->getParams();
            $formkey = Mage::getSingleton('core/session')->getFormKey();
            $result = Mage::helper('rewritewingyip')->sendFileToFtp($order->getIncrementId());
            //set status and state for this order
//            if($result) {
//                $orderModel = Mage::getModel('sales/order')->load($data['order_id']);
//                $orderModel->setData('state', "complete");
//                $orderModel->setStatus("complete");
//                $orderModel->save();
//            }

            return $this->_redirect('adminhtml/sales_order/view/order_id/'.$data['order_id'].'key/'.$formkey);
        }catch (Exception $e){
            return Mage::getSingleton('core/session')->addError($e);
        }

    }
}