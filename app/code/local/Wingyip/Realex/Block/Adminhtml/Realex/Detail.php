<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml transaction detail
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Wingyip_Realex_Block_Adminhtml_Realex_Detail extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Transaction model
     *
     * @var Mage_Sales_Model_Order_Payment_Transaction
     */
    protected $_txn;

    /**
     * Add control buttons
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->_txn = Mage::registry('realex_data');

        $backUrl = ($this->_txn->getOrderUrl()) ? $this->_txn->getOrderUrl() : $this->getUrl('*/*/');
        $this->_addButton('back', array(
            'label'   => Mage::helper('realex')->__('Back'),
            'onclick' => "setLocation('{$backUrl}')",
            'class'   => 'back'
        ));
    }

    /**
     * Retrieve header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('sales')->__("Realex Transaction # %s | %s", $this->_txn->getRealexId(), $this->formatDate($this->_txn->getTimestamp(), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true));
    }

    protected function _toHtml()
    {
    
        $this->setTxnIdHtml($this->escapeHtml($this->_txn->getRealexId()));

        
        $this->setParentTxnIdUrlHtml(
            $this->escapeHtml($this->getUrl('*/sales_transactions/view', array('txn_id' => $this->_txn->getParentId())))
        );

        $this->setParentTxnIdHtml(
            $this->escapeHtml($this->_txn->getParentTxnId())
        );

        //$order = Mage::getModel('sales/order')->load($this->_txn->getOrderId());
    
        $orderData = $this->render($this->_txn->getOrderId());
        
        
        $Incrementid = $orderData['Incrementid'];//$order->getIncrementId();
        if(!$Incrementid)
            $Incrementid = $this->_txn->getOrderId();
        
        $this->setOrderIncrementIdHtml($this->escapeHtml($Incrementid));

        $this->setTxnTypeHtml($this->escapeHtml($this->_txn->getAccount()));
        $this->setResultHtml($this->escapeHtml($this->_txn->getResult()));
        $this->setMessageHtml($this->escapeHtml($this->_txn->getMessage()));
        $this->setEciHtml($this->escapeHtml($this->_txn->getEci()));
        $this->setMerchantHtml($this->escapeHtml($this->_txn->getMerchantid()));
        $this->setTssResultHtml($this->escapeHtml($this->_txn->getTssResult()));
        $this->setBatchidHtml($this->escapeHtml($this->_txn->getBatchid()));
        
        $this->setOrderIdUrlHtml(
            $this->escapeHtml($this->getUrl('./admin/sales_order/view/', array('order_id' =>$orderData['orderId'])))
        );

        $this->setIsClosedHtml(
            ($this->_txn->getResult()) ? Mage::helper('sales')->__('Yes') : Mage::helper('sales')->__('No')
        );

        $createdAt = (strtotime($this->_txn->getTimestamp()))
            ? $this->formatDate($this->_txn->getTimestamp(), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true)
            : $this->__('N/A');
        $this->setCreatedAtHtml($this->escapeHtml($createdAt));
        return parent::_toHtml();
    }
    
    
    public function render($row){
        $quoteid = $row;

        //$order = Mage::getModel('sales/order')->load($quoteid);
        $order = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('quote_id',$quoteid)->getFirstItem();
        
        $order = Mage::getModel('sales/order')->load($order->getId());
        $Incrementid = $order->getIncrementId();

        if(!is_numeric($Incrementid)){
            $Incrementid = $quoteid;
        }
        if($order->getId())
            $orderId = $order->getId();
        else
            $orderId = $quoteid;
            
            
        return array("Incrementid"=>"# ".$Incrementid,"orderId"=>$orderId);

    }
    
}
