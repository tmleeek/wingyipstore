<?php

class NBSSystem_Nitrogento_Model_Optimization_Indexer_Url extends Mage_Catalog_Model_Indexer_Url
{
    protected function _registerProductEvent(Mage_Index_Model_Event $event)
    {
        $product = $event->getDataObject();
        $dataChange2 = false;
        
        if(($product->dataHasChangedFor('status')&&$product->getData('status')=="1")||($product->dataHasChangedFor('visibility')&&$product->getData('visibility')!="1"))
        {
        	$dataChange2 = true;
        }
       
        $dataChange = $product->dataHasChangedFor('url_key')
            || $product->getIsChangedCategories()
            || $product->getIsChangedWebsites()
            || $dataChange2;
            
        if (!$product->getExcludeUrlRewrite() && $dataChange) {
            $event->addNewData('rewrite_product_ids', array($product->getId()));
        }
    }
    
    /**
     * Rebuild all index data
     */
    public function reindexAll()
    {
        /** @var $resourceModel Mage_Catalog_Model_Resource_Url */
        $resourceModel = Mage::getResourceSingleton('catalog/url');
        $resourceModel->beginTransaction();
        try {
            Mage::getSingleton('nitrogento/optimization_url')->refreshRewrites();
            $resourceModel->commit();
        } catch (Exception $e) {
            $resourceModel->rollBack();
            throw $e;
        }
    }
    
    /**
     * Get Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('nitrogento')->__('Nitrogento Catalog URL Rewrites');
    }
    
    /**
     * Get Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('nitrogento')->__('Nitrogento Index product and categories URL rewrites');
    }
}
