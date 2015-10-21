<?php
class Wingyip_Recipe_Model_Mysql4_Course extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_CourseTable;
    protected $_validKey;
    
    public function _construct()
    {   
        $this->_init('recipe/course', 'course_id');
        $this->_CourseTable = $this->getTable('recipe/course'); 
    }
    
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $courseId = $object->getId();
        
        if($courseId){
            $urlDb = $this->validUrlKey($courseId);
            $this->_validKey = $urlDb; 
        }
        return parent::_beforeSave($object);
    }
    
    /*
    *This function will be called whenever any new record is added in backend and will generate seo url 
    */
    protected function _afterSave(Mage_Core_Model_Abstract $object)   
    {   
        $courseId = $object->getId();

        if((empty($this->_validKey)) || (!$this->_isExistsUrlRewrite()) || ($this->_validKey != $object->getUrlKey()))
        {
            $identifier = $object->getName();
            if (!empty($identifier))
            {
                $module_name = 'course';
                $pathData = Mage::helper('recipe')->getRequestPath($module_name,$object);
                if(array_key_exists('1',$pathData)){
                    $urlKey = $pathData[1];
                    $adapter = $this->_getWriteAdapter();
                    $where = array(
                    'course_id = ?'=> (int)$courseId
                    );
                    $bind  = array('url_key' => $urlKey);
                    $adapter->update($this->_CourseTable, $bind, $where);
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
        $courseId = $object->getId();
        $module_name = 'course';
        Mage::helper('recipe')->deleteUrlRewrites($courseId,$module_name);
        
        $condition = array(
            'course_id = ?'     => (int) $object->getId(),
        );
        
        return parent::_beforeDelete($object);  
    }
    
    /**
    This function is created to validate the URL Title for Brand module
    */
    public function validUrlKey($courseId){
        $readAdapter = $this->_getReadAdapter();
        $select  = $readAdapter->select()
        ->from($this->_CourseTable, 'url_key')
        ->where('course_id='.$courseId);

        return $readAdapter->fetchOne($select);
    }
    
    protected function _isExistsUrlRewrite(){
        $url_rewrite = Mage::getModel('core/url_rewrite')->getCollection()->addFieldToFilter('request_path',$this->_validKey.'.html');
        if($url_rewrite->count()>0)
            return true;
        return false;
    }    
}
