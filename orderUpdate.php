<?php
ini_set('memory_limit',-1);
require_once ("app/Mage.php");
umask(0);
Mage::app('admin');  

$orderCollection = Mage::getModel('sales/order')->getCollection()
						->addAttributeToFilter('increment_id', array('in' => array('100076934', '100076935', '100076936', '100076937', '100076938', '100076939', '100076940', '100076941', '100076942', '100076943', '100076944')));
$count = 1;

foreach ($orderCollection as $order) {
	try {
		var_dump($count . ' - ' . $order->getIncrementId());
		$order = Mage::getModel('sales/order')->load($order->getEntityId());
		$order->setUploadStatus('3');
		$order->save();
		$count++;
		unset($order);
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	
}

echo 'DONE!!!';