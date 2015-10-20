<?php



class Wingyip_Importproducts_Block_Adminhtml_Importproducts_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs

{



  public function __construct()

  {

      parent::__construct();

      $this->setId('importproducts_tabs');

      $this->setDestElementId('edit_form');

      $this->setTitle(Mage::helper('importproducts')->__('Importproducts Information'));

  }



  protected function _beforeToHtml()

  {

      $this->addTab('form_section', array(

          'label'     => Mage::helper('importproducts')->__('Importproducts Information'),

          'title'     => Mage::helper('importproducts')->__('Importproducts Information'),

          'content'   => $this->getLayout()->createBlock('importproducts/adminhtml_importproducts_edit_tab_form')->toHtml(),

      ));

     

      return parent::_beforeToHtml();

  }

}