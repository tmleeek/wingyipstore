<?php
class Wingyip_Recipe_Block_Course extends Mage_Core_Block_Template{
    protected $_collection;
    
    protected function _prepareLayout()
    {                
        parent::_prepareLayout();
        return $this;
    }
    
    protected function getCollection()
    {
        if (is_null($this->_collection)) {
            $this->_collection = Mage::getModel('recipe/course')->getCollection();
        }
        return $this->_collection;
    }
    
    public function getRecipeCourseUrl($course)
    {
        $id_path = "recipe_course/{$course->getId()}";
        $mainUrlRewrite = Mage::getModel('core/url_rewrite')->loadByIdPath($id_path);
        return Mage::getUrl().$mainUrlRewrite->getRequestPath(); 
    }
}
