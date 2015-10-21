<?php
/**
 * @category    Bubble
 * @package     Bubble_CodeReview
 * @version     1.1.0
 * @copyright   Copyright (c) 2013 BubbleCode (http://shop.bubblecode.net)
 */
class Bubble_CodeReview_DebugController extends Mage_Core_Controller_Front_Action
{
    public function infoAction()
    {
        /** @var $helper Bubble_CodeReview_Helper_Data */
        $helper = Mage::helper('bubble_codereview');
        $data = array(
            'php' => array(
                'version'   => phpversion(),
                'sapi'      => php_sapi_name(),
            ),
            'apc' => array(
                'enabled'   => extension_loaded('apc') ? ini_get('apc.enabled') : false,
                'stat'      => extension_loaded('apc') ? ini_get('apc.stat') : false,
                'shm_size'  => extension_loaded('apc') ? ini_get('apc.shm_size') : false,
            ),
            'magento' => array(
                'version'   => Mage::getVersion(),
                'compiler'  => defined('COMPILER_INCLUDE_PATH'),
            ),
            'mysql' => $helper->getMysqlInfo(),
        );

        $helper->sendResponseJSON($this, $data);
    }
}