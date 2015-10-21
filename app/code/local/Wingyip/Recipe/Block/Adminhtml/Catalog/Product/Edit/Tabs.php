<?php
 
class Wingyip_Recipe_Block_Adminhtml_Catalog_Product_Edit_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    private $parent;
 
    protected function _prepareLayout()
    {
        //get all existing tabs
        $this->parent = parent::_prepareLayout();
                
        $this->addTab('recipe_section', array(
            'label'     => Mage::helper('recipe')->__('Associated Recipes'),
            'title'     => Mage::helper('recipe')->__('Associated Recipes'),
            'url'       => $this->getUrl('recipe/adminhtml_recipe/recipe', array('_current' => true)),
            'class'     => 'ajax',
        ));
                                                                              
        return $this->parent;
    }
}
