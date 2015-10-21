<?php
class Wingyip_Recipe_Adminhtml_IngredientController extends Mage_Adminhtml_Controller_Action
{
 
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('recipe/recipe_ingredient')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Ingredient Manager'), Mage::helper('adminhtml')->__('Ingredient Manager'));
        return $this;
    }   
   
    public function indexAction() {
        $this->_initAction();     
        $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_ingredient'));
        $this->renderLayout();
    }
 
    public function editAction()
    {
        $ingredientId     = $this->getRequest()->getParam('id');
        $ingredientModel  = Mage::getModel('recipe/ingredient')->load($ingredientId);
    
        if ($ingredientModel->getId() || $ingredientId == 0) {
 
            Mage::register('ingredient_data', $ingredientModel);
 
            $this->loadLayout();
            $this->_setActiveMenu('recipe/recipe_ingredient');
           
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Ingredient Manager'), Mage::helper('adminhtml')->__('Ingredient Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Ingredient Detail'), Mage::helper('adminhtml')->__('Ingredient Detail'));
           
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
           
            $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_ingredient_edit'))
                 ->_addLeft($this->getLayout()->createBlock('recipe/adminhtml_ingredient_edit_tabs'));
               
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ingredient')->__('Ingredient does not exist'));
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
                $ingredientModel = Mage::getModel('recipe/ingredient');
                $date = date('Y-m-d H:i:s');
                // Check Unique Category Code
                $code = $postData['code'];
                $ingId = $this->getRequest()->getParam('id');
                $uniqueCode = Mage::getResourceModel('recipe/ingredient')->getUniqueCode($code);
                if($uniqueCode != false){
                if($uniqueCode['recipe_ingredients_id'] != $ingId){
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__($code.' Code already exist'));
                    if($ingId){
                            $this->_redirect('*/*/edit', array('id' => $ingId));    
                        }else{
                            $this->_redirect('*/*/new');   
                        }
                        return;
                   }
                }
                //=========================================================
                $ingredientModel->setId($ingId)
                    ->setName($postData['name'])
                    ->setCode($postData['code'])
                    ->setSort($postData['sort'])
                    ->setStatus($postData['status']);
                    
                    if ($ingId==0 || $ingId==''){
                    $ingredientModel->setCreatedTime(now())
                    ->setUpdateTime(now());
                    } else {
                        $ingredientModel->setUpdateTime(now());
                    }
                $ingredientModel->save();
               
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Ingredient was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setIngredientData(false);
 
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(
                        '*/*/edit',
                        array(
                            'id' => $ingredientModel->getId(),
                            'store' => Mage::app()->getStore()->getStoreId()
                        )
                    );
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setIngredientData($this->getRequest()->getPost());
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
                $ingredientModel = Mage::getModel('recipe/ingredient');
               
                $ingredientModel->setId($this->getRequest()->getParam('id'))
                    ->delete();
                   
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Ingredient was successfully deleted'));
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
        $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_ingredient_grid'));
        $this->renderLayout();
    }
    
    public function massDeleteAction()
    {
        $ingredientIds = $this->getRequest()->getParam('recipe_ingredients_id');
        if(!is_array($ingredientIds)) {
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('Please select ingredient(s).'));
        } else {
        try {
            $ingredientModel = Mage::getModel('recipe/ingredient');
            foreach ($ingredientIds as $ingredientId) {
            $ingredientModel->load($ingredientId)->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('recipe')->__(
            'Total of %d record(s) were deleted.', count($ingredientIds)
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
        $ingredientIds = $this->getRequest()->getParam('recipe_ingredients_id');
        $session    = Mage::getSingleton('adminhtml/session');

        if(!is_array($ingredientIds)) {
             $session->addError(Mage::helper('adminhtml')->__('Please select ingredient(s).'));
        } else {
            /* @var $session Mage_Adminhtml_Model_Session */
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($ingredientIds as $ingredientId) {
                    $model = Mage::getModel('recipe/ingredient')->load($ingredientId);
                    $model->setStatus($status)->save();
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($ingredientIds))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while updating the selected ingredient(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

}