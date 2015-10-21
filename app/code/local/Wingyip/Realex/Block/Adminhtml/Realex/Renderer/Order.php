<?php

class Wingyip_Realex_Block_Adminhtml_Realex_Renderer_Order extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract

{

	public function render(Varien_Object $row)

	{

		$quoteid = $row->getData($this->getColumn()->getIndex());

        //$order = Mage::getModel('sales/order')->load($quoteid);
        $order = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('quote_id',$quoteid)->getFirstItem();
        
    	$order = Mage::getModel('sales/order')->load($order->getId());
        $Incrementid = $order->getIncrementId();

        if(!is_numeric($Incrementid)){
            $Incrementid = $quoteid;
        }

        return $Incrementid;

	}

}

?>