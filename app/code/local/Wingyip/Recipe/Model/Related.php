<?php 
class Wingyip_Recipe_Model_Related extends Mage_Core_Model_Abstract{ 
    public function _construct()
    {
        parent::_construct();
        $this->_init('recipe/related');
    }
    
    public function addRelated($relatedData,$recipeId){
        if($recipeId){
            $collection= Mage::getModel('recipe/related')->getCollection()
            ->addFieldToFilter('recipe_id',$recipeId);   

            foreach($collection as $deleteRecord){
                $delete=Mage::getModel('recipe/related')->setId($deleteRecord->getId())->delete();
            }
            $newdata = array();
            foreach ($relatedData as $key=>$val){
                $newdata[$key] = $val['sort'];
            }
            $checkCollection=Mage::getModel('recipe/related')->getCollection()
                ->addFieldToFilter('recipe_id',$recipeId);
            if($checkCollection->getSize()){
            } 
            else {      
                foreach($newdata as $relatedId=>$position){
                    $insert=Mage::getModel('recipe/related')
                    ->setRecipeId($recipeId)
                    ->setRelrecipeId($relatedId)
                    ->setPosition($position)
                    ->save();    
                }
            } 
        }
    }
}
