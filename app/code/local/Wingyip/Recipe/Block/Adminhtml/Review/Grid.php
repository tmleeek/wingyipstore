<?php
class Wingyip_Recipe_Block_Adminhtml_Review_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
      parent::__construct(); 
      $this->setId('reviewGrid');
      $this->setDefaultSort('review_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
    }
 
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('recipe/review')->getCollection();
        $resource = Mage::getSingleton('core/resource');
        $collection->getSelect()
              ->join(
                      array('de'=>Mage::getConfig()->getTablePrefix().'recipe_review_description'),
                     'de.review_id = main_table.review_id',
                      array('de.*')
                      );
        $collection->getSelect()
              ->join(
                      array('re'=>Mage::getConfig()->getTablePrefix().'recipe'),
                     're.recipe_id = main_table.recipe_id',
                      array('re.name')
                      );
        /*$collection->getSelect()
              ->join(
                      array('st'=>Mage::getConfig()->getTablePrefix().'core_store'),
                     'st.store_id = main_table.store_id',
                      array('st.name as storename')
                      );    */
        $this->setCollection($collection);
        /*foreach($collection as $item)
        {
            echo '<pre>';print_r($item->getData()); 
        }
        die();*/
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('subject', array(
            'header'    => Mage::helper('recipe')->__('Title'),
            'align'     =>'left',
            'index'     => 'subject',
        ));
        
        /*$this->addColumn('description', array(
            'header'    => Mage::helper('recipe')->__('Review'),
            'align'     => 'left',
            'width'     => '300px',
            'index'     => 'description',
        ));*/
        
        $this->addColumn('nickname', array(
            'header'        => Mage::helper('recipe')->__('Nickname'),
            'align'         => 'left',
            'width'         => '100px',
            'index'         => 'nickname',
            'type'          => 'text',
            'truncate'      => 50,
            'escape'        => true,
        ));
         
        $this->addColumn('recipename', array(
            'header'    => Mage::helper('recipe')->__('Recipe Name'),
            'align'     =>'left',
            'index'     => 'name',
        ));
        
        /*if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('visible_in', array(
                'header'    => Mage::helper('recipe')->__('Visible In'),
                'index'     => 'storename',
                'type'      => 'store',
                'width'     => '100px',
                'store_view'=> true,
            ));
        }*/
        
        $this->addColumn('status', array(
 
            'header'    => Mage::helper('recipe')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                0 => Mage::helper('recipe')->__('Pending'),
                1 => Mage::helper('recipe')->__('Not Approved'),
                2 => Mage::helper('recipe')->__('Approved'),
            ),
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
        $this->setMassactionIdField('review_id');
        $this->getMassactionBlock()->setFormFieldName('review_id');
        $this->getMassactionBlock()->addItem('delete', array(
        'label'=> Mage::helper('recipe')->__('Delete'),
        'url'  => $this->getUrl('*/*/massDelete', array('' => '')),
        'confirm' => Mage::helper('recipe')->__('Are you sure?')
        ));
        
        $statuses = array(
                0 => Mage::helper('recipe')->__('Pending'),
                1 => Mage::helper('recipe')->__('Not Approved'),
                2 => Mage::helper('recipe')->__('Approved'),
            );
        
        
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
