<?php class Ecommage_Xurl_Model_Store extends Mage_Core_Model_Store
{

    /**
     * @param string $type
     * @param null $secure
     * @return string
     * @throws Mage_Core_Exception
     */
    public function getBaseUrl($type = self::URL_TYPE_LINK, $secure = null)
    {
        $baseUrl = Mage::getStoreConfig('web/secure/use_in_frontend');
        if($baseUrl){
            if(is_null($type)){
                $type = Mage_Core_Model_Store::URL_TYPE_LINK;
                $secure = true;
            }else{
                $secure = true;
            }
        }

        $cacheKey = $type . '/' . (is_null($secure) ? 'null' : ($secure ? 'true' : 'false'));
        if (!isset($this->_baseUrlCache[$cacheKey])) {
            switch ($type) {
                case self::URL_TYPE_WEB:
                    $secure = is_null($secure) ? $this->isCurrentlySecure() : (bool)$secure;
                    $url = $this->getConfig('web/' . ($secure ? 'secure' : 'unsecure') . '/base_url');
                    break;

                case self::URL_TYPE_LINK:
                    $secure = (bool) $secure;
                    $url = $this->getConfig('web/' . ($secure ? 'secure' : 'unsecure') . '/base_link_url');
                    $url = $this->_updatePathUseRewrites($url);
                    $url = $this->_updatePathUseStoreView($url);
                    break;

                case self::URL_TYPE_DIRECT_LINK:
                    $secure = (bool) $secure;
                    $url = $this->getConfig('web/' . ($secure ? 'secure' : 'unsecure') . '/base_link_url');
                    $url = $this->_updatePathUseRewrites($url);
                    break;

                case self::URL_TYPE_SKIN:
                case self::URL_TYPE_JS:
                    $secure = is_null($secure) ? $this->isCurrentlySecure() : (bool) $secure;
                    $url = $this->getConfig('web/' . ($secure ? 'secure' : 'unsecure') . '/base_' . $type . '_url');
                    break;

                case self::URL_TYPE_MEDIA:
                    $url = $this->_updateMediaPathUseRewrites($secure);
                    break;

                default:
                    throw Mage::exception('Mage_Core', Mage::helper('core')->__('Invalid base url type'));
            }

            if (false !== strpos($url, '{{base_url}}')) {
                $baseUrl = Mage::getConfig()->substDistroServerVars('{{base_url}}');
                $url = str_replace('{{base_url}}', $baseUrl, $url);
            }

            $this->_baseUrlCache[$cacheKey] = rtrim($url, '/') . '/';
        }

        return $this->_baseUrlCache[$cacheKey];
    }
}