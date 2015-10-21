<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Synchronization_Edit_Form_Tabs_Revise
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Template_Synchronization_Edit_Form_Data
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateSynchronizationEditFormTabsRevise');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/synchronization/form/tabs/revise.phtml');
    }

    // ####################################

    public function getDefault()
    {
        return Mage::helper('M2ePro/View_Ebay')->isSimpleMode()
            ? Mage::getSingleton('M2ePro/Ebay_Template_Synchronization')->getReviseDefaultSettingsSimpleMode()
            : Mage::getSingleton('M2ePro/Ebay_Template_Synchronization')->getReviseDefaultSettingsAdvancedMode();
    }

    // ####################################
}
