<?php

class Wingyip_Importproducts_Block_Adminhtml_Importproducts_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('importGrid');
        $this->setDefaultSort('importproducts_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('importproducts/importproducts')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('importproducts_id', array(
            'header'    => Mage::helper('importproducts')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'importproducts_id',
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('importproducts')->__('Title'),
            'align'     =>'left',
            'index'     => 'title',
        ));

        $this->addColumn('filename',
            array(
                'header'    =>  Mage::helper('importproducts')->__('Csv File'),
                'width'     => '300',
                'type'      => 'csvfile',
                'renderer' =>  'Wingyip_Importproducts_Block_Adminhtml_Importproducts_Renderer_Csvlink',
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'filename',
                'is_system' => true,
            ));

        /*$this->addColumn('loggile',
      array(
      'header'    =>  Mage::helper('importproducts')->__('Log File'),
      'width'     => '300',
      'type'      => 'csvfile',
      'renderer' =>  'Wingyip_Importproducts_Block_Adminhtml_Importproducts_Renderer_Loglink',
      'filter'    => false,
      'sortable'  => false,
      ));

    $this->addColumn('loggile',
      array(
      'header'    =>  Mage::helper('importproducts')->__('Log File'),
      'width'     => '300',
      'type'      => 'csvfile',
      'renderer' =>  'Wingyip_Importproducts_Block_Adminhtml_Importproducts_Renderer_Loglink',
      'filter'    => false,
      'sortable'  => false,
      ));*/

        $this->addColumn('start_time',
            array(
                'header'    =>  Mage::helper('importproducts')->__('Created Time'),
                'width'     => '300',
                'type'      => 'text',
                'index'     => 'created_time',
                'filter'    => false,
                'sortable'  => false,
            ));

        $this->addColumn('end_time',
            array(
                'header'    =>  Mage::helper('importproducts')->__('Updated Time'),
                'width'     => '300',
                'type'      => 'text',
                'index'     => 'update_time',
                'filter'    => false,
                'sortable'  => false,
            ));

        /*$this->addColumn('runcsv',
        array(
        'header'    =>  Mage::helper('groupimport')->__('Run Import'),
        'width'     => '300',
        'type'      => 'csvfile',
        'renderer' =>  'Cws_Groupimport_Block_Adminhtml_Groupimport_Renderer_Runcsv',
        'filter'    => false,
        'sortable'  => false,
        ));*/

        /*
        $this->addColumn('content', array(
        'header'    => Mage::helper('groupimport')->__('Item Content'),
        'width'     => '150px',
        'index'     => 'content',
        ));
        */

        $this->addColumn('status', array(
            'header'    => Mage::helper('importproducts')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                1 => Mage::helper('importproducts')->__('Pending'),
                2 => Mage::helper('importproducts')->__('Processing'),
                3 => Mage::helper('importproducts')->__('Success'),
                4 => Mage::helper('importproducts')->__('Fail'),
            ),
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('importproducts')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('importproducts')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));
        /*$this->addColumn('import',
        array(
        'header'    =>  Mage::helper('importproducts')->__('import'),
        'width'     => '100',
        'type'      => 'action',
        'getter'    => 'getId',
        'actions'   => array(
        array(
        'caption'   => Mage::helper('importproducts')->__('Import'),
        'url'       => array('base'=> '//startimport'),
        'field'     => 'id'
        )
        ),
        'filter'    => false,
        'sortable'  => false,
        'index'     => 'stores',
        'is_system' => true,
        ));*/

        // $this->addExportType('*/*/exportCsv', Mage::helper('importproducts')->__('CSV'));
        // $this->addExportType('*/*/exportXml', Mage::helper('importproducts')->__('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('importproducts_id');
        $this->getMassactionBlock()->setFormFieldName('importproducts');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('importproducts')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('importproducts')->__('Are you sure?')
        ));

        /* $statuses = Mage::getSingleton('importproducts/status')->getOptionArray();

         array_unshift($statuses, array('label'=>'', 'value'=>''));
         $this->getMassactionBlock()->addItem('status', array(
         'label'=> Mage::helper('importproducts')->__('Change status'),
         'url'  => $this->getUrl('//massStatus', array('_current'=>true)),
         'additional' => array(
         'visibility' => array(
         'name' => 'status',
         'type' => 'select',
         'class' => 'required-entry',
         'label' => Mage::helper('importproducts')->__('Status'),
         'values' => $statuses
         )
         )
         ));*/
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}