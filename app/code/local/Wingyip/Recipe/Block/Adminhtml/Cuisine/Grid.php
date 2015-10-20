<?php
class Wingyip_Recipe_Block_Adminhtml_Cuisine_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('cuisineGrid');
        // This is the primary key of the database
        $this->setDefaultSort('recipe_cuisine_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
       // $this->setUseAjax(true);
    }
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('recipe/cuisine')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => Mage::helper('recipe')->__('Name'),
            'align'     =>'left',
            'index'     => 'name',
        ));   
        $this->addColumn('code', array(
            'header'    => Mage::helper('recipe')->__('Code'),
            'align'     =>'left',
            'index'     => 'code',
        ));
        $this->addColumn('status', array(
            'header'    => Mage::helper('recipe')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::helper('recipe')->getRecipeStatusesOptionArray(), 
        ));
        $this->addColumn('created_time', array(
            'header'    => Mage::helper('recipe')->__('Creation Time'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'default'   => '--',
            'index'     => 'created_time',
        ));
        $this->addColumn('update_time', array(
            'header'    => Mage::helper('recipe')->__('Update Time'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'default'   => '--',
            'index'     => 'update_time',
        ));   
        return parent::_prepareColumns();
    }
 
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    public function getGridUrl()
    {
      return $this->getUrl('*/*/grid', array('_current'=>true));
    }
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('recipe_cuisine_id');
        $this->getMassactionBlock()->setFormFieldName('recipe_cuisine_id');
        $this->getMassactionBlock()->addItem('delete', array(
        'label'=> Mage::helper('recipe')->__('Delete'),
        'url'  => $this->getUrl('*/*/massDelete', array('' => '')),
        'confirm' => Mage::helper('recipe')->__('Are you sure?')
        ));
        
        $statuses = Mage::helper('recipe')->getRecipeStatusesOptionArray();
        $this->getMassactionBlock()->addItem('update_status', array(
            'label'         => Mage::helper('recipe')->__('Update Status'),
            'url'           => $this->getUrl(
                '*/*/massUpdateStatus',
                array('ret' => Mage::registry('usePendingFilter') ? 'pending' : 'index')
            ),
            'additional'    => array(
                'status'    => array(
                    'name'      => 'status',
                    'type'      => 'select',
                    'class'     => 'required-entry',
                    'label'     => Mage::helper('recipe')->__('Status'),
                    'values'    => $statuses
                )
            )
        ));
        
        return $this;
    }
 
 
}