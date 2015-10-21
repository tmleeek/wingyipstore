<?php

class Wingyip_Irishosted_Block_Form extends Mage_Payment_Block_Form
{
    /**
     * Payment method code
     * @var string
     */
    protected $_methodCode = 'irishosted';

    /**
     * Set template and redirect message
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('irishosted/form.phtml');
    }

}
