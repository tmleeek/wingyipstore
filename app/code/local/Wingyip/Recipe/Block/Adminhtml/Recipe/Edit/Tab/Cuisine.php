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

class Wingyip_Recipe_Block_Adminhtml_Recipe_Edit_Tab_Cuisine extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
		
		$model = Mage::registry('recipe_data');
        $form = new Varien_Data_Form();
		$this->setForm($form);      
        
		$fieldset = $form->addFieldset('recipe_form', array('legend'=>Mage::helper('recipe')->__('Cuisine Type Information')));
        
        $fieldset->addField('recipe_cuisine_type', 'checkboxes', array(
            'label' => Mage::helper('recipe')->__('Cuisine'),
            'name' => 'cuisine_type[]',
            'checked' => Mage::getResourceModel('recipe/recipe')->lookupCuisineTypeIds($model->getId()),
            'values' => Mage::helper('recipe')->getCuisineType(), 
            'onclick' => "",
            'onchange' => "",
            'value' => '1',
            'disabled' => false,
            //'after_element_html' => 'Comments',
            'tabindex' => 1
        ));
        
       	$form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
    
}
