<?php
class Wingyip_Recipe_Model_Mysql4_Recipe_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct(){
     
        //parent::__construct();
        $this->_init('recipe/recipe');
    } 
    
    public function addCategoryFilter($ids){ 
        $this->getSelect()
            ->joinInner(
                array('rc' => $this->getTable('recipe/recipe_category')),
                join(' AND ', array(
                    'rc.recipe_id = main_table.recipe_id',
                ))
            )
            ->joinInner(
                array('rmc' => $this->getTable('recipe/category')),
                join(' AND ', array(
                    'rc.recipe_category_id = rmc.recipe_category_id'
                )),array('rmc.recipe_category_id')
            )
            ->where('rc.recipe_category_id IN(?)', $ids) ;
        return $this; 
    }
    
    public function addIngredientFilter($ids){
       $this->getSelect()
            ->joinInner(
                array('ri' => $this->getTable('recipe/recipe_ingredients')),
                join(' AND ', array(
                    'ri.recipe_id = main_table.recipe_id',
                ))
            )
            ->joinInner(
                array('rmi' => $this->getTable('recipe/ingredient')),
                join(' AND ', array(
                    'ri.recipe_ingredients_id = rmi.recipe_ingredients_id'
                ))
            )
            ->where('ri.recipe_ingredients_id IN(?)', $ids) ;
        return $this;
    }
    
    public function addCupboardIngredientFilter($ids){
        $this->getSelect()
            ->joinInner(
                array('rci' => $this->getTable('recipe/recipe_cupboard')),
                join(' AND ', array(
                    'rci.recipe_id = main_table.recipe_id',
                ))
            )
            ->joinInner(
                array('rmci' => $this->getTable('recipe/cupboard')),
                join(' AND ', array(
                    'rci.recipe_cupboard_id = rmci.recipe_cupboard_id'
                ))
            )
            ->where('rci.recipe_cupboard_id IN(?)', $ids) ;
        return $this;
    }
    
    public function addCookingMethodFilter($ids){
        $this->getSelect()
            ->joinInner(
                array('rcm' => $this->getTable('recipe/recipe_cookingmethod')),
                join(' AND ', array(
                    'rcm.recipe_id = main_table.recipe_id',
                ))
            )
            ->joinInner(
                array('rmcm' => $this->getTable('recipe/cookingmethod')),
                join(' AND ', array(
                    'rcm.cooking_id = rmcm.cooking_id'
                ))
            )
            ->where('rcm.cooking_id IN(?)', $ids) ;
        return $this;
    }
    
    public function addCuisineTypeFilter($ids){
        $this->getSelect()
            ->joinInner(
                array('rct' => $this->getTable('recipe/recipe_cuisine')),
                join(' AND ', array(
                    'rct.recipe_id = main_table.recipe_id',
                ))
            )
            ->joinInner(
                array('rmct' => $this->getTable('recipe/cuisine')),
                join(' AND ', array(
                    'rct.recipe_cuisine_id = rmct.recipe_cuisine_id'
                ))
            )
            ->where('rct.recipe_cuisine_id IN(?)', $ids) ;
        return $this;
    }
    
    public function addSpecialDietTagFilter($tag){
        $this->getSelect()
            ->joinInner(
                array('rsdt' => $this->getTable('recipe/recipe_dietary')),
                join(' AND ', array(
                    'rsdt.recipe_id = main_table.recipe_id',
                ))
            )
            ->where('rsdt.special_diet_tag LIKE ?', $tag) ;
        return $this;
    }
}
