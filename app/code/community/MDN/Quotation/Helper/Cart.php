<?php

class MDN_quotation_Helper_Cart extends Mage_Core_Helper_Abstract {

    /**
     * Empty cart
     *
     */
    public function emptyCart($save = false) {
        $cart = Mage::getSingleton('checkout/cart');
        Mage::getSingleton('core/session')->unsQuoteDetails();
        $cart->truncate();
        Mage::getSingleton("core/session")->unsShippingSkuQuote();
        if ($save)
            $cart->save();
    }

}