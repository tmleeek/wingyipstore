<?php
/**
 * SF9_Realex extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   SF9
 * @package    SF9_Realex
 * @copyright  Copyright (c) 2011 StudioForty9
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   SF9
 * @package    SF9_Realex
 * @author     Alan Morkan <alan@sf9.ie>
 */
class SF9_Realex_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @param $orderIncrementId
     * @return string
     */
    public function createInvoice(string $orderIncrementId){
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $itemsQty = count($order->getAllItems());
        $invoice = $order->prepareInvoice($itemsQty);
        $invoice->register();
        $invoice->setOrder($order);
        $invoice->setEmailSent(true);
        $invoice->getOrder()->setIsInProcess(true);
        $invoice->pay();
        $invoice->save();
        $order->save();
        return $invoice->getIncrementId();
    }

    public function getDateFromTimestamp($timestamp){
        $year = substr($timestamp, 0, 4);
        $month = substr($timestamp, 4, 2);
        $day = substr($timestamp, 6, 2);
        $hour = substr($timestamp, 8, 2);
        $minutes = substr($timestamp, 10, 2);
        $seconds = substr($timestamp, 12, 2);
        $date = $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minutes . ':' . $seconds;
        Mage::log($date);
        return strtotime($date);
    }
}


