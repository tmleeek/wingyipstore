<?php

class AW_Advancedreports_Block_System_Config_Form_Fieldset_Additional_Reports
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_dummyElement;
    protected $_fieldRenderer;
    protected $_values;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        if (count($items = Mage::getModel('advancedreports/additional_reports')->getReports())) {

            //TODO Sort by name
            foreach ($items as $item) {
                $html .= $this->_getFieldHtml(
                    $element,
                    $item->getTitle(),
                    $item->getVersion(),
                    !Mage::helper('advancedreports/additional')->getVersionCheck($item)
                );
            }
        } else {
            $html .= Mage::helper('advancedreports')->__('No Additional Reports Installed');
        }
        $html .= $this->_getFooterHtml($element);
        return $html;
    }

    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new Varien_Object(array('show_in_default' => 1, 'show_in_website' => 1));
        }
        return $this->_dummyElement;
    }

    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_fieldRenderer;
    }

    protected function _getFieldHtml($fieldset, $moduleName, $ver, $isRed = false)
    {
        $redOpen = $isRed ? '<span style="color:red;">' : '';
        $redClose = $isRed ? '</span>' : '';
        $html = '<tr><td class="label"><label>' . $redOpen . $moduleName . $redClose .
            '</label></td><td class="value">' . $redOpen . $ver . $redClose . '</td></tr>';
        return $html;
    }
}