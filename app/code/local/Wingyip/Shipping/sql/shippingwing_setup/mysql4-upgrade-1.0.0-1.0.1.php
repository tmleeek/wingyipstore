<?php
/**
 * Created by PHPro
 *
 * @package      DPD
 * @subpackage   Shipping
 * @category     Checkout
 * @author       PHPro (info@phpro.be)
 */
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('sales/shipment'), 'shipping_label_created', "smallint(6) null");
$installer->getConnection()->addColumn($installer->getTable('sales/shipment'), 'shipping_label_path', "varchar(255) null default ''");
$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'shipper_shipmentid', "bigint(20) null");
$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'consignment_id', "bigint(20) null");
$installer->endSetup();
