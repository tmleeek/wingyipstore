<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Block_Form_Element_Renderer extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
    protected function _construct()
    {
        $this->setTemplate('aitcore/fieldset/element.phtml');
    }
    
    /**
     * @return Aitoc_Aitsys_Model_Module
     */
    public function getModule()
    {
        return $this->getElement()->getModule();
    }
}
