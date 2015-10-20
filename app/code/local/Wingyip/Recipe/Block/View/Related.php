<?php
class Wingyip_Recipe_Block_View_Related extends Wingyip_Recipe_Block_List{
    
    protected function _prepareLayout()
    {                
        parent::_prepareLayout();
        return $this;
    }
    
    protected function getRelatedRecipeCollection()
    {
        $recipeId = Mage::registry('recipe')->getId();
        
		$collection=Mage::getModel('recipe/related')->getCollection();
					//->addFieldToFilter('main_table.recipe_id',$recipeId);
		
                
        $relatedrecipe  = Mage::getSingleton('core/resource')->getTableName('recipe/recipe');
        $collection->getSelect()->join(array('rel' => $relatedrecipe), "main_table.relrecipe_id = rel.recipe_id");// and main.recipe_id=".$recipeId);
		$collection->getSelect()->where("main_table.recipe_id = ?",$recipeId)
					->order('main_table.position ASC');
        //echo $collection->getSelect(); exit;
        return $collection;
    }
    
    public function getAddToCartUrl($product, $additional = array()){
        $product = Mage::getModel('catalog/product')->load($product->getId()) ;
        return $this->helper('checkout/cart')->getAddUrl($product, $additional); 
    }
}
