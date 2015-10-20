<?php

class Wingyip_Catalog_Block_Category_Children extends Mage_Core_Block_Template
{
    public function getCategory(){
        return Mage::registry('current_category');   
    }
    
    public function getChildren(){
         $children = array();
         $category = $this->getCategory();
         if($category && $category->hasChildren())
         {
             $children = Mage::getModel('catalog/category')->getCollection()
             ->addAttributeToSelect('*')
             ->addFieldtoFilter('entity_id',array('in'=>explode(',',$category->getChildren())));
         }
         return $children;
     }
     
     public function getImageUrl($category){ 
         return Mage::getBaseUrl('media').'catalog/category/'.$category->getThumbnail();
     }
     
     public function getBannerImage($_category){
         return Mage::getBaseUrl('media').'catalog/category/'.$_category->getBanner();
     }
     
     public function getDescription($_category){
        $attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter('description')->getFirstItem();
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        $prefix = Mage::getConfig()->getTablePrefix();
        
        $query = "SELECT (`atr`.`value`) AS `description`
                        FROM `".$prefix."catalog_category_entity` AS e
                        INNER JOIN `".$prefix."catalog_category_entity_text` AS `atr` ON ( `atr`.`entity_id` = `e`.`entity_id` )
                        AND (`atr`.`attribute_id` = '".$attributeInfo->getId()."')
                        AND (`atr`.`store_id` = 0)
                        WHERE (`e`.`entity_id` = '".$this->getCategory()->getId()."')";
                        
        return $readConnection->fetchOne($query);
     }
}
