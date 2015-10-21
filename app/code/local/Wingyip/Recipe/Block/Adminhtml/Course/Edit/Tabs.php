<?php
class Wingyip_Recipe_Block_Adminhtml_Course_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
 
    public function __construct()
    {
        parent::__construct();
        $this->setId('course_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('recipe')->__('Course Detail'));
    }
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('recipe')->__('Course Detail'),
            'title'     => Mage::helper('recipe')->__('Course Detail'),
            'content'   => $this->getLayout()->createBlock('recipe/adminhtml_course_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}
