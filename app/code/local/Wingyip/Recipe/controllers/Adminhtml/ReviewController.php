<?php
class Wingyip_Recipe_Adminhtml_ReviewController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('recipe/recipe_review')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Review Management'), Mage::helper('adminhtml')->__('Review Management'));
        return $this;
    }   
   
    public function indexAction() {
        $this->_initAction();     
        $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_review'));
        $this->renderLayout();
    }
    
    public function pendinggridAction() {
        $this->_initAction();     
        $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_review_pendinggrid')); 
        $this->renderLayout();
    }
 
    public function editAction()
    {
        $reviewId     = $this->getRequest()->getParam('id');
        $reviewModel  = Mage::getModel('recipe/review')->load($reviewId);
    
        if ($reviewModel->getId() || $reviewId == 0) {
 
            Mage::register('review_data', $reviewModel);
 
            $this->loadLayout();
            $this->_setActiveMenu('recipe/recipe_review');
           
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Review Management'), Mage::helper('adminhtml')->__('Review Management'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Review Detail'), Mage::helper('adminhtml')->__('Review Detail'));
           
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
           
            $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_review_edit'))
                 ->_addLeft($this->getLayout()->createBlock('recipe/adminhtml_review_edit_tabs'));
               
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('Review does not exist'));
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
                $postData = $this->getRequest()->getParams();  
                
                $postData['store_id'] = Mage::app()->getStore()->getStoreId();
                $postData['subject']=$postData['title'];
                $postData['description']=$postData['detail'];
                $postData['rating']=$postData['new_rating'];
                
                //=========================================================
                
                $id = $postData['id'];
                
                $reviewModel = Mage::getModel('recipe/review');  

                $reviewModel->setData($postData)
                ->setId($id);
                    
                if ($id==0 || $id==''){
                    $reviewModel->setCreatedAt(now())
                    ->setUpdatedAt(now());
                } else {
                    $reviewModel->setUpdatedAt(now());
                }
                
                $reviewModel->save();
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Review was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setReviewData(false);
 
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(
                        '*/*/edit',
                        array(
                            'id' => $reviewModel->getId(),
                            'store' => Mage::app()->getStore()->getStoreId()
                        )
                    );
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setReviewData($this->getRequest()->getPost());
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
                $reviewModel = Mage::getModel('recipe/review');
               
                $reviewModel->setId($this->getRequest()->getParam('id'))
                    ->delete();
                   
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Review was successfully deleted'));
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
        $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_review_grid'));
        $this->renderLayout();
    }
    
    public function massDeleteAction()
    {
        $reviewIds = $this->getRequest()->getParam('review_id');
        if(!is_array($reviewIds)) {
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('Please select Review(s).'));
        } else {
        try {
            $reviewModel = Mage::getModel('recipe/review');
            foreach ($reviewIds as $reviewId) {
            $reviewModel->load($reviewId)->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('recipe')->__(
            'Total of %d record(s) were deleted.', count($reviewId)
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
        $reviewIds = $this->getRequest()->getParam('review_id'); 
        $session    = Mage::getSingleton('adminhtml/session');

        if(!is_array($reviewIds)) {
             $session->addError(Mage::helper('adminhtml')->__('Please select Review(s).'));
        } else {
            /* @var $session Mage_Adminhtml_Model_Session */
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($reviewIds as $reviewId) { 
                    $model = Mage::getModel('recipe/review')->load($reviewId);
                    $model->setStatus($status)->save();
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($reviewIds))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while updating the selected Review(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }
}
?>
