<?php

class Wingyip_Shipping_IndexController extends Mage_Core_Controller_Front_Action
{
    public function accesspointAction()
    {

        $request = $this->getRequest()->getParams();
        $quoteId = Mage::getModel('checkout/session')->getQuoteId();
        $quoteAddressObj = Mage::getModel('sales/quote_address')->getCollection()->addFieldToFilter('quote_id', $quoteId)->addFieldToFilter('address_type', 'shipping')->getFirstItem();
        if (isset($request['action'])) {
            $quoteAddressObj->setCompany($request['company'])
                ->setCity($request['city'])
                ->setPostcode($request['zip'])
                ->setStreet(array(
                    0 => $request['street1'],
                    1 => $request['street2']
                ));
            $quoteAddressObj->save();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(true));

        } else {
            $quoteAddressObj->setCompany($request['APid'])
                ->setCity($request['city'])
                ->setPostcode($request['zip'])
                ->setStreet($request['street'] . ' ' . $request['name']);
            $quoteAddressObj->save();
            $this->loadLayout();
            $this->renderLayout();
        }


    }

}
