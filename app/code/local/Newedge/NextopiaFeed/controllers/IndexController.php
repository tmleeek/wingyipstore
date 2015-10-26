<?php
class Newedge_NextopiaFeed_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        return $this->NextopiaExport();
    }

    public function NextopiaExport(){
        header('Content-type: text/xml');
        // header('Content-Disposition: attachment; filename="text.xml"');
        $this->outputHeader();

        $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $collection->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
        $counter = 0;
        foreach($collection as $prod) {
            // if ($counter > 5){
            //     // continue;
            // }
            $product = Mage::getModel('catalog/product')->load($prod->getId());
            $this->generateProductRowXML($product);
            $counter++;
        }
    }

    public function generateProductRowXML($product){
        $item = $this->createNode("g:id", $product->sku);
        $item .= $this->createNode("title", $product->name);
        $item .= $this->createNode("description", $product->description);
        $item .= $this->createNode("title", $product->name);
        $item .= $this->createNode("price", $product->getPrice() . " GBP");
        $item .= $this->createNode("link", $product->getProductUrl());
        $item .= $this->createNode("g:image_link", Mage::helper('catalog/image')->init($product, 'image'));
        $item .= $this->createNode("g:product_type", "something");
        $item .= $this->createNode("g:availability", Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getIsInStock()?'in stock':'out of stock');
        $item .= $this->createNode("g:brand", $product->brand);
        $row = $this->createItem("item", $item);
        echo $row;
    }

    public function createNode($NodeName, $NodeValue){
        return "<$NodeName><![CDATA[$NodeValue]]></$NodeName>";
    }

    public function createItem($ItemName, $ItemContent){
        return "<$ItemName>$ItemContent</$ItemName>";
    }

    public function outputHeader(){
        echo '<?xml version="1.0"?>';
        echo '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">';
        echo '<channel>';
        $store = Mage::app()->getStore();
        $name = $store->getName();
        $url = $store->getUrl();
        echo '<title>' . $name . ' product export (' . date("d/m/Y") . ')</title>';
        echo '<link>' . $url . '</link>';
        echo '<description>Export of all enabled products for ' . $name . ' store.</description>";';
    }
}