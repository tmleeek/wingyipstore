<?php

class AW_Advancedreports_Model_Mysql4_Cache extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('advancedreports/cache', 'entity_id');
    }
}