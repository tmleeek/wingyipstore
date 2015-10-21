<?php
class Wingyip_Recipe_Model_Mysql4_Cupboard extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('recipe/cupboard', 'recipe_cupboard_id');
    }
    public function getUniqueCode($code)
    { 
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
                ->from($this->getTable('recipe/cupboard'))
                     ->where('code = ?',$code);
        $row = $adapter->fetchRow($select);
        return $row;
    }
}
