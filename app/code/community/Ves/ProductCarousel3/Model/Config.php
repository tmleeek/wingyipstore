<?php
class Ves_ProductCarousel3_Model_Config extends Mage_Catalog_Model_Product_Media_Config {

    public function getBaseMediaPath() {
        return Mage::getBaseDir('media') .DS. 'productcarousel3';
    }

    public function getBaseMediaUrl() {
        return Mage::getBaseUrl('media') . 'productcarousel3';
    }

    public function getBaseTmpMediaPath() {
        return Mage::getBaseDir('media') .DS. 'tmp' .DS. 'productcarousel3';
    }

    public function getBaseTmpMediaUrl() {
        return Mage::getBaseUrl('media') . 'tmp/productcarousel3';
    }

}
