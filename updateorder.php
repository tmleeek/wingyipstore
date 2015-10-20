<?php
require 'app/Mage.php';
umask(0);
Mage::app();

$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
$sql = "SELECT entity_id FROM " . Mage::getSingleton('core/resource')->getTableName('sales/order') . " WHERE (status IN('processing'))";
$orderIds = $connection->fetchCol($sql);
if ($orderIds) {
    $getOrder=Mage::getModel('sales/order');
    foreach ($orderIds as $orderId) {
        $getOrder = $getOrder->load($orderId);
        if ($getOrder->getState() == 'payment_review') {
            $getOrder->setState('processing');
            $result = $getOrder->save();
            if($result){
                echo $getOrder->getIncrementId().'----Success<br>';
            }else{
                echo $getOrder->getIncrementId().'----Failed<br>';
            }
        }else{
            echo $getOrder->getIncrementId().'----Not in State Payment_Review<br>';
        }
    }
}else{
    echo $getOrder->getIncrementId().'----Not exist Entity ID<br>';
}

