<?php
class Wingyip_Recipe_Block_Category extends Mage_Core_Block_Template{
    protected $_collection;
    
    protected function _prepareLayout()
    {                
        parent::_prepareLayout();
        return $this;
    }
    
    protected function getCollection()
    {
        if (is_null($this->_collection)) {
            $this->_collection = Mage::getModel('recipe/category')->getCollection()->addFieldToFilter('status',1);
        }
        return $this->_collection;
    }
    
    public function getRecipeCategoryUrl($category)
    {
        $id_path = "recipe_category/{$category->getId()}";
        $mainUrlRewrite = Mage::getModel('core/url_rewrite')->loadByIdPath($id_path);
        return Mage::getUrl().$mainUrlRewrite->getRequestPath(); 
    }
	
	public function getImage($category){
		$image=$category->getImage();
		
		if($image!="" && file_exists(Mage::getBaseDir('media').DS.$image)){


            $_imageUrl = Mage::getBaseDir('media').DS.$image;
            $imageResized = Mage::getBaseDir('media').DS.'resized/'.$image;
            if (!file_exists($imageResized)&&file_exists($_imageUrl)) {
                $imageObj = new Varien_Image($_imageUrl);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(TRUE);
                $imageObj->keepFrame(FALSE);
                $imageObj->resize(230, 175);
                $imageObj->save($imageResized);

                $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "resized/" . $image;
                return $resizedURL;
            } 
            elseif(file_exists($imageResized)) {
                $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "resized/" . $image;
                return $resizedURL;    
            }
            else{
                return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$image;    
            }    
        }
        else
        {
            return Mage::getDesign()->getSkinUrl()."images/noimage.png";
        }
	}
}
