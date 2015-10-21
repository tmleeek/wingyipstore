<?php
class Wingyip_Recipe_Adminhtml_CuisineController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('recipe/recipe_cuisine')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Cuisine Type Manager'), Mage::helper('adminhtml')->__('Cuisine Type Manager'));
        return $this;
    }   
   
    public function indexAction() {
        $this->_initAction();     
        $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_cuisine'));
        $this->renderLayout();
    }
 
    public function editAction()
    {
        $cuisineId     = $this->getRequest()->getParam('id');
        $cuisineModel  = Mage::getModel('recipe/cuisine')->load($cuisineId);
       
        if ($cuisineModel->getId() || $cuisineId == 0) {
 
            Mage::register('cuisine_data', $cuisineModel);
 
            $this->loadLayout();
            $this->_setActiveMenu('recipe/recipe_cuisine');
           
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Cuisine Type Manager'), Mage::helper('adminhtml')->__('Cuisine Type Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Cuisine News'), Mage::helper('adminhtml')->__('Cuisine Type News'));
           
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
           
            $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_cuisine_edit'))
                 ->_addLeft($this->getLayout()->createBlock('recipe/adminhtml_cuisine_edit_tabs'));
               
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cuisine')->__('Cuisine Type does not exist'));
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
                $cuisineModel = Mage::getModel('recipe/cuisine');
                
                // Check Unique Cuisine Code
                $code = $postData['code'];
                $cuisId = $this->getRequest()->getParam('id');
                $uniqueCode = Mage::getResourceModel('recipe/cuisine')->getUniqueCode($code);
                if($uniqueCode != false){
                if($uniqueCode['recipe_cuisine_id'] != $cuisId){
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__($code.' Code already exist'));
                    if($cuisId){
                            $this->_redirect('*/*/edit', array('id' => $cuisId));    
                        }else{
                            $this->_redirect('*/*/new');   
                        }
                        return;
                   }
                }
                //=========================================================
                $cuisineModel->setId($cuisId)
                    ->setName($postData['name'])
                    ->setLevel($postData['level'])
                    ->setPath($postData['path'])
                    ->setCode($postData['code'])
                    ->setStatus($postData['status'])
                    ->setSort($postData['sort']) 
                    ->setParentId(0);
                    
                    if ($catId==0 || $catId==''){
                    $cuisineModel->setCreatedTime(now())
                    ->setUpdateTime(now());
                    } else {
                        $cuisineModel->setUpdateTime(now());
                    }
                    $cuisineModel->save();
               
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Cuisine Type was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setCuisineData(false);
 
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(
                        '*/*/edit',
                        array(
                            'id' => $cuisineModel->getId(),
                            'store' => Mage::app()->getStore()->getStoreId()
                        )
                    );
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCuisineData($this->getRequest()->getPost());
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
                $cuisineModel = Mage::getModel('recipe/cuisine');
               
                $cuisineModel->setId($this->getRequest()->getParam('id'))
                    ->delete();
                   
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Cuisine Type was successfully deleted'));
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
        $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_cuisine_grid'));
        $this->renderLayout();
    }
    
    public function massDeleteAction()
    {
        $cuisineIds = $this->getRequest()->getParam('recipe_cuisine_id');
        if(!is_array($cuisineIds)) {
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('Please select cuisine type(s).'));
        } else {
        try {
            $cuisineModel = Mage::getModel('recipe/cuisine');
            foreach ($cuisineIds as $cuisineId) {
            $cuisineModel->load($cuisineId)->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('recipe')->__(
            'Total of %d record(s) were deleted.', count($cuisineIds)
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
        $cuisineIds = $this->getRequest()->getParam('recipe_cuisine_id');
        $session    = Mage::getSingleton('adminhtml/session');

        if(!is_array($cuisineIds)) {
             $session->addError(Mage::helper('adminhtml')->__('Please select cuisine type(s).'));
        } else {
            /* @var $session Mage_Adminhtml_Model_Session */
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($cuisineIds as $cuisineId) {
                    $model = Mage::getModel('recipe/cuisine')->load($cuisineId);
                    $model->setStatus($status)->save();
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($cuisineIds))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while updating the cuisine type(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

}
