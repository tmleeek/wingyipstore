<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Other_Synchronization_Edit_Tabs_Stop extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingOtherSynchronizationEditTabsStop');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/other/synchronization/tabs/stop.phtml');
    }

    // ####################################
}
