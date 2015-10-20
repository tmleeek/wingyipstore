<?php
ini_set('memory_limit', -1);
require_once "../app/Mage.php";
umask(0);
Mage::app();
$storeId=0;
Mage::app()->setCurrentStore($storeId);
$csvFile = 'updatename_desctiptions.csv';
$csvObj = new Varien_File_Csv();
$data = $csvObj->getData($csvFile);

$i = 0;

foreach ($data as $k=>$csv) {
	$i++;
	if($i <=0){
		continue;
	}
	try {
		Mage::log('------------------Update Name and Descriptions-------------------------');
		if($csv[0]){
			$product=Mage::getModel('catalog/product')->getIdBySku($csv[0]);
			if($product){
				$product=Mage::getModel('catalog/product')->load($product);
				if($product->getId() && $product->getEntityId()){
					//Update Name
					$success='';
					if($csv[1]){
						$product->setName($csv[1]);
						$success='Name: '.$csv[1]."\n";
					}
					//Update descriptions
					if($csv[2]){
						$product->setDescription($csv[2]);
						$success.='Description: '.$csv[2]."\n";
					}
					//Update Short_Descriptions
					if($csv[3]){
						$product->setShortDescription($csv[3]);
						$success.='ShortDescription: '.$csv[2]."\n";
					}
					$product->save();
					echo $i.'----SUCCESS---'."\n Sku:--".$csv[0]."\n".$success."\n".'---END SUCCESS'."\n";
					Mage::log($i.'----SUCCESS---'."\n");
					Mage::log($success."\n");
					Mage::log($i.'----END SUCCESS---'."\n");
				}
			}else{
				Mage::log('--Error Get Model--: Row'.$i."\n");
				continue;
			}
		}else{
			Mage::log('--NO SKU--: Row'.$i."\n");
			continue;
		}
		unset($product);
	} catch (Exception $e) {
		Mage::log('----------------Exception Error---------');
		Mage::log($e->getMessage()."\n");
		echo $i.'--ERROR--'.'Sku: '.$csv[0] ."----Url Key: ".$csv[1]. "\n";
		Mage::log('----------------End Exception Error---------');
		continue;
	}

}
