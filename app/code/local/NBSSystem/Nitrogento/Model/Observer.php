<?php

class NBSSystem_Nitrogento_Model_Observer
{
    public function validateLicenceKey($observer)
    {
        Mage::helper('nitrogento')->validateLicenceKey();
    }
    
    public function addProductDeleteCacheMassAction($observer)
    {
        // Retrieve current block
        $currentBlock = $observer->getBlock();
        if ($currentBlock instanceof Mage_Adminhtml_Block_Catalog_Product_Grid)
        {
            $currentBlock->getMassactionBlock()->addItem('cleanCache', array(
                    'label' => Mage::helper('nitrogento')->__('Clean Product Cache'),
                    'url'   => $currentBlock->getUrl('*/catalog_cleaner/massCleanProductCache')
            ));
        }
    }
    
    public function addCategoryDeleteCacheAction($observer)
    {
        // Retrieve current block
        $currentBlock = $observer->getBlock();
        if ($currentBlock instanceof Mage_Adminhtml_Block_Catalog_Category_Edit_Form)
        {
            $currentBlock->addAdditionalButton("cleanerCategoryCache", array(
                    'label'     => Mage::helper('catalog')->__('Clean Category Cache'),
                    'onclick'   => "cleanCategoryCache('" . $currentBlock->getUrl('*/catalog_cleaner/cleanCategoryCache', array('_current'=>true)). "')",
            ));
    
            $currentBlock->getLayout()->getBlock('js')->append($currentBlock->getLayout()->createBlock('nitrogento/adminhtml_cache_common_category_cleaner'));
        }
    }
    
    public function disableCatalogSessionMemorizeParams($observer)
    {
        Mage::getSingleton('catalog/session')->setParamsMemorizeDisabled(true);
    }
    
    public function ensureFormKey($observer)
    {
        if(isset($_COOKIE[NBSSystem_Nitrogento_Helper_Data::NITROGENTO_COOKIE_FORMKEY])) 
        {
    		Mage::getSingleton('core/session')->setData('_form_key', $_COOKIE[NBSSystem_Nitrogento_Helper_Data::NITROGENTO_COOKIE_FORMKEY]);
        }
    }
    
	public function restoreFormKeyFromPlaceholder($observer)
    {
    	$controllerAction = $observer->getEvent()->getControllerAction();
    	$bodyHtml = $controllerAction->getResponse()->getBody();
    	
    	$bodyHtml = str_replace(
    		NBSSystem_Nitrogento_Helper_Data::NITROGENTO_FORMKEY_PLACEHOLDER, 
    		Mage::getSingleton('core/session')->getFormKey(), 
    		$bodyHtml
        );
    	
    	$controllerAction->getResponse()->clearBody();
    	$controllerAction->getResponse()->appendBody($bodyHtml);
    }
    
    public function disableReportsProductDisplay($observer)
    {
        if (Mage::helper("nitrogento")->isCacheFullpageEnabled())
        {
            if (version_compare(Mage::getVersion(), '1.4.0', '>='))
            {
                $observer->getEvent()->getLayout()->getUpdate()->addHandle('nitrogento_disable_reports_product_display_v14');
            }
            else
            {
                $observer->getEvent()->getLayout()->getUpdate()->addHandle('nitrogento_disable_reports_product_display_v13');
            }
        }
    }
    
    public function saveStoremap()
    {
        $storemap = new NBSSystem_Nitrogento_Model_Core_Storemap();
        $storemap->save();
    }
}
