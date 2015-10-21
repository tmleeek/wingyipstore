<?php
class Wingyip_Recipe_Block_Category_View extends Mage_Core_Block_Template{
    protected $_collection;
    
    protected function _prepareLayout()
    {                
         parent::_prepareLayout();
		
        if ($headBlock = $this->getLayout()->getBlock('head')) {
			
           	$recipieInfo = $this->getRecipeCategory(); 
				if ($title = $recipieInfo->getMetaTitle()) {
					$headBlock->setTitle($title);
				}
				if ($description = $recipieInfo->getMetaDescription()) {
					$headBlock->setDescription($description);
				}
				if ($keywords = $recipieInfo->getMetaKeyword()) {
					$headBlock->setKeywords($keywords);
				}			
        	}
        return $this;
    }
    
	public function getRecipeCategory()
    {
        $recipeCatId = $this->getRequest()->getParam('category_id',0);
        if (!Mage::registry('recipe_category') && $recipeCatId) {
            $recipeCategory = Mage::getModel('recipe/category')->load($recipeCatId);
            Mage::register('recipe_category', $recipeCategory);
        }
        return Mage::registry('recipe_category');
    }
	
	
   
}
