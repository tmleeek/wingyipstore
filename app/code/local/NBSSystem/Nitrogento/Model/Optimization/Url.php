<?php
 
class NBSSystem_Nitrogento_Model_Optimization_Url extends Mage_Catalog_Model_Url
{
    /**
     * Retrieve resource model
     *
     * @return NBSSystem_Nitrogento_Model_Mysql4_Optimization_Indexer_Url
     */
    public function getResource()
    {
        if (is_null($this->_resourceModel)) {
            $this->_resourceModel = Mage::getResourceModel('nitrogento/optimization_indexer_url');
        }
        return $this->_resourceModel;
    }
    
    public function refreshProductRewrites($storeId)
    {
        $this->_categories = array();
        $storeRootCategoryId = $this->getStores($storeId)->getRootCategoryId();
        $this->_categories[$storeRootCategoryId] = $this->getResource()->getCategory($storeRootCategoryId, $storeId);

        $lastEntityId = 0;
        $process = true;

        $enableOptimisation = Mage::getStoreConfigFlag('nitrogento/optimization_index_url/enable');
        $excludeProductsDisabled = Mage::getStoreConfigFlag('nitrogento/optimization_index_url/disable');
        $excludeProductsNotVisible = Mage::getStoreConfigFlag('nitrogento/optimization_index_url/notvisible');
        $useCategoriesInUrl = Mage::getStoreConfig('catalog/seo/product_use_categories');
        
        while ($process == true) {
            $products = $this->getResource()->getProductsByStore($storeId, $lastEntityId);
            if (!$products) {
                $process = false;
                break;
            }

            $this->_rewrites = array();
            $this->_rewrites = $this->getResource()->prepareRewrites($storeId, false, array_keys($products));

            $loadCategories = array();
            foreach ($products as $product) {
                foreach ($product->getCategoryIds() as $categoryId) {
                    if (!isset($this->_categories[$categoryId])) {
                        $loadCategories[$categoryId] = $categoryId;
                    }
                }
            }

            if ($loadCategories) {
                foreach ($this->getResource()->getCategories($loadCategories, $storeId) as $category) {
                    $this->_categories[$category->getId()] = $category;
                }
            }
            
            
            foreach ($products as $product) {
	            
           	 	if($enableOptimisation&&$excludeProductsDisabled&&$product->getData("status")==2)
           	 	{
	           	 	continue;
           	 	}
            	
           	 	if($enableOptimisation&&$excludeProductsNotVisible&&$product->getData("visibility")==1)
           	 	{
	           	 	continue;
           	 	}            	
            	
           	 	// Always Reindex short url
                $this->_refreshProductRewrite($product, $this->_categories[$storeRootCategoryId]);
            	
            	
            	if($useCategoriesInUrl!="0"||!$enableOptimisation)
            	{
	            	foreach ($product->getCategoryIds() as $categoryId) {
                    	if ($categoryId != $storeRootCategoryId && isset($this->_categories[$categoryId])) {
                        	$this->_refreshProductRewrite($product, $this->_categories[$categoryId]);
                        }
                    }
            	}

            }

            unset($products);
            $this->_rewrites = array();
        }

        $this->_categories = array();
        return $this;
    }
}