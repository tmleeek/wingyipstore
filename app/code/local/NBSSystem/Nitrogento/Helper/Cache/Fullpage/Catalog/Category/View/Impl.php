<?php

class NBSSystem_Nitrogento_Helper_Cache_Fullpage_Catalog_Category_View_Impl extends NBSSystem_Nitrogento_Helper_Cache_Fullpage_Abstract
{
    public function buildCacheTags()
    {
        $cacheTags = parent::buildBaseCacheTags();
        
        if ($currentCategory = Mage::registry('current_category'))
        {
            if ($currentCategory->getIsAnchor())
            {
                foreach ($currentCategory->getAllChildren(true) as $categoryId)
                {
                    $cacheTags[] = Mage_Catalog_Model_Category::CACHE_TAG . "_" . $categoryId;
                }
            }
            else
            {
                $cacheTags[] = Mage_Catalog_Model_Category::CACHE_TAG . "_" . $currentCategory->getId();
            }
        }
        
        // Not Necessary to add in cache all productsIds
        /*$currentLayer = Mage::getSingleton('catalog/layer');
        
        if ($currentLayer)
        {
            foreach ($currentLayer->getProductCollection()->getAllIds() as $productId)
            {
                $cacheTags[] = Mage_Catalog_Model_Product::CACHE_TAG . "_" . $productId;
            } 
        }*/
        
        return $cacheTags;
    }
    
    public function isPageCachable()
    {
    	if ($currentCategory = Mage::registry('current_category'))
    	{
    		return Mage::helper('nitrogento')->isCatalogEntityContentCachable($currentCategory);
    	}
    	
    	return parent::isPageCachable();
    }
}