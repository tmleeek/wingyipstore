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
 * @package     Mage_Sendfriend
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Email to a Friend Product Controller
 *
 * @category    Mage
 * @package     Mage_Sedfriend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Wingyip_Recipe_SendfriendController extends Mage_Core_Controller_Front_Action
{
	const XML_PATH_EMAIL_SENDFRIEND  = 'recipe/messages/recipe_sendfriend_email';
    /**
     * Predispatch: check is enable module
     * If allow only for customer - redirect to login page
     *
     * @return Mage_Sendfriend_ProductController
     */
    public function preDispatch()
    {
        parent::preDispatch();

        /* @var $helper Mage_Sendfriend_Helper_Data */
        $helper = Mage::helper('recipe');
        /* @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');

        if (!$helper->isEnabled()) {
            $this->norouteAction();
            return $this;
        }

        if (!$helper->isAllowForGuest() && !$session->authenticate($this)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            if ($this->getRequest()->getActionName() == 'sendemail') {
                $session->setBeforeAuthUrl(Mage::getUrl('*/*/recipesend', array(
                    '_current' => true
                )));
                Mage::getSingleton('catalog/session')
                    ->setSendfriendFormData($this->getRequest()->getPost());
            }
        }

        return $this;
    }

    /**
     * Initialize Product Instance
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initRecipe()
    {
        $recipeId  = (int)$this->getRequest()->getParam('id');
        if (!$recipeId) {
            return false;
        }
        $recipe = Mage::getModel('recipe/recipe')->load($recipeId);
        if (!$recipe->getId()) {
            return false;
        }

        Mage::register('recipe', $recipe);
        return $recipe;
    }

    /**
     * Initialize send friend model
     *
     * @return Mage_Sendfriend_Model_Sendfriend
     */
    protected function _initSendToFriendModel()
    {
        $model  = Mage::getModel('sendfriend/sendfriend');
        $model->setRemoteAddr(Mage::helper('core/http')->getRemoteAddr(true));
        $model->setCookie(Mage::app()->getCookie());
        $model->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

        Mage::register('send_to_friend_model', $model);

        return $model;
    }

    /**
     * Show Send to a Friend Form
     *
     */
    public function recipesendAction()
    {
        $product    = $this->_initRecipe();
        //$model      = $this->_initSendToFriendModel();

        if (!$product) {
            $this->_forward('noRoute');
            return;
        }

       /* if ($model->getMaxSendsToFriend() && $model->isExceedLimit()) {
            Mage::getSingleton('catalog/session')->addNotice(
                $this->__('The messages cannot be sent more than %d times in an hour', $model->getMaxSendsToFriend())
            );
        }*/

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $data = Mage::getSingleton('catalog/session')->getSendfriendFormData();
        if ($data) {
            Mage::getSingleton('catalog/session')->setSendfriendFormData(true);
            $block = $this->getLayout()->getBlock('recipe.sendfriend.send');
            if ($block) {
                $block->setFormData($data);
            }
        }

        $this->renderLayout();
    }

    /**
     * Send Email Post Action
     *
     */
    public function sendmailAction()
    { 
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/recipesend', array('_current' => true));
        }

        $recipe    = $this->_initRecipe();
       // $model      = $this->_initSendToFriendModel();
        $data       = $this->getRequest()->getPost();

        if (!$recipe || !$data) {
            $this->_forward('noRoute');
            return;
        }

        try {
			$sender=$this->getRequest()->getPost('sender');
			$recipients=$this->getRequest()->getPost('recipients');
			
			/* @var $translate Mage_Core_Model_Translate */
			$translate = Mage::getSingleton('core/translate');
			$translate->setTranslateInline(false);
	
			/* @var $mailTemplate Mage_Core_Model_Email_Template */
			$mailTemplate = Mage::getModel('core/email_template');
	
			$message = nl2br(htmlspecialchars($sender['message']));
			$sender  = array(
				'name'  => $sender['name'],
				'email' => $sender['email']
			);
	
			$mailTemplate->setDesignConfig(array(
				'area'  => 'frontend',
				'store' => Mage::app()->getStore()->getId()
			));
	
			foreach ($recipients['emails'] as $k => $email) {
				$name = $recipients['name'][$k];
				$mailTemplate->sendTransactional(
					self::XML_PATH_EMAIL_SENDFRIEND,
					$sender,
					$email,
					$name,
					array(
						'name'          => $name,
						'email'         => $email,
						'recipe_name'  => $this->getProduct()->getName(),
						'recipe_url'   => $this->getProduct()->getUrlInStore(),
						'message'       => $message,
						'sender_name'   => $sender['name'],
						'sender_email'  => $sender['email'],
						'product_image' => Mage::helper('catalog/image')->init($this->getProduct(),
						'small_image')->resize(75),
					)
				);
			}
			if (!$mailTemplate->getSentSuccess())
			{
						Mage::getSingleton('catalog/session')->addError($this->__('There were some problems with the data.'));
			}
			else{
				$translate->setTranslateInline(true);
				 Mage::getSingleton('catalog/session')->addSuccess($this->__('The link to a friend was sent.'));
					$this->_redirectSuccess($product->getProductUrl());
					return;
			}
           
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('catalog/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('catalog/session')
                ->addException($e, $this->__('Some emails were not sent.'));
        }

        // save form data
        Mage::getSingleton('catalog/session')->setSendfriendFormData($data);

        $this->_redirectError(Mage::getURL('*/*/recipesend', array('_current' => true)));
    }
}
