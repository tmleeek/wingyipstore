<?php

class Wingyip_Irishosted_Block_Standard_Redirect extends Mage_Core_Block_Abstract
{

	protected function _toHtml()
    {
        $irishosted = Mage::getModel('irishosted/irishosted');
        $_helper = Mage::helper('irishosted');


        $form = new Varien_Data_Form();
        $form->setAction($_helper->getRedirectURL())
            ->setId('irishosted_standard_checkout')
            ->setName('irishosted_standard_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);
        foreach ($irishosted->getCheckoutRequestFormFields() as $field=>$value) {
            $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
        }
        $idSuffix = Mage::helper('core')->uniqHash();
        $submitButton = new Varien_Data_Form_Element_Submit(array(
            'value'    => $this->__('Click here if you are not redirected within 10 seconds...'),
        ));
        $id = "submit_to_irishosted_button_{$idSuffix}";
        $submitButton->setId($id);
        $form->addElement($submitButton);
        $html = '<html><body>';
        $html.= $this->__('You will be redirected to the PayPal website in a few seconds.');
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("irishosted_standard_checkout").submit();</script>';
        $html.= '</body></html>';
		
		Mage::getModel('irishosted/irishosted')->debugLog($html);
		
        return $html;
    }
}
