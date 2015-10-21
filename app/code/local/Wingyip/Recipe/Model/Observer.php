<?php
/**
 * MageRevol
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Magerevol
 * @package    Magerevol_Brands
 * @author     Magerevol Development Team
 * @copyright  Copyright (c) 2012 MageRevol. (http://www.magerevol.com)
 * @license    http://opensource.org/licenses/osl-3.0.php
 */
class Wingyip_Recipe_Model_Observer
{
    /**
     * Flag to stop observer executing more than once
     *
     * @var static bool
     */
    static protected $_singletonFlag = false;

    /**
     * This method will run when the product is saved from the Magento Admin
     * Use this function to update the product model, process the
     * data or anything you like
     *
     * @param Varien_Event_Observer $observer
     */
    public function saveProductTabData(Varien_Event_Observer $observer) 
    {   
        if (!self::$_singletonFlag) {
            self::$_singletonFlag = true;
            $data = $this->_getRequest()->getPost();
            
            $product = $observer->getProduct();
            $productData = $product->getData();
            $productId = $product->getId();

            if ($product->hasDataChanges()){
                if (isset($data['links']['recipe'])) {  
                    $recipeData=Mage::helper('adminhtml/js')->decodeGridSerializedInput($data['links']['recipe']);
                    
                    $collection= Mage::getModel('recipe/associated')->getCollection()
                                ->addFieldToFilter('product_id',array("eq"=>$productId));
                    if(count($collection)>0){
                        foreach($collection as $deleteRecord){
                        $delete=Mage::getModel('recipe/associated')->setId($deleteRecord->getId())->delete();
                        }
                    }
                    
                    $newCollection= Mage::getModel('recipe/associated')->getCollection()
                                ->addFieldToFilter('product_id',array("eq"=>$productId));
                                
                    if($newCollection->getSize()){
                    } 
                    else {      
                        foreach($recipeData as $rId => $qty){
                        $associated=Mage::getModel('recipe/associated')
                            ->setRecipeId($rId)
                            ->setProductId($productId)
                            ->setQty($qty['qty'])
                            ->save();
                        }
                    }
                }
            }
        }
    }
    
    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }
}
