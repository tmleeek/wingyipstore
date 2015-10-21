<?php

class NBSSystem_Nitrogento_Model_Core_Cache extends Mage_Core_Model_Cache
{
    protected $_useTwoLevels = false;
    
    public function __construct(array $options = array())
    {
        $config = new Mage_Core_Model_Config();
        $this->_defaultBackendOptions['cache_dir'] = isset($options['cache_dir']) ? $options['cache_dir'] :
            $config->getOptions()->getCacheDir();
        /**
         * Initialize id prefix
         */
        $this->_idPrefix = isset($options['id_prefix']) ? $options['id_prefix'] : '';
        if (!$this->_idPrefix && isset($options['prefix'])) {
            $this->_idPrefix = $options['prefix'];
        }
        if (empty($this->_idPrefix)) {
            $this->_idPrefix = substr(md5($config->getOptions()->getEtcDir()), 0, 3).'_';
        }

        $backend    = $this->_getBackendOptions($options);
        $frontend   = $this->_getFrontendOptions($options);

        $this->_frontend = Zend_Cache::factory('Varien_Cache_Core', $backend['type'], $frontend, $backend['options'],
            true, true, true
        );

        if (isset($options['request_processors'])) {
            $this->_requestProcessors = $options['request_processors'];
        }

        if (isset($options['disallow_save'])) {
            $this->_disallowSave = (bool)$options['disallow_save'];
        }
    }
    
    // TWEAK NITROGENTO : FOR RENDER PAGE 2nd LEVEL IS NOT MANDATORY
    protected function _getTwoLevelsBackendOptions($fastOptions, $cacheOptions)
    {
        $this->_useTwoLevels = true;
        return $fastOptions;
    }
    
    public function getUseTwoLevels()
    {
        return $this->_useTwoLevels;
    }
}