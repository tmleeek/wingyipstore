<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Review
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review controller
 *
 * @category   Mage
 * @package    Mage_Review
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Wingyip_Recipe_ReviewController extends Mage_Core_Controller_Front_Action
{

    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('post');

    public function preDispatch()
    {
        parent::preDispatch();

        $allowGuest = false;//Mage::helper('review')->getIsGuestAllowToWrite();
        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $action = $this->getRequest()->getActionName();
        if (!$allowGuest){// && $action == 'post' && $this->getRequest()->isPost()) {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);  
                Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_current' => true)));
                Mage::getSingleton('review/session')->setFormData($this->getRequest()->getPost())
                    ->setRedirectUrl($this->_getRefererUrl());
                $this->_redirectUrl(Mage::helper('customer')->getLoginUrl());
            }
        }

        return $this;
    }
    /**
     * Initialize and check product
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initRecipe()
    {
        $recipeId  = (int) $this->getRequest()->getParam('id');

        $recipe = $this->_loadRecipe($recipeId);
        if (!$recipe) {
            return false;
        }

        return $recipe;
    }

    /**
     * Load recipe model with data by passed id.
     * Return false if product was not loaded or has incorrect status.
     *
     * @param int $productId
     * @return bool|Mage_Catalog_Model_Product
     */
    protected function _loadRecipe($recipeId)
    {
        if (!$recipeId) {
            return false;
        }

        $recipe = Mage::getModel('recipe/recipe')
            ->load($recipeId);
        /* @var $product Mage_Catalog_Model_Product */
        if (!$recipe->getId()) {
            return false;
        }

        Mage::register('current_recipe', $recipe);
        Mage::register('recipe', $recipe);

        return $recipe;
    }

    /**
     * Load review model with data by passed id.
     * Return false if review was not loaded or review is not approved.
     *
     * @param int $productId
     * @return bool|Mage_Review_Model_Review
     */
    protected function _loadReview($reviewId)
    {
        if (!$reviewId) {
            return false;
        }

        $review = Mage::getModel('review/review')->load($reviewId);
        /* @var $review Mage_Review_Model_Review */
        if (!$review->getId() || !$review->isApproved() || !$review->isAvailableOnStore(Mage::app()->getStore())) {
            return false;
        }

        Mage::register('current_review', $review);

        return $review;
    }

    /**
     * Submit new review action
     *
     */
    public function postreviewAction()
    {
        if (!$this->_validateFormKey()) {
            // returns to the product item page
            $this->_redirectReferer();
            return;
        }

        if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
            $rating = array();
            if (isset($data['rating']) ) {
                $rating = $data['rating'];
            }
        } else {
            $data   = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('rating');
        }

        if (($recipe = $this->_initRecipe()) && !empty($data)) {
            $session    = Mage::getSingleton('core/session');
			$data['subject']=$data['title'];
			$data['description']=$data['detail'];
			$data['recipe_id']=$this->getRequest()->getParam('id');
			
            $review     = Mage::getModel('recipe/review')->setData($data);
            
                try {
					
                    $review
                        ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->setStoreId(Mage::app()->getStore()->getId())
						->setCreatedAt(now())
                   		 ->setUpdatedAt(now())
                        ->save();
					//$reviewId=$review->getId();
					//echo "<pre>"; print_r($review->getData());exit;
					/*$reviewDecr=Mage::getModel('review/reviewdescr')
								->setData($data)
								->setStoreId(Mage::app()->getStore()->getId())
								->save();*/

                    $session->addSuccess($this->__('Your review has been accepted for moderation.'));
                }
                catch (Exception $e) {
                    $session->setFormData($data);
                    $session->addError($this->__('Unable to post the review.'.$e->getMessage()));
                }
        }

        if ($redirectUrl = Mage::getSingleton('review/session')->getRedirectUrl(true)) {
            $this->_redirectUrl($redirectUrl);
            return;
        }
        $this->_redirectReferer();
    }

    /**
     * Show list of product's reviews
     *
     */
    public function listAction()
    { 
        if ($recipe = $this->_initRecipe()) {
            Mage::register('recipeId', $recipe->getId());
			
			$this->loadLayout();
			
			$id_path = "recipe/{$recipe->getId()}";
        	$mainUrlRewrite = Mage::getModel('core/url_rewrite')->loadByIdPath($id_path);
        	$recipeUrl=Mage::getUrl().$mainUrlRewrite->getRequestPath(); 
		
            // update breadcrumbs
            if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbsBlock->addCrumb('recipe', array(
                    'label'    => $recipe->getName(),
                    'link'     => $recipeUrl,
                    'readonly' => true,
                ));
                $breadcrumbsBlock->addCrumb('reviews', array('label' => Mage::helper('recipe')->__('Recipes Reviews')));
            }
			
            $this->renderLayout();
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }

    /**
     * Show details of one review
     *
     */
    public function viewAction()
    {
        $review = $this->_loadReview((int) $this->getRequest()->getParam('id'));
        if (!$review) {
            $this->_forward('noroute');
            return;
        }

        $product = $this->_loadProduct($review->getEntityPkValue());
        if (!$product) {
            $this->_forward('noroute');
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('review/session');
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }
    public function massUpdateStatusAction()
    {
        $reviewIds = $this->getRequest()->getParam('review_id');
        $session    = Mage::getSingleton('adminhtml/session');

        if(!is_array($reviewIds)) {
             $session->addError(Mage::helper('adminhtml')->__('Please select review(s).'));
        } else {
            /* @var $session Mage_Adminhtml_Model_Session */
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($reviewIds as $_reviewId) {
                    $model = Mage::getModel('recipe/review')->load($_reviewId);
                    $model->setStatus($status)->save();
                        
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($reviewIds))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while updating the selected review(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

}
