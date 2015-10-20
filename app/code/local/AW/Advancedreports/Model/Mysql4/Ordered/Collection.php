<?php

class AW_Advancedreports_Model_Mysql4_Ordered_Collection extends Mage_Reports_Model_Mysql4_Product_Ordered_Collection
{
    //It fix magento sstore filter bug 8p
    public function setStoreIds($storeIds)
    {
        $storeIds = array_values($storeIds);
        if ($this->getAwStoreIds($storeIds)) {
            $this->getSelect()->where('order.store_id in (?)', $this->getAwStoreIds($storeIds));
        }
        return parent::setStoreIds($storeIds);
    }

    public function getAwStoreIds($ids = array())
    {
        if (count($ids)) {
            $res = '';
            $isFirst = true;
            foreach ($ids as $id) {
                $res .= $isFirst ? $id : "," . $id;
                $isFirst = false;
            }
            return $res;
        }
        return '';
    }

    public function addOrderedQty($from = '', $to = '')
    {
        $filterField = Mage::helper('advancedreports')->confOrderDateFilter();

        $qtyOrderedTableName = $this->getTable('sales/order_item');
        $qtyOrderedFieldName = 'qty_ordered';

        $productIdTableName = $this->getTable('sales/order_item');
        $productIdFieldName = 'product_id';

        $productTypes = " AND (e.type_id <> '" . Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE . "' )";

        if ($from != '' && $to != '') {
            $dateFilter = " AND `order`.{$filterField} BETWEEN '{$from}' AND '{$to}'";
        } else {
            $dateFilter = "";
        }

        $productEntityConditions = array(
            "e.entity_id = order_items.{$productIdFieldName}",
            "e.entity_type_id = {$this->getProductEntityTypeId()}{$productTypes}",
        );
        $this->getSelect()->reset()
            ->from(
                array('order_items' => $qtyOrderedTableName),
                array('ordered_qty' => "SUM(order_items.{$qtyOrderedFieldName})")
            )
            ->joinInner(
                array('order' => $this->getTable('sales/order')),
                'order.entity_id = order_items.order_id' . $dateFilter,
                array()
            )
            ->joinInner(
                array('e' => $this->getProductEntityTableName()),
                join(' AND ', $productEntityConditions)
            )
            ->group('e.entity_id')
            ->having('ordered_qty > 0');
        return $this;
    }
}