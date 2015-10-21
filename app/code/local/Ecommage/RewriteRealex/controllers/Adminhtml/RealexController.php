<?php
require_once "SF9/Realex/controllers/Adminhtml/RealexController.php";

class Ecommage_RewriteRealex_Adminhtml_RealexController extends SF9_Realex_Adminhtml_RealexController
{
    protected function _initTransaction()
    {
        $realexModel = Mage::getModel('realex/realex')->load(
            $this->getRequest()->getParam('id')
        );

        if (!$realexModel->getId()) {
            $this->_getSession()->addError($this->__('Wrong transaction ID specified.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            $realexModel->setOrderUrl(
                $this->getUrl('*/sales_order/view', array('order_id' => $orderId))
            );
        }

        Mage::register('realex_data', $realexModel);
        return $realexModel;
    }

    public function viewAction()
    {
        $realexModel = $this->_initTransaction();
        if (!$realexModel) {
            return;
        }
        $this->_title($this->__('Sales'))
            ->_title($this->__('Transactions'))
            ->_title(sprintf("#%s", $realexModel->getId()));

        $this->loadLayout()
            ->_setActiveMenu('realex/items')
            ->renderLayout();
    }

}