<?php
/**
 * @category    Bubble
 * @package     Bubble_CodeReview
 * @version     1.1.0
 * @copyright   Copyright (c) 2013 BubbleCode (http://shop.bubblecode.net)
 */
class Bubble_CodeReview_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function sendResponseJSON($front, $data)
    {
        $json = json_encode($data);
        $front->getResponse()
            ->setBody($json)
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-Length', strlen($json))
            ->setHeader('Content-type', 'application/json', true)
            ->sendResponse();
        exit;
    }

    public function getMysqlInfo()
    {
        $variables = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchPairs('SHOW VARIABLES');

        return $variables;
    }
}