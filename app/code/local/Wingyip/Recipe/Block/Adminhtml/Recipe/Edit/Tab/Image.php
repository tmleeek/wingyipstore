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
 * Poll edit form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Wingyip_Recipe_Block_Adminhtml_Recipe_Edit_Tab_Image extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('recipe/edit/options.phtml');
    }
	
	protected function _prepareLayout()
    {
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('recipe')->__('Add New Image'),
                    'class' => 'add',
                    'id'    => 'add_new_defined_image'
                ))
        );
		
		$this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete Image'),
                    'class' => 'delete delete-image '
                ))
        );
        
        /*$this->setChild('delete_permanent',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Delete Image'),
                    'class'     => 'delete delete-image ' ,
                    'onclick'   => 'deleteImage()',
                    
                ))  
        );   */     


        /*$this->setChild('options_box',
        	  $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_options_option')
        );*/

        return parent::_prepareLayout();
    }
	
	public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }
    
   /*public function getPermanentDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_permanent');
    } */

  	/**
     * Retrieve HTML of add button
     *
     * @return string
     */
    public function getAddNewButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
	
	public function getRecipeImage()
    {
		$recipeId = $this->getRequest()->getParam('id');
		$collection=Mage::getModel('recipe/image')->getCollection()
					->addFieldToFilter('recipe_id',$recipeId);
					
        return $collection;
    }
    
}
