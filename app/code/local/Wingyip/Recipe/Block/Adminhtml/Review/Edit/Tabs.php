<?php
class Wingyip_Recipe_Block_Adminhtml_Review_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
 
    public function __construct()
    {
        parent::__construct();
        $this->setId('review_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('recipe')->__('Review Detail'));
    }
 
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('recipe')->__('Review Detail'),
            'title'     => Mage::helper('recipe')->__('Review Detail'),
            'content'   => $this->getLayout()->createBlock('recipe/adminhtml_review_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}
