<?php
class Wingyip_Recipe_Block_Adminhtml_Course_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('courseGrid');
        // This is the primary key of the database
        $this->setDefaultSort('course_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
       // $this->setUseAjax(true);
    }
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('recipe/course')->getCollection();
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
        
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('recipe')->__('Creation Time'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'default'   => '--',
            'index'     => 'created_at',
        ));
        $this->addColumn('updated_at', array(
            'header'    => Mage::helper('recipe')->__('Update Time'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'default'   => '--',
            'index'     => 'updated_at',
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
        $this->setMassactionIdField('course_id');
        $this->getMassactionBlock()->setFormFieldName('course_id');
        $this->getMassactionBlock()->addItem('delete', array(
        'label'=> Mage::helper('recipe')->__('Delete'),
        'url'  => $this->getUrl('*/*/massDelete', array('' => '')),
        'confirm' => Mage::helper('recipe')->__('Are you sure?')
        ));   
    }
}