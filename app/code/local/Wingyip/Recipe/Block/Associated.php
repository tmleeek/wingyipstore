<?php
class Wingyip_Recipe_Block_Associated extends Mage_Core_Block_Template{
    protected $_collection;
    
    protected function _prepareLayout()
    {                
        parent::_prepareLayout();
        return $this;
    }
    
    protected function getCollection()
    {
        $recipeId = Mage::registry('recipe')->getId();
        
        $this->_collection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('*');
                
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->_collection);
        $associated  = Mage::getSingleton('core/resource')->getTableName('recipe/associated');
        $this->_collection->getSelect()->join(array('ass' => $associated), "e.entity_id = ass.product_id", array('ass.*'))
                ->where("recipe_id = ?",$recipeId);
        
        return $this->_collection;
    }
    
    public function getAddToCartUrl($product, $additional = array()){
        $product = Mage::getModel('catalog/product')->load($product->getId()) ;
        return $this->helper('checkout/cart')->getAddUrl($product, $additional); 
    }
}
