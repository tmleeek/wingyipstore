<?php

class SF9_Realex_Block_Adminhtml_Realex_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('realexGrid');
      //$this->setDefaultSort('order_date');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('realex/realex')->getCollection();
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

//    $this->addColumn('realex_id', array(
//          'header'    => Mage::helper('realex')->__('ID'),
//          'align'     =>'right',
//          'width'     => '50px',
//          'index'     => 'realex_id',
//     ));

      $this->addColumn('order_id', array(
          'header'    => Mage::helper('realex')->__('Order ID'),
          'index'     => 'order_id',
     ));

      $this->addColumn('timestamp', array(
          'header'    => Mage::helper('realex')->__('Timestamp'),
          'type'        => 'datetime',
          'index'     => 'timestamp',
     ));

//      $this->addColumn('merchantid', array(
//          'header'    => Mage::helper('realex')->__('Merchant ID'),
//          'index'     => 'merchantid',
//     ));
//
//      $this->addColumn('account', array(
//          'header'    => Mage::helper('realex')->__('Account'),
//          'index'     => 'account',
//     ));

      $this->addColumn('authcode', array(
          'header'    => Mage::helper('realex')->__('Authcode'),
          'index'     => 'authcode',
     ));

      $this->addColumn('result', array(
          'header'    => Mage::helper('realex')->__('Result'),
          'index'     => 'result',
          'width'     => '50px',

     ));

      $this->addColumn('message', array(
          'header'    => Mage::helper('realex')->__('Message'),
          'index'     => 'message',
     ));

      $this->addColumn('pasref', array(
          'header'    => Mage::helper('realex')->__('PasRef'),
          'index'     => 'pasref',
     ));

      $this->addColumn('cvnresult', array(
          'header'    => Mage::helper('realex')->__('CVN Result'),
          'index'     => 'cvnresult',
          'width'     => '50px',
     ));

      $this->addColumn('batchid', array(
          'header'    => Mage::helper('realex')->__('Batch ID'),
          'index'     => 'batchid',
     ));

      $this->addColumn('card_issuer_bank', array(
          'header'    => Mage::helper('realex')->__('Bank'),
          'index'     => 'card_issuer_bank',
     ));

      $this->addColumn('card_issuer_country', array(
          'header'    => Mage::helper('realex')->__('Country'),
          'index'     => 'card_issuer_country',
     ));

      $this->addColumn('tss_result', array(
          'header'    => Mage::helper('realex')->__('TSS Result'),
          'index'     => 'tss_result',
     ));

      $this->addColumn('avspostcoderesponse', array(
          'header'    => Mage::helper('realex')->__('AVS Postcode'),
          'index'     => 'avspostcoderesponse',
          'width'     => '50px',
     ));

      $this->addColumn('avsaddressresponse', array(
          'header'    => Mage::helper('realex')->__('AVS Address'),
          'index'     => 'avsaddressresponse',
          'width'     => '50px',
     ));

      $this->addColumn('timetaken', array(
          'header'    => Mage::helper('realex')->__('Time Taken'),
          'index'     => 'timetaken',
          'width'     => '50px',
     ));

      $this->addColumn('authtimetaken', array(
          'header'    => Mage::helper('realex')->__('Auth Time Taken'),
          'index'     => 'authtimetaken',
          'width'     => '50px',
     ));
	  
//      $this->addColumn('action',
//            array(
//                'header'    =>  Mage::helper('realex')->__('Action'),
//                'width'     => '100',
//                'type'      => 'action',
//                'getter'    => 'getId',
//                'actions'   => array(
//                    array(
//                        'caption'   => Mage::helper('realex')->__('Edit'),
//                        'url'       => array('base'=> '*/*/edit'),
//                        'field'     => 'id'
//                    )
//                ),
//                'filter'    => false,
//                'sortable'  => false,
//                'index'     => 'stores',
//                'is_system' => true,
//        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('realex')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('realex')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('realex_id');
        $this->getMassactionBlock()->setFormFieldName('realex');

//        $this->getMassactionBlock()->addItem('delete', array(
//             'label'    => Mage::helper('realex')->__('Delete'),
//             'url'      => $this->getUrl('*/*/massDelete'),
//             'confirm'  => Mage::helper('realex')->__('Are you sure?')
//        ));

//        $statuses = Mage::getSingleton('realex/status')->getOptionArray();
//
//        array_unshift($statuses, array('label'=>'', 'value'=>''));
//        $this->getMassactionBlock()->addItem('status', array(
//             'label'=> Mage::helper('realex')->__('Change status'),
//             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
//             'additional' => array(
//                    'visibility' => array(
//                         'name' => 'status',
//                         'type' => 'select',
//                         'class' => 'required-entry',
//                         'label' => Mage::helper('realex')->__('Status'),
//                         'values' => $statuses
//                     )
//             )
//        ));
        
        return $this;
    }

  public function getRowUrl($row)
  {
      return false;
  }

}