<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Synchronization_Policies_Abstract
    extends Ess_M2ePro_Model_Ebay_Synchronization_Abstract
{
    //####################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::POLICIES;
    }

    protected function processTask($taskPath)
    {
        return parent::processTask('Policies_'.$taskPath);
    }

    //####################################
}
