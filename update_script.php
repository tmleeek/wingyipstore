<?php
require_once "app/Mage.php";
umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);


    
$productCollection = Mage::getModel("catalog/product")->getCollection();
$count = 0;
foreach ($productCollection as $product) {
    try {
    $count++;
    if ($count < 3010) {
        continue;
    }
	$product = Mage::getModel("catalog/product")->load($product->getEntityId());
    $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
    $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
	$stockItem->setData('is_in_stock', 1);
    $stockItem->setData('qty', 0);
    $stockItem->setData('manage_stock', 1);
	$stockItem->save();
	$product->save();
    // echo $i++ . '_';
    unset($product);
    // unset($stockItem);
    } catch(Exception $ex) {
        $product->delete();
        // echo $product->getSku() . ' - ';
    }
}


echo ('Update successfully');
