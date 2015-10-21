<?php
class Wingyip_Recipe_Model_Mysql4_Category extends Mage_Core_Model_Mysql4_Abstract
{   
    protected $_CategoryTable;
    protected $_validKey;
    
    public function _construct()
    {   
        $this->_init('recipe/category', 'recipe_category_id');
        $this->_CategoryTable = $this->getTable('recipe/category'); 
    }
    
    public function getUniqueCode($code)
    { 
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
                ->from($this->getTable('recipe/category'))
                     ->where('code = ?',$code);
        $row = $adapter->fetchRow($select);
        return $row;
    }
    
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $categoryId = $object->getId();
        
        if($categoryId){
            $urlDb = $this->validUrlKey($categoryId);
            $this->_validKey = $urlDb; 
        }
        return parent::_beforeSave($object);
    }
    
    /*
    *This function will be called whenever any new record is added in backend and will generate seo url 
    */
    protected function _afterSave(Mage_Core_Model_Abstract $object)   
    {   
        $categoryId = $object->getId();
        
        if((empty($this->_validKey)) || (!$this->_isExistsUrlRewrite()) || ($this->_validKey != $object->getUrlKey()))
        {
            $identifier = $object->getName();
            if (!empty($identifier))
            {
                $module_name = 'category';
                $pathData = Mage::helper('recipe')->getRequestPath($module_name,$object);
                if(array_key_exists('1',$pathData)){
                    $urlKey = $pathData[1];
                    $adapter = $this->_getWriteAdapter();
                    $where = array(
                    'recipe_category_id = ?'=> (int)$categoryId
                    );
                    $bind  = array('url_key' => $urlKey);
                    $adapter->update($this->_CategoryTable, $bind, $where);
                }
                Mage::helper('recipe')->handleUrlRewrite($module_name,$object,$pathData[0]);
                //return parent::_afterSave($object); 
            }
        }
        return parent::_afterSave($object);
    }
    
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)  
    {
        /**
        * The url rewrite objects must also be deleted.
        */
        $categoryId = $object->getId();
        $module_name = 'category';
        Mage::helper('recipe')->deleteUrlRewrites($categoryId,$module_name);
        
        $condition = array(
            'recipe_category_id = ?'     => (int) $object->getId(),
        );
        
        return parent::_beforeDelete($object);  
    }
    
    /**
    This function is created to validate the URL Title for Brand module
    */
    public function validUrlKey($categoryId){
        $readAdapter = $this->_getReadAdapter();
        $select  = $readAdapter->select()
        ->from($this->_CategoryTable, 'url_key')
        ->where('recipe_category_id='.$categoryId);

        return $readAdapter->fetchOne($select);
    }
    
    protected function _isExistsUrlRewrite(){
        $url_rewrite = Mage::getModel('core/url_rewrite')->getCollection()->addFieldToFilter('request_path',$this->_validKey.'.html');
        if($url_rewrite->count()>0)
            return true;
        return false;
    }
}
