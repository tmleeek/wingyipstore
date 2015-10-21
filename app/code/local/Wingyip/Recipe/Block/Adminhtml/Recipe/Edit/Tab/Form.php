<?php
class Wingyip_Recipe_Block_Adminhtml_Recipe_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array('add_variables' => false, 'add_widgets' => false,'files_browser_window_url'=>$this->getBaseUrl().'admin/cms_wysiwyg_images/index/'));
        
        $fieldset = $form->addFieldset('recipe_form', array('legend'=>Mage::helper('recipe')->__('Recipe Information')));
       
        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('recipe')->__('Name'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
        ));
                
        $fieldset->addField('code', 'text', array(
            'label'     => Mage::helper('recipe')->__('Code'),
            'required'  => true,
            'name'      => 'code',
        ));
        
        $fieldset->addField('url_key', 'text', array(
            'label'     => Mage::helper('recipe')->__('Url Key'),
            'name'      => 'url_key',
        ));       
        
        $fieldset->addField('sort', 'text', array(
            'label'     => Mage::helper('recipe')->__('Sort'),
            'class'     => 'validate-number',
            'name'      => 'sort',
        ));
        
        $fieldset->addField('description', 'editor', array(
            'label'     => Mage::helper('recipe')->__('Description'),
            'name'      => 'description',
            'style'     => 'width:98%; height:200px;',
            'state'     => 'html',
            'wysiwyg'   => true,
            'required'  => false,
            'config'    => $wysiwygConfig,
        ));
        
        $fieldset->addField('short_description', 'editor', array(
            'label'     => Mage::helper('recipe')->__('Short Description'),
            'name'      => 'short_description',
            'style'     => 'width:98%; height:200px;',
            'state'     => 'html',
            'wysiwyg'   => true,
            'required'  => false,
            'config'    => $wysiwygConfig,
        ));
        
        $fieldset->addField('ingredients_description', 'editor', array(
            'label'     => Mage::helper('recipe')->__('Ingredients'),
            'name'      => 'ingredients_description',
            'style'     => 'width:98%; height:200px;',
            'state'     => 'html',
            'wysiwyg'   => true,
            'required'  => false,
            'config'    => $wysiwygConfig,
        ));
        
        $fieldset->addField('cooking_time', 'text', array(
            'label'     => Mage::helper('recipe')->__('Cooking Time'),
            'name'      => 'cooking_time',
        ));
            
        $fieldset->addField('special_dietary_tags', 'textarea', array(
            'label'     => Mage::helper('recipe')->__('Special Dietary Tags'),
            'name'      => 'special_dietary_tags',
            'after_element_html' => '<p class="nm"><small>Comma separated tag entry</small><p>',
        )); 
        
        $fieldset->addField('course', 'select', array(
            'label'     => Mage::helper('recipe')->__('Course'),
            'name'      => 'course',
            'values'    => Mage::helper('recipe')->getCourseData(),
        ));
        
        $fieldset->addField('occasion', 'select', array(
            'label'     => Mage::helper('recipe')->__('Occasion'),
            'name'      => 'occasion',
            'values'    => Mage::helper('recipe')->getOccasionData(),
        ));
        
        $fieldset->addField('serving_size', 'text', array(
            'label'     => Mage::helper('recipe')->__('Serving Size'),
            'class'     => 'validate-number',
            'name'      => 'serving_size',
        ));
        
        $fieldset->addField('heat_spice_level', 'text', array(
            'label'     => Mage::helper('recipe')->__('Heat/Spice Level'),
            'class'     => 'validate-number',
            'name'      => 'heat_spice_level',
        ));
        
        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('recipe')->__('Status'),
            'name'      => 'status',
            'required'  => true,
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('recipe')->__('Active'),
                ),
                array(
                    'value'     => 2,
                    'label'     => Mage::helper('recipe')->__('Inactive'),
                ),
            ),
            
        ));
        
        if ( Mage::getSingleton('adminhtml/session')->getRecipeData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getRecipeData());
            Mage::getSingleton('adminhtml/session')->setRecipeData(null);
        } elseif ( Mage::registry('recipe_data') ) {
            $recipe_data = Mage::registry('recipe_data')->getData();
            $tags = Mage::getResourceModel('recipe/recipe')->lookupSpecialDietTagIds(Mage::registry('recipe_data')->getId());
            $recipe_data['special_dietary_tags'] = implode(',',$tags);
            $form->setValues($recipe_data);
        }
        return parent::_prepareForm();
    }
}