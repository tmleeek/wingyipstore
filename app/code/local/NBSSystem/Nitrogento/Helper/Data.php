<?php

class NBSSystem_Nitrogento_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CACHE_BLOCKHTML_OBJECTS = "CACHE_BLOCKHTML_OBJECTS";
    const BLOCK_HTML = "block_html";
    
    const FULLPAGE = "FULLPAGE";
    const CACHE_FULLPAGE_OBJECTS = "CACHE_FULLPAGE_OBJECTS";
    const CACHE_FULLPAGE_DEBUG = "CACHE_FULLPAGE_DEBUG";
    
    const NITROGENTO_REFRESH_CACHE_ON_PAGE = 'nitrogento_refresh_cache_on_page';
    
    const NITROGENTO_COOKIE_FORMKEY = 'nitrogento_formkey';
    const NITROGENTO_FORMKEY_PLACEHOLDER = 'nitrogento_formkey_placeholder';
    
    protected static $_cacheContainer;
    
    public function isCacheBlockhtmlEnabled()
    {
        //return Mage::app()->useCache(Mage_Core_Block_Abstract::CACHE_GROUP);
        // Compatibility 1.3
        return Mage::app()->useCache(self::BLOCK_HTML);
    }
    
    public function isCacheFullpageEnabled()
    {
        return Mage::app()->useCache(strtolower(self::FULLPAGE));
    }
    
    public function toGroupStoresOptionArray()
    {
        $groupStores = array();
        $stores = Mage::getModel('core/store')->getCollection()->setLoadDefault(true);
        
        foreach ($stores as $store)
        {
            $groupStores[$store->getGroup()->getId()]["group_name"] = $store->getGroup()->getName();
            
            if (empty($groupStores[$store->getGroup()->getId()]["stores"]))
            {
                $groupStores[$store->getGroup()->getId()]["stores"] = array();
            }
            
            $groupStores[$store->getGroup()->getId()]["stores"][$store->getId()] = $store->getName();
        }
        
        return $groupStores;
    }
    
    public function toStoresOptionArray()
    {
        $assocStores = array();
        $stores = Mage::getModel('core/store')->getCollection()->setLoadDefault(true);
        
        foreach ($stores as $store)
        {
            $assocStores[$store->getId()] = $store->getName();
        }
        
        return $assocStores;
    }
    
    public function toCustomerGroupsOptionArray()
    {
        $assocCustomerGroups = array();
        $customerGroups = Mage::getModel('customer/group')->getCollection();
        
        foreach ($customerGroups as $customerGroup)
        {
            $assocCustomerGroups[$customerGroup->getId()] = $customerGroup->getCode();
        }
        
        return $assocCustomerGroups;
    }
    
    public function getBlockTimerKey($block)
    {
        return get_class($block) . '_' . $block->getTemplate() . '_' . Mage::app()->getStore()->getId();
    }
    
    public function extractDelimitedContentFromString($startTag, $endTag, $string, $trim = false)
    {
        $stringFirstSplit = explode($startTag, $string);
        $stringSecondSplit = explode($endTag, $stringFirstSplit[count($stringFirstSplit) - 1]);
        
        if ($trim)
        {
            return trim($stringSecondSplit[0]);
        }
        else
        {
            return $stringSecondSplit[0];
        }
    }
    
    public static function getSimpleConfig()
    {
        if (!Mage::registry('simple_config'))
        {
            Varien_Profiler::start('nitrogento_load_simple_config');
            Mage::setRoot();
            $config = new Mage_Core_Model_Config();
            $config->loadFile($config->getOptions()->getEtcDir() . DS . 'local.xml');
            Mage::register('simple_config', $config);
            Varien_Profiler::stop('nitrogento_load_simple_config');
        }
        
        return Mage::registry('simple_config');
    }
    
    public static function getQueryStringFilters()
    {
        $result = array(self::NITROGENTO_REFRESH_CACHE_ON_PAGE);
        $simpleConfig = self::getSimpleConfig();
                    
        if ($simpleConfig->getNode('global/nitrogento/cache/query_string_filters'))
        {
            $queryStringFilters = $simpleConfig->getNode('global/nitrogento/cache/query_string_filters')->asArray();
            
            if (!empty($queryStringFilters))
            {
                $result = array_merge(explode(',', $queryStringFilters), $result);
            }
        }
        
        return $result;
    }
    
    public static function formatCacheKey($cacheKey)
    {
        $cacheKey = md5($cacheKey);
        $cacheKey = strtoupper($cacheKey);
        //$cacheKey = preg_replace('/([^a-zA-Z0-9_]{1,1})/', '_', $cacheKey);        
        return $cacheKey;
    }
    
    public function validateLicenceKey()
    {
        try
        {
            if (!Mage::app()->loadCache('nitrogento_validate_licence_key_flag'))
            {
                $client = new Zend_Http_Client('http://www.nitrogento.com/nitrogent-users-write.php',  array('timeout' => 5));
                $client->setMethod(Zend_Http_Client::GET);
                $client->setParameterGet(array(
                    'licence_key' => Mage::getStoreConfig('nitrogento/licence/key'),
                    'url' => Mage::getStoreConfig('web/unsecure/base_url'),
                    'version' => Mage::app()->getConfig()->getNode('modules/NBSSystem_Nitrogento/version')->asArray(),
					'contact_email' => Mage::getStoreConfig('trans_email/ident_general/email'),
                ));
                $client->setHeaders('Accept-Encoding', 'gzip;q=0, compress;q=0');
                $response = $client->request();
                Mage::getConfig()->saveConfig('nitrogento/licence/error_message', $response->getBody());
                Mage::app()->saveCache('nitrogento_validate_licence_key_flag', true, array(), 3600);
            }
        }
        catch (Exception $e)
        {
            // NOTHING TO DO
        }
    }
    
    public static function getCacheContainer()
    {
        if (!self::$_cacheContainer)
        {
            if (version_compare(Mage::getVersion(), '1.4.0', '>='))
            {
                self::$_cacheContainer = new NBSSystem_Nitrogento_Model_Core_Cache_Container_V14();
            }
            else
            {
                self::$_cacheContainer = new NBSSystem_Nitrogento_Model_Core_Cache_Container_V13();
            }
        }
        
        return self::$_cacheContainer;
    }
    
    public static function loadFromCache($cacheKey, Zend_Cache_Core $cacheFrontend, $useTwoLevels = false)
    {
        $content = $cacheFrontend->load($cacheKey);
        
        if (!$content)
        {
            return false;
        }
        
        if ($useTwoLevels)
        {
            $content = unserialize($content);
            $content = $content['data'];
        }
        
        return $content;
    }
    
    public static function getDeviceKey()
    {
        if (is_null(Mage::registry('device_key')))
        {
            $deviceKey = 0;
            $simpleConfig = self::getSimpleConfig();
            
            if ($simpleConfig->getNode('global/nitrogento/device_detector/class'))
            {
                $deviceDetectorClass = $simpleConfig->getNode('global/nitrogento/device_detector/class')->asArray();
                
                if (!empty($deviceDetectorClass))
                {
                    $deviceDetector = new $deviceDetectorClass();
                    
                    if ($deviceDetector instanceof NBSSystem_Nitrogento_Helper_Device_IDetector)
                    {
                        $deviceKey = $deviceDetector->getDeviceKey();
                    }
                }
            }
            
            Mage::register('device_key', $deviceKey, true);
        }
        
        return Mage::registry('device_key');
    }
    
    public function getCDNUsableUrl($baseUrl)
    {
        if (!Mage::getStoreConfig('nitrogento/optimization_cdn/enabled'))
        {
            return $baseUrl;
        } 
        
        $host = Mage::getStoreConfig('nitrogento/optimization_cdn/base');
        $cdnHost = Mage::getStoreConfig('nitrogento/optimization_cdn/cdn');
        $cn = Mage::getStoreConfig('nitrogento/optimization_cdn/number');
        return str_replace($host, str_replace('[X]', rand(1, $cn), $cdnHost), $baseUrl);
    }
    
    public function isResponse404(Zend_Controller_Response_Abstract $response)
    {
    	foreach($response->getHeaders() as $header)
    	{
    		if($header['value'] == '404 Not Found')
    		{
    			return true;
    		}
    	}
    	
    	if($response->getHttpResponseCode() == '404') 
    	{
    		return true;
    	}
    	
    	return false;
    }
    
    public function isCatalogEntityContentCachable(Mage_Catalog_Model_Abstract $entity)
    {
    	if(!Mage::getStoreConfig('nitrogento/cache/decide_manual_catalog_entity_cache_storing')) 
    	{
    		return true;
    	}
    	
    	// IF DATA IS NOT SET WE KEEP OLD BEHAVIOUR : CACHE ALL
    	if(!$entity->hasData('store_in_nitrogento_cache'))
    	{
    		return true;
    	}
    	
    	return $entity->getData('store_in_nitrogento_cache');
    }
}