<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Review
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review form block
 *
 * @category   Mage
 * @package    Mage_Review
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Wingyip_Recipe_Block_Review_List extends Wingyip_Recipe_Block_View
{

    public function getRating()
    {
		$recipeId = $this->getRequest()->getParam('id',0);
        if (!Mage::registry('recipe') && $recipeId) {
            $recipe = Mage::getModel('recipe/recipe')->load($recipeId);
            Mage::register('recipe', $recipe);
        }
        $recipe= Mage::registry('recipe');
		
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

    public function getRatingCount()
    {
       $recipeId = $this->getRequest()->getParam('id',0);
        if (!Mage::registry('recipe') && $recipeId) {
            $recipe = Mage::getModel('recipe/recipe')->load($recipeId);
            Mage::register('recipe', $recipe);
        }
        $recipe= Mage::registry('recipe');
		
        $recipeCollection = Mage::getModel('recipe/review')->getCollection()
				->addFieldToFilter('recipe_id',$recipeId)
				->addFieldToFilter('status','2');
		
		return $recipeCollection->getSize();
				
    }
	
	public function getReviewsCollection(){
		$recipeId = $this->getRequest()->getParam('id',0);
        if (!Mage::registry('recipe') && $recipeId) {
            $recipe = Mage::getModel('recipe/recipe')->load($recipeId);
            Mage::register('recipe', $recipe);
        }
        $recipe= Mage::registry('recipe');
		$recipereviewDescr  = Mage::getSingleton('core/resource')->getTableName('recipe/recipe_reviewdescr');
		
        $recipeCollection = Mage::getModel('recipe/review')->getCollection()
				->addFieldToFilter('main_table.recipe_id',$recipeId)
				->addFieldToFilter('status','2');
	    $recipeCollection->getSelect()->join(array('descr' => $recipereviewDescr), "descr.review_id = main_table.review_id",array('descr.*'));// and main.recipe_id=".$recipeId);				
		
		return $recipeCollection;
	}

   
}
