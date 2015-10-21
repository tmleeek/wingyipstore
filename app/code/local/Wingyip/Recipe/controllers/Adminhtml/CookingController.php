<?php
class Wingyip_Recipe_Adminhtml_CookingController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('recipe/recipe_cookingmethod')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Cooking Method Management'), Mage::helper('adminhtml')->__('Cooking Method Management'));
        return $this;
    }   
   
    public function indexAction() {
        $this->_initAction();     
        $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_cookingmethod'));
        $this->renderLayout();
    }
 
    public function editAction()
    {
        $cookingId     = $this->getRequest()->getParam('id');
        $cookingModel  = Mage::getModel('recipe/cookingmethod')->load($cookingId);
    
        if ($cookingModel->getId() || $cookingId == 0) {
 
            Mage::register('cooking_data', $cookingModel);
 
            $this->loadLayout();
            $this->_setActiveMenu('recipe/recipe_cookingmethod');
           
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Cooking Method Management'), Mage::helper('adminhtml')->__('Cooking Method Management'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Cooking Method Detail'), Mage::helper('adminhtml')->__('Cooking Method Detail'));
           
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
           
            $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_cookingmethod_edit'))
                 ->_addLeft($this->getLayout()->createBlock('recipe/adminhtml_cookingmethod_edit_tabs'));
               
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('Cooking Method does not exist'));
            $this->_redirect('*/*/');
        }
    }
   
    public function newAction()
    {
        $this->_forward('edit');
    }
   
    public function saveAction()
    {
        if ( $this->getRequest()->getPost() ) {
            try {
                $postData = $this->getRequest()->getPost();  
                $cookingModel = Mage::getModel('recipe/cookingmethod');
                $date = date('Y-m-d H:i:s');
                // Check Unique Category Code
                $code = $postData['code'];
                $cooId = $this->getRequest()->getParam('id');
                $uniqueCode = Mage::getResourceModel('recipe/cookingmethod')->getUniqueCode($code);
                if($uniqueCode != false){
                if($uniqueCode['cooking_id'] != $cooId){
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__($code.' Code already exist'));
                    if($cooId){
                            $this->_redirect('*/*/edit', array('id' => $cooId));    
                        }else{
                            $this->_redirect('*/*/new');   
                        }
                        return;
                   }
                }
                //=========================================================
                $cookingModel->setId($cooId)
                    ->setName($postData['name'])
                    ->setStatus($postData['description'])
                    ->setStatus($postData['status'])
                    ->setCode($postData['code'])
                    ->setSort($postData['sort']);
                    
                if ($cooId==0 || $cooId==''){
                    $cookingModel->setCreatedAt(now())
                    ->setUpdatedAt(now());
                } else {
                    $cookingModel->setUpdatedAt(now());
                }
                
                $cookingModel->save();  
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Cooking Method was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setCookingData(false);
 
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(
                        '*/*/edit',
                        array(
                            'id' => $cookingModel->getId(),
                            'store' => Mage::app()->getStore()->getStoreId()
                        )
                    );
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCookingData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }
   
    public function deleteAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $cookingModel = Mage::getModel('recipe/cookingmethod');
               
                $cookingModel->setId($this->getRequest()->getParam('id'))
                    ->delete();
                   
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Cooking Method was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
    /**
     * Product grid for AJAX request.
     * Sort and filter result for example.
     */
    public function gridAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_cookingmethod_grid'));
        $this->renderLayout();
    }
    
    public function massDeleteAction()
    {
        $cookingIds = $this->getRequest()->getParam('cooking_id');
        if(!is_array($cookingIds)) {
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('Please select Cooking Method(s).'));
        } else {
        try {
            $cookingModel = Mage::getModel('recipe/cookingmethod');
            foreach ($cookingIds as $cookingId) {
            $cookingModel->load($cookingId)->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('recipe')->__(
            'Total of %d record(s) were deleted.', count($cookingId)
            )
            );
            } 
            catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    public function massUpdateStatusAction()
    {
        $cookingIds = $this->getRequest()->getParam('cooking_id'); 
        $session    = Mage::getSingleton('adminhtml/session');

        if(!is_array($cookingIds)) {
             $session->addError(Mage::helper('adminhtml')->__('Please select Cooking Method(s).'));
        } else {
            /* @var $session Mage_Adminhtml_Model_Session */
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($cookingIds as $cookingId) { 
                    $model = Mage::getModel('recipe/cookingmethod')->load($cookingId);
                    $model->setStatus($status)->save();
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($cookingIds))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while updating the selected Cooking Method(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }
}
?>
