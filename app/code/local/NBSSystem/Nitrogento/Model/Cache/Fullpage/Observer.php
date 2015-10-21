<?php

class NBSSystem_Nitrogento_Model_Cache_Fullpage_Observer extends Varien_Object
{
    // Retro compat with 1.2.6 without config clean cache, will be removed in 1.2.8
    public function decidePutPageInCache($observer)
    {
        $this->handleFullpageCache($observer);
    }
    
    public function handleFullpageCache($observer)
    {
        $response = Mage::app()->getResponse();
        
        if ($response->getHttpResponseCode() != '200'
         || Mage::helper("nitrogento")->isResponse404($response)
         || !Mage::helper("nitrogento")->isCacheFullpageEnabled()
         || !Mage::getSingleton('nitrogento/cache_fullpage_cookie')->getNitrogentoCacheFullpage())
        {
            return;
        }
        
        $cacheFullpageConfig = Mage::getSingleton('nitrogento/cache_fullpage_config');
        
        if ($cacheFullpageConfig->tryPageMatchWithCacheFullpageConfig())
        {
            if($cacheHelper = Mage::helper($cacheFullpageConfig->getHelperClass()))
            {
                $cacheHelper->handleFullpageCache($response, $cacheFullpageConfig->getCacheLifetime());
            }
        }
    }
    
    public function ensureFormKeyPlaceholder($observer)
    {
    	$response = Mage::app()->getResponse();
    	    	
    	$response->setBody(str_replace(
    		Mage::getSingleton('core/session')->getFormKey(),
    		NBSSystem_Nitrogento_Helper_Data::NITROGENTO_FORMKEY_PLACEHOLDER,
    		$response->getBody(false)
    	));
    }
}