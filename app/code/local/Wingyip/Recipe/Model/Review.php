<?php 
class Wingyip_Recipe_Model_Review extends Mage_Core_Model_Abstract
{ 
    public function _construct()
    {
        parent::_construct();
        $this->_init('recipe/review');
    }
	
	public function getRatingList($recipeId)
    {
		//$recipeId = $this->getRequest()->getParam('id',0);
       /* if (!Mage::registry('recipe') && $recipeId) {
            $recipe = Mage::getModel('recipe/recipe')->load($recipeId);
            Mage::register('recipe', $recipe);
        }
        $recipe= Mage::registry('recipe');*/
		
        $recipeCollection = Mage::getModel('recipe/review')->getCollection()
				->addFieldToFilter('recipe_id',$recipeId)
				->addFieldToFilter('status','2')
				->addExpressionFieldToSelect('ratingsum', 'SUM(rating)')
				->getFirstItem();
				
		$recipecountCollection = Mage::getModel('recipe/review')->getCollection()
				->addFieldToFilter('recipe_id',$recipeId)
				->addFieldToFilter('status','2');		
				
		if(count($recipeCollection->getData()))
		{
			if($recipeCollection->getRatingsum()){
				$count=count($recipecountCollection->getData());
				$totalRating=$recipeCollection->getRatingsum()/$count;
				$vote=$totalRating*20;
				return $vote;
			}
			else
				return 0;
		}
		else		
        	return 0;
    }
	
}
