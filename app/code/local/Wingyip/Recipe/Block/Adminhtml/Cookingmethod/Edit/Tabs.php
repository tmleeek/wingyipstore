<?php
class Wingyip_Recipe_Block_Adminhtml_Cookingmethod_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
 
    public function __construct()
    {
        parent::__construct();
        $this->setId('cookingmethod_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('recipe')->__('Cookimg Method Detail'));
    }
 
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('recipe')->__('Cookimg Method Detail'),
            'title'     => Mage::helper('recipe')->__('Cookimg Method Detail'),
            'content'   => $this->getLayout()->createBlock('recipe/adminhtml_cookingmethod_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}
