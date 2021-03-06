<?php
class Wingyip_Recipe_Adminhtml_CupboardController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('recipe/recipe_cupboard')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Cupboard Manager'), Mage::helper('adminhtml')->__('Cupboard Manager'));
        return $this;
    }   
   
    public function indexAction() {
        $this->_initAction();     
        $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_cupboard'));
        $this->renderLayout();
    }
 
    public function editAction()
    {
        $cupboardId     = $this->getRequest()->getParam('id');
        $cupboardModel  = Mage::getModel('recipe/cupboard')->load($cupboardId);
    
        if ($cupboardModel->getId() || $cupboardId == 0) {
 
            Mage::register('cupboard_data', $cupboardModel);
 
            $this->loadLayout();
            $this->_setActiveMenu('recipe/recipe_cupboard');
           
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Cupboard Manager'), Mage::helper('adminhtml')->__('Cupboard Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Cupboard Detail'), Mage::helper('adminhtml')->__('Cupboard Detail'));
           
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
           
            $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_cupboard_edit'))
                 ->_addLeft($this->getLayout()->createBlock('recipe/adminhtml_cupboard_edit_tabs'));
               
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cupboard')->__('Cupboard does not exist'));
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
                $cupboardModel = Mage::getModel('recipe/cupboard');
                
                // Check Unique Cupboard Code
                $code = $postData['code'];
                $cupId = $this->getRequest()->getParam('id');
                $uniqueCode = Mage::getResourceModel('recipe/cupboard')->getUniqueCode($code);
                if($uniqueCode != false){
                if($uniqueCode['recipe_cupboard_id'] != $cupId){
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__($code.' Code already exist'));
                    if($cupId){
                            $this->_redirect('*/*/edit', array('id' => $cupId));    
                        }else{
                            $this->_redirect('*/*/new');   
                        }
                        return;
                   }
                }
                //=========================================================
                
                $cupboardModel->setId($cupId)
                    ->setName($postData['name'])
                    ->setCode($postData['code'])
                    ->setSort($postData['sort'])
                    ->setStatus($postData['status']);
                    
                    if ($cupId==0 || $cupId==''){
                    $cupboardModel->setCreatedTime(now())
                    ->setUpdateTime(now());
                    } else {
                        $cupboardModel->setUpdateTime(now());
                    }
                    $cupboardModel->save();
                    
               
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Recipe Cupboard Ingrient was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setCupboardData(false);
 
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(
                        '*/*/edit',
                        array(
                            'id' => $cupboardModel->getId(),
                            'store' => Mage::app()->getStore()->getStoreId()
                        )
                    );
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCupboardData($this->getRequest()->getPost());
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
                $cupboardModel = Mage::getModel('recipe/cupboard');
               
                $cupboardModel->setId($this->getRequest()->getParam('id'))
                    ->delete();
                   
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Recipe Cupboard Ingredient was successfully deleted'));
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
        $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_cupboard_grid'));
        $this->renderLayout();
    }
    
    public function massDeleteAction()
    {
        $cupboardIds = $this->getRequest()->getParam('recipe_cupboard_id');
        if(!is_array($cupboardIds)) {
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('Please select cupboard ingredient(s).'));
        } else {
        try {
            $cupboardModel = Mage::getModel('recipe/cupboard');
            foreach ($cupboardIds as $cupboardId) {
            $cupboardModel->load($cupboardId)->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('recipe')->__(
            'Total of %d record(s) were deleted.', count($cupboardIds)
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
        $cupboardIds = $this->getRequest()->getParam('recipe_cupboard_id');
        $session    = Mage::getSingleton('adminhtml/session');

        if(!is_array($cupboardIds)) {
             $session->addError(Mage::helper('adminhtml')->__('Please select cupboard ingredient(s).'));
        } else {
            /* @var $session Mage_Adminhtml_Model_Session */
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($cupboardIds as $cupboardId) {
                    $model = Mage::getModel('recipe/cupboard')->load($cupboardId);
                    $model->setStatus($status)->save();
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($cupboardIds))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while updating the cupboard ingredient(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

}