<?php

/**
 * Realex session namespace
 *
 * @category   Wingyip
 * @package    Wingyip_Realex
 * @author     Wingyip <info@wingyip.com>
 */
class Wingyip_Realex_Model_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct()
    {
        $this->init('realex');
    }

}