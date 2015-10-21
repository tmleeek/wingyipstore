<?php
class Ecommage_Adminshippingprice_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getLabelMatrixRate($shippingSku){
        if($shippingSku != ''){
            $resource = Mage::getSingleton('core/resource');

            /**
             * Retrieve the read connection
             */
            $readConnection = $resource->getConnection('core_read');

            $query = 'SELECT * FROM ' . $resource->getTableName('productmatrix_shipping/productmatrix'). ' where shipping_sku = '.$shippingSku.' LIMIT 1';

            /**
             * Execute the query and store the results in $results
             */
            $results_query = $readConnection->fetchAll($query);
            return $results_query;
        }
    }
}
	 