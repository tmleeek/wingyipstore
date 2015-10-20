<?php
class Wingyip_Exportorder_Adminhtml_ExportorderController extends Mage_Adminhtml_Controller_action
{
    public function runcronAction(){
         $cronModel = Mage::getModel('cron/schedule');
         $cronModel->setJobCode('wingyip_exportorder')
                ->setDescription($postData['description'])
                ->setCreatedAt(date('Y-m-d h:i:s'))
                ->setscheduled_at(date("Y/m/d h:i:s", strtotime("+30 minutes")))
                ->save();
         Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Cron status update successfully.'));
         Mage::getSingleton('adminhtml/session')->setExportorderData(false);
        $this->_redirect('adminhtml/sales_order/index');
        return;
    }
    
    public function exportorderAction(){
        $order_id = $this->getRequest()->getParam('order_id');
        if ($order_id) {
            $order_ids = array($order_id);
            try {
                Mage::getModel('exportorder/exportorder')->exportOrder($order_ids);
                $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__('The order has been exported.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                Mage::logException($e);
            }
        }
        $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order_id));    
    }
    public function exportselectedordersAction(){
        $order_ids = $this->getRequest()->getParam('order_ids');
        if ($order_ids) {
            try {
                Mage::getModel('exportorder/exportorder')->exportOrder($order_ids);
                $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__('The order(s) has been exported.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                Mage::logException($e);
            }
        }
        $this->_redirect('adminhtml/sales_order/index');     
    }
    
    public function downloadAction() {
        $order_id = $this->getRequest()->getParam('order_id');
        if($order_id){
            try {
                $order = Mage::getModel('sales/order')->load($order_id);
                $name = 'magento'.'_'.$order->getIncrementId().'.txt';
                
                $path = Mage::getBaseDir('var') . DS . 'export' . DS . 'order' . DS . $name;
                if($order->getExportStatus() == 2 && file_exists($path)){  
                      
                    $content = file_get_contents($path);
                    $this->_prepareDownloadResponse($name, $content, 'text/plain');
                                        
                }else{
                    Mage::throwException(Mage::helper('adminhtml')->__('No file exported to Download.'));
                }
            }catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order_id));
            }               
        }
    } 
}