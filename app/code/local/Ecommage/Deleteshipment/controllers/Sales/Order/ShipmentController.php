<?php
require_once 'Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php';
class Ecommage_Deleteshipment_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController{


    /**
     * Remove tracking number from shipment
     */
    public function deleteAction(){
        $request=Mage::app()->getRequest()->getParams();
        $shipment = Mage::getModel('sales/order_shipment')->load($request['shipment_id']);

        $order = Mage::getModel('sales/order')->load($shipment->getOrderId());

        // check if has shipments
        if(!$order->hasShipments()){
            $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot delete shipment.'),
            );
        }else{
            //delete shipment
            $shipments = $order->getShipmentsCollection();
            foreach ($shipments as $shipment){
                $shipment->delete();
            }

            // Reset item shipment qty
            // see Mage_Sales_Model_Order_Item::getSimpleQtyToShip()
            $items = $order->getAllVisibleItems();
            foreach($items as $i){
                $i->setQtyShipped(0);
                $i->save();
            }
            //Reset order state
            $order->setCanShip(true);
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Undo Shipment');
            $order->save();

            $db = Mage::getSingleton('core/resource')->getConnection('core_write');
            $sales_flat_shipment_grid = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_grid');
            $sql = "DELETE FROM ".$sales_flat_shipment_grid." WHERE order_id='".mysql_escape_string($order->getId())."'";
            $db->query($sql);


            Mage::getSingleton('core/session')->addSuccess('Delete shipment success.');

            $this->_redirect('*/sales_order/view/', array(
                'order_id' => $order->getId()
            ));
        }
        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }
}