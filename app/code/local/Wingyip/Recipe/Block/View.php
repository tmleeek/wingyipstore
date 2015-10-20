<?php
class Wingyip_Recipe_Block_View extends Mage_Core_Block_Template{

	protected function _prepareLayout()
    {
        parent::_prepareLayout();
		
        if ($headBlock = $this->getLayout()->getBlock('head')) {
			
           	$recipieInfo = $this->getRecipe(); 
				if ($title = $recipieInfo->getMetaTitle()) {
					$headBlock->setTitle($title);
				}
				if ($description = $recipieInfo->getMetaDescription()) {
					$headBlock->setDescription($description);
				}
				if ($keywords = $recipieInfo->getMetaKeyword()) {
					$headBlock->setKeywords($keywords);
				}			
        	}
        return $this;
    }


    public function getRecipe()
    {
        $recipeId = $this->getRequest()->getParam('id',0);
        if (!Mage::registry('recipe') && $recipeId) {
            $recipe = Mage::getModel('recipe/recipe')->load($recipeId);
            Mage::register('recipe', $recipe);
        }
        return Mage::registry('recipe');
    }
    
    public function getIngredients($recipe){
        return Mage::getModel('recipe/ingredient')
        ->getCollection()
        ->addFieldToFilter('status',1)
        ->addFieldToFilter('recipe_ingredients_id',array("in"=>Mage::getResourceModel('recipe/recipe')->lookupIngredientIds($recipe->getId())))
        ->setOrder('sort', 'ASC')
        ->load();
    }
    
    public function getCupboardIngredients($recipe){
       return Mage::getModel('recipe/cupboard')
       ->getCollection()
       ->addFieldToFilter('status',1)
       ->addFieldToFilter('recipe_cupboard_id',array("in"=>Mage::getResourceModel('recipe/recipe')->lookupCupboardIngredientsIds($recipe->getId())))
       ->setOrder('sort', 'ASC')
       ->load(); 
    }
    
    public function getCuisine($recipe){
        return Mage::getModel('recipe/cuisine')
        ->getCollection()
        ->addFieldToFilter('status',1)
        ->addFieldToFilter('recipe_cuisine_id',array("in"=>Mage::getResourceModel('recipe/recipe')->lookupCuisineTypeIds($recipe->getId())))
        ->setOrder('sort', 'ASC')
        ->load();    
    }
    
    public function getCategories($recipe){
        return Mage::getModel('recipe/category')
        ->getCollection()
        ->addFieldToFilter('status',1)
        ->addFieldToFilter('recipe_category_id',array("in"=>Mage::getResourceModel('recipe/recipe')->lookupCategoryIds($recipe->getId())))
        ->setOrder('sort', 'ASC')
        ->load();    
    }
    
    public function getCookingmethod($recipe){
        return Mage::getModel('recipe/cookingmethod')
        ->getCollection()->addFieldToFilter('status',1)
        ->addFieldToFilter('cooking_id',array("in"=>Mage::getResourceModel('recipe/recipe')->lookupCookingMethodIds($recipe->getId())))
        ->setOrder('sort', 'ASC')
        ->load();
    }
	
	public function getRecipeImage(){
		$recipeId=$this->getRecipe()->getId();
		$collection=Mage::getModel('recipe/image')->getCollection()
					->addFieldToFilter('recipe_id',$recipeId)
					->addFieldToFilter('is_default','1')
					->getFirstItem()
					->load();
					
		return  $collection->getImage();
	}
	
	public function getGalleryImages(){
		$recipeId=$this->getRecipe()->getId();
		$collection=Mage::getModel('recipe/image')->getCollection()
					->addFieldToFilter('recipe_id',$recipeId)
					->addFieldToFilter('is_default','0')
					;
					
			
		return  $collection;
	}

    public function getVideoUrl(){
        $recipeId=$this->getRecipe()->getId();
        $videoUrl =Mage::getModel('recipe/recipe')->getCollection()
            ->addFieldToFilter('recipe_id',$recipeId)
            ->getFirstItem()
            ->getVideo();
        ;
        return $videoUrl;
    }
	
	public function getImageUrl($image){
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$image;
	}
	public function getRatingUrl()
    {
        return Mage::getUrl('recipe/review/list', array(
            'id'     => $this->getRecipe()->getId()
        ));
    }
}
