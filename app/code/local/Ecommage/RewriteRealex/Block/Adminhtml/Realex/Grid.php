<?php

class Ecommage_RewriteRealex_Block_Adminhtml_Realex_Grid extends SF9_Realex_Block_Adminhtml_Realex_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('realexGrid');
        $this->setDefaultSort('timestamp');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }


    protected function _getIncrementId($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        $getCrementId = $order->getIncrementId();
        if ($getCrementId) {
            return $getCrementId;
        }
        return $orderId;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('realex/realex')->getCollection()->addFieldToFilter('timestamp', array("gteq" => '2014-12-15 12:20:07'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Return Current work store
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        return Mage::app()->getStore();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('order_id', array(
            'header' => Mage::helper('realex')->__('Order #'),
            'align' => 'left',
            'index' => 'order_id',
            'renderer' => 'Wingyip_Realex_Block_Adminhtml_Realex_Renderer_Order',
            'filter' => false,
            'sortable' => false,
        ));
        $this->addColumn('timestamp', array(
            'header' => Mage::helper('realex')->__('Timestamp'),
            'type' => 'datetime',
            'index' => 'timestamp',
        ));
        $this->addColumn('authcode', array(
            'header' => Mage::helper('realex')->__('Authcode'),
            'index' => 'authcode',
        ));

        $this->addColumn('result', array(
            'header' => Mage::helper('realex')->__('Result'),
            'index' => 'result',
            'width' => '50px',

        ));

        $this->addColumn('message', array(
            'header' => Mage::helper('realex')->__('Message'),
            'index' => 'message',
        ));

        $this->addColumn('pasref', array(
            'header' => Mage::helper('realex')->__('PasRef'),
            'index' => 'pasref',
        ));

        $this->addColumn('cvnresult', array(
            'header' => Mage::helper('realex')->__('CVN Result'),
            'index' => 'cvnresult',
            'width' => '50px',
        ));

        $this->addColumn('batchid', array(
            'header' => Mage::helper('realex')->__('Batch ID'),
            'index' => 'batchid',
        ));
        $this->addColumn('tss_result', array(
            'header' => Mage::helper('realex')->__('TSS Result'),
            'index' => 'tss_result',
        ));

        $this->addColumn('avspostcoderesponse', array(
            'header' => Mage::helper('realex')->__('AVS Postcode'),
            'index' => 'avspostcoderesponse',
            'width' => '50px',
        ));

        $this->addColumn('avsaddressresponse', array(
            'header' => Mage::helper('realex')->__('AVS Address'),
            'index' => 'avsaddressresponse',
            'width' => '50px',
        ));

        $this->addColumn('timetaken', array(
            'header' => Mage::helper('realex')->__('Time Taken'),
            'index' => 'timetaken',
            'width' => '50px',
        ));

        $this->addColumn('authtimetaken', array(
            'header' => Mage::helper('realex')->__('Auth Time Taken'),
            'index' => 'authtimetaken',
            'width' => '50px',
        ));
        $this->addExportType('*/*/exportCsv', Mage::helper('realex')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('realex')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('realex_id');
        $this->getMassactionBlock()->setFormFieldName('realex');
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }
}