<?php
class Wingyip_Recipe_CategoryController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
            $this->loadLayout();
            $this->renderLayout();
    }
    public function viewAction()
    { 
		$recipeCatId = $this->getRequest()->getParam('category_id',0);
        if (!Mage::registry('recipe_category') && $recipeCatId) {
            $recipeCategory = Mage::getModel('recipe/category')->load($recipeCatId);
            Mage::register('recipe_category', $recipeCategory);
        }
	
	
        $this->loadLayout();
        $this->renderLayout(); 
    }
}
