<?php
class Wingyip_Recipe_Block_Adminhtml_Recipe_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('recipeGrid');
        // This is the primary key of the database
        $this->setDefaultSort('recipe_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
       // $this->setUseAjax(true);
    }
 
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('recipe/recipe')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
       /* $this->addColumn('recipe_category_id', array(
            'header'    => Mage::helper('recipe')->__('Recipe Category Id'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'recipe_category_id',
        ));*/
 
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
      /*  $this->addColumn('description', array(
            'header'    => Mage::helper('recipe')->__('Description'),
            'width'     => '250px',
            'index'     => 'description',
        ));
        
        $this->addColumn('level', array(
            'header'    => Mage::helper('recipe')->__('Level'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'level',
        ));
        
        $this->addColumn('path', array(
            'header'    => Mage::helper('recipe')->__('Path'),
            'align'     =>'left',
            'index'     => 'path',
        ));     */
        
        $this->addColumn('status', array(
 
            'header'    => Mage::helper('recipe')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::helper('recipe')->getRecipeStatusesOptionArray(),
        ));
        
        /*$this->addColumn('sort', array(
                'header'    => Mage::helper('recipe')->__('Sort'),
                'align'     =>'right',
                'width'     => '50px',
                'index'     => 'sort',
            ));
        
         $this->addColumn('parent_id', array(
            'header'    => Mage::helper('recipe')->__('Parent ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'parent_id',
        ));    */
 
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('recipe')->__('Created At'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'date',
            'default'   => '--',
            'index'     => 'created_at',
        ));
 
        $this->addColumn('updated_at', array(
            'header'    => Mage::helper('recipe')->__('Updated At'),
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
        $this->setMassactionIdField('recipe_id');
        $this->getMassactionBlock()->setFormFieldName('recipe_id');
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
        
        $this->getMassactionBlock()->addItem('update_url', array(
        'label'=> Mage::helper('recipe')->__('Update Url'),
        'url'  => $this->getUrl('*/*/massUpdateRecipeUrl', array('' => '')),
        'confirm' => Mage::helper('recipe')->__('Are you sure?')
        ));
    } 
}