<?php

class Ecommage_Autoinvoice_Model_System_Config_Source_OrderStatus
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $_helper = Mage::helper('sales');
        return array(
            array('value' => Mage_Sales_Model_Order::STATE_NEW, 'label'=> $_helper->__('New')),
            array('value' => Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, 'label'=> $_helper->__('Payment Review')),
            array('value' => Mage_Sales_Model_Order::STATE_PROCESSING, 'label'=> $_helper->__('Processing')),
            array('value' => Mage_Sales_Model_Order::STATE_COMPLETE, 'label'=> $_helper->__('Complete')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $values = array();
        foreach($this->toOptionArray() as $data){
            $values[$data['value']] = $data['label'];
        }

        return $values;
    }
}