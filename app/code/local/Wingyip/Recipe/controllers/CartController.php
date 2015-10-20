<?php
require_once('Mage/Checkout/controllers/CartController.php');
class Wingyip_Recipe_CartController extends Mage_Checkout_CartController//Mage_Core_Controller_Front_Action
{
    public function addmultipleAction() 
    {
        $productIds = $this->getRequest()->getParam('products');
        
        if (!is_array($productIds)) {
            $this->_goBack();
            return;
        }

        $cart = $this->_getCart();

        foreach( $productIds as $productId) {

            try {
                $qty = $this->getRequest()->getParam('qty' . $productId, 0);
                if ($qty <= 0) continue; // nothing to add
                
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId)
                    ->setConfiguredAttributes($this->getRequest()->getParam('super_attribute'))
                    ->setGroupedProducts($this->getRequest()->getParam('super_group', array()));
                $eventArgs = array(
                    'product' => $product,
                    'qty' => $qty,
                    'request' => $this->getRequest(),
                    'response' => $this->getResponse(),
                );
                Mage::dispatchEvent('checkout_cart_before_add', $eventArgs);

                $cart->addProduct($product, $qty);
                Mage::dispatchEvent('checkout_cart_after_add', $eventArgs);
                
                Mage::dispatchEvent('checkout_cart_add_product', array('product'=>$product));
                $message = $this->__('%s was successfully added to your shopping cart.', $product->getName());    
                Mage::getSingleton('checkout/session')->addSuccess($message);
            }
            catch (Mage_Core_Exception $e) {
                if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                    Mage::getSingleton('checkout/session')->addNotice($product->getName() . ': ' . $e->getMessage());
                }
                else {
                    Mage::getSingleton('checkout/session')->addError($product->getName() . ': ' . $e->getMessage());
                }
            }
            catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addException($e, $this->__('Can not add item to shopping cart'));
            }

        }
        $cart->save();
        
        $this->_goBack();
    }
}
