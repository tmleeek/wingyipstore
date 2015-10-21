<?php
class Wingyip_Recipe_Model_Mysql4_Review extends Mage_Core_Model_Mysql4_Abstract
{
	protected $_reviewDetailTable;
	
	
    public function _construct()
    {   
        $this->_init('recipe/review', 'review_id');
		$this->_reviewDetailTable   = $this->getTable('recipe/recipe_reviewdescr');
    }
    
    public function getUniqueCode($code)
    { 
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
                ->from($this->getTable('recipe/review'))
                     ->where('code = ?',$code);
        $row = $adapter->fetchRow($select);
        return $row;
    }
	
	/**
     * Perform actions after object save
     *
     * @param Varien_Object $object
     * @return Mage_Review_Model_Resource_Review
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $adapter = $this->_getWriteAdapter();
        /**
         * save detail
         */
/*        $detail = array(
            'title'     => $object->getTitle(),
            'detail'    => $object->getDetail(),
            'nickname'  => $object->getNickname(),
        );*/
		$detail = array();
		$select = $adapter->select()
            ->from($this->_reviewDetailTable)
            ->where('review_id = :review_id');
        $detailId = $adapter->fetchOne($select, array(':review_id' => $object->getId()));
        
        //print_r($detail);
        //echo $select;die;
        
        if ($detailId) {
            $condition = array("descr_id = ?" => $detailId);
            $detail['store_id']   = $object->getStoreId();
            $detail['review_id']  = $object->getId();
            $detail['subject']  = $object->getSubject();
            $detail['description']  = $object->getDescription(); 
            $adapter->update($this->_reviewDetailTable, array_filter($detail), $condition);
        } else { 
            $detail['store_id']   = $object->getStoreId();
           // $detail['customer_id']= $object->getCustomerId();
            $detail['review_id']  = $object->getId();
			$detail['subject']  = $object->getSubject();
			$detail['description']  = $object->getDescription(); 
            $adapter->insert($this->_reviewDetailTable, array_filter($detail));
        }


        return $this;
    }
	
}
