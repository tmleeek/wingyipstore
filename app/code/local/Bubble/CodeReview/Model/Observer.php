<?php
/**
 * @category    Bubble
 * @package     Bubble_CodeReview
 * @version     1.1.0
 * @copyright   Copyright (c) 2013 BubbleCode (http://shop.bubblecode.net)
 */
class Bubble_CodeReview_Model_Observer
{
    public function onSendResponseBefore(Varien_Event_Observer $observer)
    {
        /** @var $debug Bubble_Debug_Model_Observer */
        $debug = Mage::getSingleton('bubble_debug/observer');
        $front = $observer->getEvent()->getFront();
        if ($debug->isDebugEnabled() && $front->getRequest()->getCookie('review')) {
            Mage::helper('bubble_codereview')->sendResponseJSON($front, $debug->getDebug());
        }
    }
}