<?php

abstract class NBSSystem_Nitrogento_Helper_Cache_Blockhtml_Abstract extends Mage_Core_Helper_Abstract
{
    /**
     * Build the current cacheKey block
     *
     * @return string
     */
    abstract public function buildCacheKey($block);
    
    /**
     * Build the current cacheTags block
     *
     * @return array
     */
    abstract public function buildCacheTags($block);
    
    /**
     * Build a full base cacheKey
     *
     * @return string
     */
    public function buildFullBaseCacheKey($block)
    {
        return Mage::app()->getStore()->getId() . '_' . 
            Mage::getSingleton('customer/session')->getCustomerGroupId() . '_' . 
            Mage::app()->getRequest()->getScheme() . '_' . 
            get_class($block) . '_' . 
            $block->getTemplate() . '_' . 
            NBSSystem_Nitrogento_Helper_Data::getDeviceKey();
    }
    
    /**
     * Build a simple base cacheKey
     *
     * @return string
     */
    public function buildSimpleBaseCacheKey($block)
    {
        return get_class($block) . '_' . $block->getTemplate();
    }
    
    public function buildBaseCacheTags($block)
    {
        return array(
            NBSSystem_Nitrogento_Helper_Data::BLOCK_HTML, 
            NBSSystem_Nitrogento_Helper_Data::CACHE_BLOCKHTML_OBJECTS, 
            get_class($block)
        );
    }
    
    public function isBlockCachable($block)
    {
        return true;
    }
    
    public function handleBlockhtmlCache($block, $cacheLifetime)
    {
        if (Mage::app()->getRequest()->getParam(NBSSystem_Nitrogento_Helper_Data::NITROGENTO_REFRESH_CACHE_ON_PAGE))
        {
            return;
        }
        
        if ($this->isBlockCachable($block))
    	{
            $block->addData(array(
                'cache_lifetime' => $cacheLifetime,
                'cache_tags' => $this->buildCacheTags($block),
                'cache_key' => NBSSystem_Nitrogento_Helper_Data::formatCacheKey($this->buildCacheKey($block))
            ));
        }
    }
}