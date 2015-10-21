<?php



class Wingyip_Importproducts_Block_Adminhtml_Importproducts_Edit extends Mage_Adminhtml_Block_Widget_Form_Container

{

    public function __construct()

    {

        parent::__construct();

                 

        $this->_objectId = 'id';

        $this->_blockGroup = 'importproducts';

        $this->_controller = 'adminhtml_importproducts';

        

        $this->_updateButton('save', 'label', Mage::helper('importproducts')->__('Save Importproducts File'));

        $this->_updateButton('delete', 'label', Mage::helper('importproducts')->__('Delete Importproducts File'));

		

        $this->_addButton('saveandcontinue', array(

            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),

            'onclick'   => 'saveAndContinueEdit()',

            'class'     => 'save',

        ), -100);



        $this->_formScripts[] = "

            function toggleEditor() {

                if (tinyMCE.getInstanceById('importproducts_content') == null) {

                    tinyMCE.execCommand('mceAddControl', false, 'importproducts_content');

                } else {

                    tinyMCE.execCommand('mceRemoveControl', false, 'importproducts_content');

                }

            }



            function saveAndContinueEdit(){

                editForm.submit($('edit_form').action+'back/edit/');

            }

        ";

    }



    public function getHeaderText()

    {

        if( Mage::registry('importproducts_data') && Mage::registry('importproducts_data')->getId() ) {

            return Mage::helper('importproducts')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('importproducts_data')->getTitle()));

        } else {

            return Mage::helper('importproducts')->__('Add Importproducts File');

        }

    }

}