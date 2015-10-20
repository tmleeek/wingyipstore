<?php 
class Wingyip_Recipe_Model_Associated extends Mage_Core_Model_Abstract{ 
    public function _construct()
    {
        parent::_construct();
        $this->_init('recipe/associated');
    }
    
    public function addAssociated($associatedData,$recipeId){
        if($recipeId){
            $collection= Mage::getModel('recipe/associated')->getCollection()
            ->addFieldToFilter('recipe_id',$recipeId);   
            
            foreach($collection as $deleteRecord){
                $delete=Mage::getModel('recipe/associated')->setId($deleteRecord->getId())->delete();
            }
            
            $newdata = array();
            foreach ($associatedData as $key=>$val){
                $newdata[$key] = $val['qty'];
            }
            
            $checkCollection=Mage::getModel('recipe/associated')->getCollection()
                ->addFieldToFilter('recipe_id',$recipeId);
            if($checkCollection->getSize()){
            } 
            else {      
                foreach($newdata as $productId=>$qty){
                    $insert=Mage::getModel('recipe/associated')
                    ->setRecipeId($recipeId)
                    ->setProductId($productId)
                    ->setQty($qty)
                    ->save();    
                }
            } 
        }
    }
}
