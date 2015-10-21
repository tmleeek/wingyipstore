<?php
class Wingyip_Exportorder_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
   /* protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        //$collection->getSelect()->join('sales_flat_order', 'main_table.entity_id = sales_flat_order.entity_id',array('shipping_description'));
        $collection->getSelect()->join('wy_sales_flat_order', 'main_table.entity_id = wy_sales_flat_order.entity_id',array('shipping_description' => 'shipping_description'));
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }     */
 /* protected function _getCollectionClass()
  {
        return 'sales/order_grid_collection';
  }   */
   protected function _prepareCollection()
   {   
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $collection->getSelect()->joinLeft(array('shipping'=>'wy_sales_flat_order'), 'main_table.entity_id= shipping.entity_id',array('shipping_description'));
        $collection->addFilterToMap('increment_id', 'main_table.increment_id');
        $collection->addFilterToMap('status', 'main_table.status');  
        $collection->addFilterToMap('base_grand_total', 'main_table.base_grand_total');
        $collection->addFilterToMap('grand_total', 'main_table.grand_total'); 
        $collection->addFilterToMap('store_id', 'main_table.store_id'); 
        $collection->addFilterToMap('created_at', 'main_table.created_at'); 
        $this->setCollection($collection);                                                        
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();       
   }

    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
            'filter_index' => 'main_table.increment_id'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'filter_index' => 'main_table.store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
            'filter_index' => 'main_table.billing_name'
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
            'filter_index' => 'main_table.shipping_name'
        ));
        
        $this->addColumn('shipping_description', array(
            'header' => Mage::helper('sales')->__('Shipping Method'),
            'index' => 'shipping_description',
            'filter_index' => 'shipping.shipping_description'
        ));
        
        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'filter_index' => 'main_table.base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'filter_index' => 'main_table.grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));
        
        $this->addColumn('upload_status', array(
            'header' => Mage::helper('sales')->__('Upload Status'),
            'index' => 'upload_status',
            'filter_index' => 'main_table.upload_status',
            'type'  => 'options',
            'options'   => array(
            '1' => 'Awaiting Export',
            '2' => 'Awaiting Upload',
            '3' => 'Completed',
            '4' => 'Failed'
            ),
            'width' => '40px',
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'*/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
        }
        $this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

        return parent::_prepareColumns();
    }
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();
        $this->getMassactionBlock()->addItem('export_order', array(
             'label'=> Mage::helper('sales')->__('Export orders'),
             'url'  => $this->getUrl('exportorder/adminhtml_exportorder/exportselectedorders'),
        ));        
        return $this;
    }

}
