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
* @package     Mage_Adminhtml
* @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
* @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
* Adminhtml cms blocks grid
*
* @category   Mage
* @package    Mage_Adminhtml
* @author      Magento Core Team <core@magentocommerce.com>
*/
class Wingyip_Recipe_Block_Adminhtml_Catalog_Product_Edit_Tab_Recipe extends Mage_Adminhtml_Block_Widget_Grid
{
      /**
     * Set grid params
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('recipe_grid');
        $this->setDefaultSort('recipe_id');
        $this->setUseAjax(true);
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_product') {
            $recipeIds = $this->_getSelectedRecipes();
            if (empty($recipeIds)) {
                $recipeIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('recipe_id', array('in' => $recipeIds));
            } else {
                if($recipeIds) {
                    $this->getCollection()->addFieldToFilter('recipe_id', array('nin' => $recipeIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /*$collection = Mage::getModel('catalog/product_link')->useAssociatedLinks()
            ->getProductCollection()
            ->setProduct($this->_getProduct())
            ->addAttributeToSelect('*');*/
        $collection = Mage::getModel('recipe/recipe')->getCollection()->addFieldToFilter('status',1);
            

        if ($this->isReadonly()) {
            $recipeIds = $this->_getSelectedRecipes();
            if (empty($recipeIds)) {
                $recipeIds = array(0);
            }
            $collection->addFieldToFilter('recipe_id', array('in' => $recipeIds));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Checks when this block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        //return $this->_getRecipe()->getAssociatedReadonly();
    }

    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        if (!$this->isReadonly()) {
            $this->addColumn('in_product', array(
                'header_css_class'  => 'a-center',
                'type'              => 'checkbox',
                'name'              => 'in_product',
                'values'            => $this->_getSelectedRecipes(),
                'align'             => 'center',
                'index'             => 'recipe_id'
            ));
        }

        $this->addColumn('recipe_id', array(
            'header'    => Mage::helper('recipe')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'recipe_id'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('recipe')->__('Name'),
            'index'     => 'name'
        ));

        $this->addColumn('code', array(
            'header'    => Mage::helper('recipe')->__('Code'),
            'align'     =>'left',
            'index'     => 'code',
        ));
        
        $this->addColumn('status', array(
            'header'    => Mage::helper('recipe')->__('Status'),
            'align'     => 'left',
            'width'     => '80px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::helper('recipe')->getRecipeStatusesOptionArray(),
        ));

        $this->addColumn('qty', array(
            'header'            => Mage::helper('recipe')->__('Qty'),
            'name'              => 'qty',
            'type'              => 'number',
            'validate_class'    => 'validate-number',
            'index'             => 'qty',
            'width'             => 60,
            'editable'          => true,
          //  'edit_only'         => !$this->_getRecipe()->getId()
        ));

        return parent::_prepareColumns();
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('recipe/adminhtml_recipe/recipeGrid', array('_current' => true));
    }

    /**
     * Retrieve selected Associated products
     *
     * @return array
     */
    protected function _getSelectedRecipes()
    {
        $products = $this->getRecipeAssociated();
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedAssociatedRecipes());
        }
        return $products;
    }

    /**
     * Retrieve Associated products
     *
     * @return array
     */
    public function getSelectedAssociatedRecipes()
    {
        $products = array();
        
        $modelId = $this->getRequest()->getParam('id');

        $modelCollection= Mage::getModel('recipe/recipe')->getCollection()->addFieldToFilter('status',1); 
        
        $associated  = Mage::getSingleton('core/resource')->getTableName('recipe/associated'); 
        
        $modelCollection->getSelect()->join(array('ass' => $associated), "main_table.recipe_id = ass.recipe_id", array('ass.*'))
                ->where("product_id = ?",$modelId);
        
        foreach ($modelCollection as $recipe) {
            $products[$recipe->getRecipeId()] = array('qty' => $recipe->getQty());
        } 
        
        return $products;
    }              
}
