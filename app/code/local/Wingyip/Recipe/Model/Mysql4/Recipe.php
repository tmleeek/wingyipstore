<?php

class Wingyip_Recipe_Model_Mysql4_Recipe extends Mage_Core_Model_Mysql4_Abstract{
    
    protected $_RecipeTable;
    protected $_validKey;
    
    public function _construct()
    {   
        $this->_init('recipe/recipe', 'recipe_id');
        $this->_RecipeTable = $this->getTable('recipe/recipe');
    }
    public function getUniqueCode($code)
    { 
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
                ->from($this->getTable('recipe/recipe'))
                     ->where('code = ?',$code);
        $row = $adapter->fetchRow($select);
        return $row;
    }
    
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $recipeId = $object->getId();
        
        $no_of_ingredients = count($object->getIngredients()) + count($object->getCupboardIngredients());
        $object->setNoOfIngredients($no_of_ingredients);
        
        if($recipeId){
            $urlDb = $this->validUrlKey($recipeId);
            $this->_validKey = $urlDb; 
        }
        return parent::_beforeSave($object);
    }
    
    /*
    *This function will be called whenever any new record is added in backend and will generate seo url 
    */
    protected function _afterSave(Mage_Core_Model_Abstract $object)   
    {   
        $this->_processCategories($object);
        $this->_processIngredients($object);
        $this->_processCupboardIngredients($object);
        $this->_processCuisineType($object);
        $this->_processCookingMethod($object);
        $this->_processSpecialDietTags($object);
        
        $recipeId = $object->getId();

        if((empty($this->_validKey)) || (!$this->_isExistsUrlRewrite()) || ($this->_validKey != $object->getUrlKey()))
        {
            $identifier = $object->getName();
            if (!empty($identifier))
            {
                $module_name = 'recipe';
                $pathData = Mage::helper('recipe')->getRequestPath($module_name,$object);
                if(array_key_exists('1',$pathData)){
                    $urlKey = $pathData[1];
                    $adapter = $this->_getWriteAdapter();
                    $where = array(
                    'recipe_id = ?'=> (int)$recipeId
                    );
                    $bind  = array('url_key' => $urlKey);
                    $adapter->update($this->_RecipeTable, $bind, $where);
                }        
                
                Mage::helper('recipe')->handleUrlRewrite($module_name,$object,$pathData[0]);

                //return parent::_afterSave($object); 
            }
        }
        return parent::_afterSave($object);
    }
    
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)  
    {
        /**
        * The url rewrite objects must also be deleted.
        */
        $recipeId = $object->getId();
        $module_name = 'recipe';
        Mage::helper('recipe')->deleteUrlRewrites($recipeId,$module_name);
        
        $condition = array(
            'recipe_id = ?'     => (int) $object->getId(),
        );

        $this->_getWriteAdapter()->delete($this->getTable('recipe/recipe_category'), $condition);
        $this->_getWriteAdapter()->delete($this->getTable('recipe/recipe_ingredients'), $condition);
        $this->_getWriteAdapter()->delete($this->getTable('recipe/recipe_cupboard'), $condition);
        $this->_getWriteAdapter()->delete($this->getTable('recipe/recipe_cuisine'), $condition);
        $this->_getWriteAdapter()->delete($this->getTable('recipe/recipe_cookingmethod'), $condition);
        $this->_getWriteAdapter()->delete($this->getTable('recipe/recipe_dietary'), $condition);
        
        return parent::_beforeDelete($object);  
    }

    /**
    This function is created to validate the URL Title for Brand module
    */
    public function validUrlKey($recipeId){
        $readAdapter = $this->_getReadAdapter();
        $select  = $readAdapter->select()
        ->from($this->_RecipeTable, 'url_key')
        ->where('recipe_id='.$recipeId);

        return $readAdapter->fetchOne($select);
    }
    
    protected function _processCategories(Mage_Core_Model_Abstract $object){
        
        $oldCategories = $this->lookupCategoryIds($object->getId());
        $newCategories = (array)$object->getCategories(); 
        if(count($newCategories) > 0)
        {
            $table  = $this->getTable('recipe/recipe_category');
            $insert = array_diff($newCategories, $oldCategories);
            $delete = array_diff($oldCategories, $newCategories);
            
            if ($delete) {
                $where = array(
                    'recipe_id = ?'     => (int) $object->getId(),
                    'recipe_category_id IN (?)' => $delete
                );

                $this->_getWriteAdapter()->delete($table, $where);
            }

            if ($insert) {
                $data = array();

                foreach ($insert as $catId) {
                    $data[] = array(
                        'recipe_id'  => (int) $object->getId(),
                        'recipe_category_id' => (int) $catId
                    );
                }
                
                $this->_getWriteAdapter()->insertMultiple($table, $data);
            }
        }
    } 
    
    protected function _processIngredients(Mage_Core_Model_Abstract $object){
        
        $oldIngredients = $this->lookupIngredientIds($object->getId());
        $newIngredients = (array)$object->getIngredients(); 

        if(count($newIngredients)>0){
            $table  = $this->getTable('recipe/recipe_ingredients');
            $insert = array_diff($newIngredients, $oldIngredients);
            $delete = array_diff($oldIngredients, $newIngredients);
            
            if ($delete) {
                $where = array(
                    'recipe_id = ?'     => (int) $object->getId(),
                    'recipe_ingredients_id IN (?)' => $delete
                );

                $this->_getWriteAdapter()->delete($table, $where);
            }

            if ($insert) {
                $data = array();

                foreach ($insert as $catId) {
                    $data[] = array(
                        'recipe_id'  => (int) $object->getId(),
                        'recipe_ingredients_id' => (int) $catId
                    );
                }
                
                $this->_getWriteAdapter()->insertMultiple($table, $data);
            }
        }
    } 
    
    protected function _processCupboardIngredients(Mage_Core_Model_Abstract $object){
        
        $oldCupboardIngredients = $this->lookupCupboardIngredientsIds($object->getId());
        $newCupboardIngredients = (array)$object->getCupboardIngredients(); 

        if(count($newCupboardIngredients)>0){
        
            $table  = $this->getTable('recipe/recipe_cupboard');
            $insert = array_diff($newCupboardIngredients, $oldCupboardIngredients);
            $delete = array_diff($oldCupboardIngredients, $newCupboardIngredients);
            
            if ($delete) {
                $where = array(
                    'recipe_id = ?'     => (int) $object->getId(),
                    'recipe_cupboard_id IN (?)' => $delete
                );

                $this->_getWriteAdapter()->delete($table, $where);
            }

            if ($insert) {
                $data = array();

                foreach ($insert as $catId) {
                    $data[] = array(
                        'recipe_id'  => (int) $object->getId(),
                        'recipe_cupboard_id' => (int) $catId
                    );
                }
                
                $this->_getWriteAdapter()->insertMultiple($table, $data);
            }
        }
    }
    
    protected function _processCuisineType(Mage_Core_Model_Abstract $object){
        
        $oldCuisineType = $this->lookupCuisineTypeIds($object->getId());
        $newCuisineType = (array)$object->getCuisineType(); 

        if(count($newCuisineType)>0){
        
            $table  = $this->getTable('recipe/recipe_cuisine');
            $insert = array_diff($newCuisineType, $oldCuisineType);
            $delete = array_diff($oldCuisineType, $newCuisineType);
            
            if ($delete) {
                $where = array(
                    'recipe_id = ?'     => (int) $object->getId(),
                    'recipe_cuisine_id IN (?)' => $delete
                );

                $this->_getWriteAdapter()->delete($table, $where);
            }

            if ($insert) {
                $data = array();

                foreach ($insert as $catId) {
                    $data[] = array(
                        'recipe_id'  => (int) $object->getId(),
                        'recipe_cuisine_id' => (int) $catId
                    );
                }
                
                $this->_getWriteAdapter()->insertMultiple($table, $data);
            }
        }
    }
    
    protected function _processCookingMethod(Mage_Core_Model_Abstract $object){
        
        $oldCookingMethod = $this->lookupCookingMethodIds($object->getId());
        $newCookingMethod = (array)$object->getCookingMethod(); 
        
        if(count($newCookingMethod)>0){

            $table  = $this->getTable('recipe/recipe_cookingmethod');
            $insert = array_diff($newCookingMethod, $oldCookingMethod);
            $delete = array_diff($oldCookingMethod, $newCookingMethod);
            
            if ($delete) {
                $where = array(
                    'recipe_id = ?'     => (int) $object->getId(),
                    'cooking_id IN (?)' => $delete
                );

                $this->_getWriteAdapter()->delete($table, $where);
            }

            if ($insert) {
                $data = array();

                foreach ($insert as $catId) {
                    $data[] = array(
                        'recipe_id'  => (int) $object->getId(),
                        'cooking_id' => (int) $catId
                    );
                }
                
                $this->_getWriteAdapter()->insertMultiple($table, $data);
            }
        }
    }
    
    protected function _processSpecialDietTags(Mage_Core_Model_Abstract $object){
        
        $oldSpecialDietTags = $this->lookupSpecialDietTagIds($object->getId());
        $newSpecialDietTags = (array)$object->getSpecialDietaryTags(); 

        if(count($newSpecialDietTags)>0){
            
            $table  = $this->getTable('recipe/recipe_dietary');
            $insert = array_diff($newSpecialDietTags, $oldSpecialDietTags);
            $delete = array_diff($oldSpecialDietTags, $newSpecialDietTags);
            
            if ($delete) {
                $where = array(
                    'recipe_id = ?'     => (int) $object->getId(),
                    'special_diet_tag IN (?)' => $delete
                );

                $this->_getWriteAdapter()->delete($table, $where);
            }

            if ($insert) {
                $data = array();

                foreach ($insert as $tag) {
                    $data[] = array(
                        'recipe_id'  => (int) $object->getId(),
                        'special_diet_tag' => $tag
                    );
                }
                
                $this->_getWriteAdapter()->insertMultiple($table, $data);
            }
        }
    }
    
    public function lookupCategoryIds($id){            
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('recipe/recipe_category'), 'recipe_category_id')
            ->where('recipe_id = :recipe_id');

        $binds = array(
            ':recipe_id' => (int) $id
        );
                                                  
        return $adapter->fetchCol($select, $binds);
    }
    
    public function lookupIngredientIds($id){            
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('recipe/recipe_ingredients'), 'recipe_ingredients_id')
            ->where('recipe_id = :recipe_id');

        $binds = array(
            ':recipe_id' => (int) $id
        );
                                                  
        return $adapter->fetchCol($select, $binds);
    }
    
    public function lookupCupboardIngredientsIds($id){            
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('recipe/recipe_cupboard'), 'recipe_cupboard_id')
            ->where('recipe_id = :recipe_id');

        $binds = array(
            ':recipe_id' => (int) $id
        );
                                                  
        return $adapter->fetchCol($select, $binds);
    }
    
    public function lookupCuisineTypeIds($id){            
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('recipe/recipe_cuisine'), 'recipe_cuisine_id')
            ->where('recipe_id = :recipe_id');

        $binds = array(
            ':recipe_id' => (int) $id
        );
                                                  
        return $adapter->fetchCol($select, $binds);
    }
    
    public function lookupCookingMethodIds($id){            
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('recipe/recipe_cookingmethod'), 'cooking_id')
            ->where('recipe_id = :recipe_id');

        $binds = array(
            ':recipe_id' => (int) $id
        );
                                                  
        return $adapter->fetchCol($select, $binds);
    }
    
    public function lookupSpecialDietTagIds($id){            
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('recipe/recipe_dietary'), 'special_diet_tag')
            ->where('recipe_id = :recipe_id');

        $binds = array(
            ':recipe_id' => (int) $id
        );                                                  
        return $adapter->fetchCol($select, $binds);
    }
    
    public function getAllSpecialDietTags(){
        $adapter = $this->_getReadAdapter();   
        
        $select  = "SELECT DISTINCT special_diet_tag FROM {$this->getTable('recipe/recipe_dietary')}";
        
        return $adapter->fetchCol($select);
    }
    
    public function getMinIngredientsCount(){
        $adapter = $this->_getReadAdapter();   
        $select  = "SELECT MIN(no_of_ingredients) FROM {$this->getTable('recipe/recipe')}";
        return $adapter->fetchOne($select);
    }
    
    public function getMaxIngredientsCount(){
        $adapter = $this->_getReadAdapter();   
        $select  = "SELECT MAX(no_of_ingredients) FROM {$this->getTable('recipe/recipe')}";
        return $adapter->fetchOne($select);
    }
    
    protected function _isExistsUrlRewrite(){
        $url_rewrite = Mage::getModel('core/url_rewrite')->getCollection()->addFieldToFilter('request_path',$this->_validKey.'.html');
        if($url_rewrite->count()>0)
            return true;
        return false;
    }
}
