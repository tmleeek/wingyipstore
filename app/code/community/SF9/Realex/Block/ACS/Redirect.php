<?php

class SF9_Realex_Block_ACS_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
    	$orderid = Mage::getSingleton('checkout/session')->getLastRealOrderId();
    	$order = Mage::getModel('sales/order')->loadByIncrementId($orderid);
    	$additionaldata = unserialize($order->getPayment()->getAdditionalData());

    	$url = $additionaldata['url'];
    	$pareq = $additionaldata['pareq'];
    	$termurl = Mage::getUrl('realex/ACS/verifysig/', array('_secure' => true));
    	$md = $additionaldata['md'];

        $form = new Varien_Data_Form();
        $form->setAction($url)
            ->setId('realex_acs')
            ->setName('realex_acs')
            ->setMethod('POST')
            ->setUseContainer(true);

        $form->addField('PaReq', 'hidden', array('name'=>'PaReq', 'value'=>$pareq));
        $form->addField('TermUrl', 'hidden', array('name'=>'TermUrl', 'value'=>$termurl));
        $form->addField('MD', 'hidden', array('name'=>'MD', 'value'=>$md));
        $html = '<html><body>';
        $html.= $this->__('You will be redirected to 3D Secure in a few seconds.');
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.observe("dom:loaded", function(){document.getElementById("realex_acs").submit();});</script>';
        $html.= '</body></html>';

        if(Mage::getModel('realex/remote')->getConfigData('debug')){
	        Mage::log($html);
        }

        return $html;
    }
}