<?php
class Wingyip_Recipe_Block_Adminhtml_Category_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
 
    public function __construct()
    {
        parent::__construct();
        $this->setId('category_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('recipe')->__('Category Detail'));
    }
 
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('recipe')->__('Category Detail'),
            'title'     => Mage::helper('recipe')->__('Category Detail'),
            'content'   => $this->getLayout()->createBlock('recipe/adminhtml_category_edit_tab_form')->toHtml(),
        ));
		
		$this->addTab('meta_section', array(
            'label'     => Mage::helper('recipe')->__('Meta Information'),
            'title'     => Mage::helper('recipe')->__('Meta Information'),
            'content'   => $this->getLayout()->createBlock('recipe/adminhtml_category_edit_tab_meta')->toHtml()
        ));
        return parent::_beforeToHtml();
    }
}
