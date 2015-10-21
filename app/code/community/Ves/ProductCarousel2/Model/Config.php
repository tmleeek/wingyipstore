<?php
class Ves_ProductCarousel2_Model_Config extends Mage_Catalog_Model_Product_Media_Config {

    public function getBaseMediaPath() {
        return Mage::getBaseDir('media') .DS. 'productcarousel2';
    }

    public function getBaseMediaUrl() {
        return Mage::getBaseUrl('media') . 'productcarousel2';
    }

    public function getBaseTmpMediaPath() {
        return Mage::getBaseDir('media') .DS. 'tmp' .DS. 'productcarousel2';
    }

    public function getBaseTmpMediaUrl() {
        return Mage::getBaseUrl('media') . 'tmp/productcarousel2';
    }

}
