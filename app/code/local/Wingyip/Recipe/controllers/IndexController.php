<?php
class Wingyip_Recipe_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    public function viewAction()
    { 
	
		$recipeId = $this->getRequest()->getParam('id',0);
        if (!Mage::registry('recipe') && $recipeId) {
            $recipe = Mage::getModel('recipe/recipe')->load($recipeId);
            Mage::register('recipe', $recipe);
        }
		$recipe=Mage::registry('recipe');
	
        $this->loadLayout();
		if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home', array(
                'label'=>Mage::helper('recipe')->__('Home'),
                'title'=>Mage::helper('recipe')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
            ));

            $breadcrumbsBlock->addCrumb('modulename', array('label' => 'Recipes','title'=>Mage::helper('recipe')->__('Recipes'),'link' => Mage::getUrl('recipe')));
            $breadcrumbsBlock->addCrumb('recipesname', array('label' => $recipe->getName()));
        }
        $this->renderLayout(); 
    }
	
    
    public function searchAction()
    {
        $this->loadLayout();
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home', array(
                'label'=>Mage::helper('recipe')->__('Home'),
                'title'=>Mage::helper('recipe')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
            ));

            $breadcrumbsBlock->addCrumb('modulename', array('label' => 'Recipes','title'=>Mage::helper('recipe')->__('Recipes'),'link' => Mage::getUrl('recipe')));
            $breadcrumbsBlock->addCrumb('pagename', array('label' => 'Search'));
        }
        $this->renderLayout();
    }
    
    public function advancesearchAction()
    {   
        $this->loadLayout();
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home', array(
                'label'=>Mage::helper('recipe')->__('Home'),
                'title'=>Mage::helper('recipe')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
            ));

            $breadcrumbsBlock->addCrumb('modulename', array('label' => 'Recipes','title'=>Mage::helper('recipe')->__('Recipes'),'link' => Mage::getUrl('recipe')));
            $breadcrumbsBlock->addCrumb('pagename', array('label' => 'Advanced'));
        }
        $this->renderLayout();
    }
    
    public function resultAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}