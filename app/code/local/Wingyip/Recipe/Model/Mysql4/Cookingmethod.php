<?php
class Wingyip_Recipe_Model_Mysql4_Cookingmethod extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('recipe/cookingmethod', 'cooking_id');
    }
    
    public function getUniqueCode($code)
    { 
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
                ->from($this->getTable('recipe/cookingmethod'))
                     ->where('code = ?',$code);
        $row = $adapter->fetchRow($select);
        return $row;
    }
}
