<?php
class Wingyip_Importproducts_Block_Adminhtml_Importproducts_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('importproducts_form', array('legend'=>Mage::helper('importproducts')->__('Importproducts information')));

        if(!$this->getRequest()->getParam('id')){
            $fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('importproducts')->__('File Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
            ));

            $fieldset->addField('filename', 'file', array(
            'label'     => Mage::helper('importproducts')->__('File'),
            'required'  => false,
            'name'      => 'filename',
            ));
        }

        $fieldset->addField('status', 'select', array(
        'label'     => Mage::helper('importproducts')->__('Status'),
        'name'      => 'status',
        'values'    => array(
            array(
            'value'     => 1,
            'label'     => Mage::helper('importproducts')->__('Pending'),
            ),
            array(
            'value'     => 2,
            'label'     => Mage::helper('importproducts')->__('Processing'),
            ),
            array(
            'value'     => 3,
            'label'     => Mage::helper('importproducts')->__('Success'),
            ),
            array(
            'value'     => 4,
            'label'     => Mage::helper('importproducts')->__('Fail'),
            ))
        ));

        if(Mage::getSingleton('adminhtml/session')->getGroupimportData()){
            $fieldset->addField('log_file', 'editor', array(
            'name'      => 'log_file',
            'label'     => Mage::helper('importproducts')->__('Download Log File'),
            'required'  => false,
            ));  
        }

        /*$fieldset->addField('content', 'editor', array(
        'name'      => 'content',
        'label'     => Mage::helper('groupimport')->__('Content'),
        'title'     => Mage::helper('groupimport')->__('Content'),
        'style'     => 'width:700px; height:500px;',
        'wysiwyg'   => false,
        'required'  => true,
        ));*/

        if(Mage::getSingleton('adminhtml/session')->getGroupimportData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getImportproductsData());
            Mage::getSingleton('adminhtml/session')->setImportproductsData(null);
        } elseif(Mage::registry('importproducts_data')) {
            $form->setValues(Mage::registry('importproducts_data')->getData());
        }
        return parent::_prepareForm();
    }

}