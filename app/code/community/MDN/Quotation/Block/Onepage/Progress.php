<?php
 
class MDN_Quotation_Block_Onepage_Progress extends Mage_Checkout_Block_Onepage_Progress
{
    protected function _getStepCodes()
    {
        //return array('login', 'billing', 'shipping', 'shipping_method', 'payment', 'review');
        return array('login', 'payment', 'review');
    }
}