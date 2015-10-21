<?php

class Wingyip_Exportorder_Model_Exportorder extends Mage_Core_Model_Abstract
{
    const XML_PATH_EMAIL_ERROR_TEMPLATE = 'exportproducts_error_email_template';

    protected $_ftpDetails = array();

    public function _construct()
    {
        parent::_construct();
        $this->_init('exportorder/exportorder');
        $this->_setFtpDetails();
    }

    protected function _setFtpDetails()
    {
        $this->_ftpDetails = array(
            "host" => Mage::getStoreConfig('export_section/exportorder_group/ftp_host'),
            "username" => Mage::getStoreConfig('export_section/exportorder_group/ftp_username'),
            "password" => Mage::getStoreConfig('export_section/exportorder_group/ftp_password'),
            "type" => Mage::getStoreConfig('export_section/exportorder_group/ftp_type'),
            "port" => Mage::getStoreConfig('export_section/exportorder_group/ftp_port'),
            "folder" => Mage::getStoreConfig('export_section/exportorder_group/ftp_folder')
        );
    }

    protected function _getFtpDetails()
    {
        return $this->_ftpDetails;
    }

    public function getOrderCollection($order_ids)
    {

        $orderCollection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('entity_id', array('in' => $order_ids))
            ->addFieldToFilter('status', array('in' => array('processing', 'complete')));

        return $orderCollection;
    }

    public function getOrder($orderId)
    {
        return Mage::getModel('sales/order')->load($orderId);
    }

    public function getCustomer($customerId)
    {
        return Mage::getModel('customer/customer')->load($customerId);
    }

    public function exportOrder($order_ids)
    {
        if ($order_ids) {
        //mail("emailtestertwo@gmail.com","cron test","It's a test message Of Order Export cron staging start".now());
            try {
                $orderCollection = $this->getOrderCollection($order_ids);
                $recipenewsletter = $keepdetailsonfile = 'n';
                $discountcodes = "na";
                $orderCreated = array();
                if ($orderCollection->getSize()) {
                    foreach ($orderCollection as $order) {
                        $order = $this->getOrder($order->getId());
                        $customer = $this->getCustomer($order->getCustomerId());

                        $billingAddress = $order->getBillingAddress();
                        $shippingAddress = $order->getShippingAddress();
                        $orderedItems = $order->getAllItems();

                        $startsWith = substr($order->getIncrementId(), 0, 3);
                        if ($startsWith == 'Amz') {
                            $accountNo = 798888;
                        } else if ($startsWith == 'Eba') {
                            $accountNo = 798887;
                        } else {
                            $accountNo = 888;
                        }

                        $orderInfo = '[customerdetails]' . "\n";
                        $orderInfo .= 'title=' . $billingAddress->getPrefix() . "\n";
                        $orderInfo .= 'firstname=' . $billingAddress->getFirstname() . "\n";
                        $orderInfo .= 'surname=' . $billingAddress->getLastname() . "\n";
                        $orderInfo .= 'address1=' . $billingAddress->getStreet(1) . "\n";
                        $orderInfo .= 'address2=' . $billingAddress->getStreet(2) . "\n";
                        $orderInfo .= 'address3=' . '' . "\n";
                        $orderInfo .= 'fax=' . $billingAddress->getFax() . "\n";
                        $orderInfo .= 'town=' . $billingAddress->getCity() . "\n";
                        $orderInfo .= 'postcode=' . $billingAddress->getPostcode() . "\n";
                        $orderInfo .= 'county=' . $billingAddress->getRegion() . "\n";
                        $orderInfo .= 'country=' . $billingAddress->getCountryId() . "\n";
                        $orderInfo .= 'email=' . $billingAddress->getEmail() . "\n";
                        $orderInfo .= 'telephone=' . $billingAddress->getTelephone() . "\n";
                        $orderInfo .= 'mobilenumber=' . $billingAddress->getMobileNumber() . "\n";
                        $orderInfo .= 'customerref=' . $customer->getId() . "\n";
                        $orderInfo .= 'webref=' . $order->getIncrementId() . "\n";
                        $orderInfo .= 'orderdate=' . date('dmY', strtotime($order->getCreatedAt())) . "\n";
                        $orderInfo .= 'ordertime=' . date('His', strtotime($order->getCreatedAt())) . "\n";
                        $orderInfo .= 'ipaddress=' . $order->getRemoteIp() . "\n";
                        $orderInfo .= 'deliveryoption=' . $order->getShippingDescription() . "\n";
                        $orderInfo .= 'recipenewsletter=' . $recipenewsletter . "\n";
                        $orderInfo .= 'keepdetailsonfile=' . $keepdetailsonfile . "\n";
                        $orderInfo .= "\n\n";


                        $orderInfo .= '[deliverydetails]' . "\n";
                        $orderInfo .= 'locno=79' . "\n";
                        $orderInfo .= 'accountno=' . $accountNo . "\n";
                        $orderInfo .= 'contactname=' . $shippingAddress->getFirstname() . " " . $shippingAddress->getLastname() . "\n";
                        $orderInfo .= 'address1=' . $shippingAddress->getStreet(1) . "\n";
                        $orderInfo .= 'address2=' . $shippingAddress->getStreet(2) . "\n";
                        $orderInfo .= 'address3=' . '' . "\n";
                        $orderInfo .= 'fax=' . $shippingAddress->getFax() . "\n";
                        $orderInfo .= 'town=' . $shippingAddress->getCity() . "\n";
                        $orderInfo .= 'postcode=' . $shippingAddress->getPostcode() . "\n";
                        $orderInfo .= 'county=' . $shippingAddress->getRegion() . "\n";
                        $orderInfo .= 'country=' . $shippingAddress->getCountryId() . "\n";
                        $orderInfo .= 'telephone=' . $shippingAddress->getTelephone() . "\n";
                        $orderInfo .= 'mobilenumber=' . $shippingAddress->getMobileNumber() . "\n";
                        $orderInfo .= 'email=' . $shippingAddress->getEmail() . "\n";
                        $orderInfo .= "\n\n";

                        $itemInfo = '';
                        $goodssubtotalexvat = 0;
                        $cnt = 0;
                        foreach ($orderedItems as $item) {
                            $productType = $item->getProductType();
                            if ($productType == 'simple') {

                                $qty = (int)$item->getQtyOrdered();
                                $qty = $qty - $item->getQtyRefunded();

                                $sku = $item->getSku();

                                if ($qty < 1)
                                    continue;

                                $cnt++;

                                /*echo "<pre>";
                                print_r($item->getData());die;*/

                                $itemPricewithoutDiscount = ($item->getOriginalPrice() * $qty) - $item->getDiscountAmount();
                                $goodssubtotalexvat += $itemPricewithoutDiscount;

                                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                                if ($product) {
                                    $price = number_format($item->getOriginalPrice(), 2, '.', '');
                                    $size = $product->getProductsize();
                                    $unitcost = $itemPricewithoutDiscount;//$product->getPrice();
                                    $vatAmmount = $item->getTaxAmount();
                                    //Store Order Product Data in array
                                    $itemInfo .= "\n" . '[orderline' . "$cnt" . ']' . "\n";
                                    $itemInfo .= 'productcode=' . $sku . "\n";
                                    $itemInfo .= 'recipeindicator=' . '0' . "\n";
                                    $itemInfo .= 'recipetitle=' . '' . "\n";
                                    $itemInfo .= 'productdescription=' . strip_tags($product->getName()) . "\n";
                                    $itemInfo .= 'maxpacksize=1' . "\n";
                                    $itemInfo .= 'minpacksize=1' . "\n";
                                    $itemInfo .= 'maxpackcost=1' . "\n";
                                    $itemInfo .= 'minpackcost=0' . "\n";
                                    $itemInfo .= 'unitcost=' . $price . "\n";
                                    $itemInfo .= 'discountcodes=' . $discountcodes . "\n";
                                    $itemInfo .= 'qtyordered=' . $qty . "\n";
                                    $itemInfo .= 'qtysupplied=' . $qty . "\n";
                                    $itemInfo .= 'Sellunit=1' . "\n";
                                    $itemInfo .= 'linecost=' . number_format($qty * $price, 2, '.', '') . "\n";
                                    $itemInfo .= ($product->getTaxClassId() == 21) ? "vatcode=1 \n" : "vatcode=3 \n";
                                    if ($product->getTaxClassId() == 21) {
                                        $itemInfo .= 'vatamount=' . number_format($vatAmmount, 2, '.', '') . "\n";
                                    } else {
                                        $itemInfo .= 'vatamount=0' . "\n";
                                    }

                                } else {
                                    $itemInfo .= "\n" . '[orderline' . "$cnt" . ']' . "\n";
                                    $itemInfo .= 'product issue' . "\n";
                                }
                            }
                        }

                        $orderInfo .= "[orderdetails]" . "\n";
                        $orderInfo .= 'orderlines=' . ($cnt + 1) . "\n";
                        $orderInfo .= 'numspecialinstructionlines=0' . "\n";
                        //$orderInfo .= 'specialinstructionsline1='.''."\n";
                        $orderInfo .= 'goodssubtotalexvat=' . number_format($goodssubtotalexvat, 2, '.', '') . "\n";
                        $orderInfo .= 'goodssubtotalvat=' . number_format(($order->getTaxAmount() - $order->getBaseTaxRefunded()), 2, '.', '') . "\n";
                        $orderInfo .= 'carriagesubtotal=' . number_format($order->getShippingAmount(), 2, '.', '') . "\n";
                        $orderInfo .= 'grossordervalue=' . number_format(($order->getGrandTotal() - $order->getBaseTotalRefunded()), 2, '.', '') . "\n";

                        $orderInfo .= "\n\n";

                        $orderInfo .= $itemInfo;

                        $cnt = $cnt + 1;
                        $orderInfo .= "\n" . '[orderline' . $cnt . ']' . "\n";
                        $orderInfo .= 'productcode=' . $order->getShippingSku() . "\n";
                        $orderInfo .= 'recipeindicator=' . '0' . "\n";
                        $orderInfo .= 'recipetitle=' . '' . "\n";
                        $orderInfo .= 'productdescription=' . $order->getShippingDescription() . "\n";
                        $orderInfo .= 'maxpacksize=1' . "\n";
                        $orderInfo .= 'minpacksize=1' . "\n";
                        $orderInfo .= 'maxpackcost=1' . "\n";
                        $orderInfo .= 'minpackcost=0' . "\n";
                        $orderInfo .= 'unitcost=' . number_format($order->getShippingTaxAmount() + $order->getShippingAmount(), 2, '.', '') . "\n";
                        $orderInfo .= 'discountcodes=' . $discountcodes . "\n";
                        $orderInfo .= 'qtyordered=1' . "\n";
                        $orderInfo .= 'qtysupplied=1' . "\n";
                        $orderInfo .= 'sellunit=1' . "\n";
                        $orderInfo .= 'linecost=' . number_format($order->getShippingTaxAmount() + $order->getShippingAmount(), 2, '.', '') . "\n";
                        $orderInfo .= 'vatcode=3' . "\n";
                        $orderInfo .= 'vatamount=0.00' . "\n";

                        $orderInfo .= '[END]';

                        if ($file = $this->createFile($orderInfo, $order->getIncrementId())) {
                            $order->setExportStatus(2);
                            $order->save();
                            $orderCreated[$order->getId()] = $file;
                        } else {
                            Mage::throwException(Mage::helper('exportorder')->__('Unable to create export order File.'));
                        }
                    }
                    $this->uploadFileUsingFtp($orderCreated);
                } else {
                    Mage::throwException(Mage::helper('exportorder')->__('Unable to Exprort order.Order status must be "Processing" Or "Complete".'));
                }

            } catch (Exception $exception) {
                $orderCollection = $this->getOrderCollection($order_ids);
                $mesg = '';
                foreach ($orderCollection as $order) {
                    $order = $this->getOrder($order->getId());
                    $mesg .= 'Unable to export order reference #' . $order->getIncrementId() . ' via FTP. The order export has failed.</br>';
                }
                $this->sendMail($mesg);
                throw $exception;
            }
        }
    }

    /* create order.txt file */
    public function createFile($orderInfo, $incrementId)
    {
        try {
            $io = new Varien_Io_File();
            $path = Mage::getBaseDir('var') . DS . 'export' . DS . 'order';
            $file = $path . DS . 'magento' . '_' . $incrementId . '.txt';
            $io->setAllowCreateFolders(true);
            $io->open(array('path' => $path));
            $io->streamOpen($file, 'w+');
            $io->streamLock(true);
            $io->streamWrite($orderInfo);
            $io->streamClose();
            $io->close($file);
        } catch (Exception $exception) {
            throw $exception;
        }
        return $file;
    }

    /*File Upload from Local to Ftp server*/

    public function uploadFileUsingFtp($orderCreated)
    {
        $folder = Mage::getStoreConfig('export_section/exportorder_group/ftp_folder');
        $ftp = new Varien_Io_Ftp();
        try {
            if (empty($this->_ftpDetails['host'])) {
                Mage::throwException(Mage::helper('exportorder')->__('Empty host specified'));
            }

            if (empty($this->_ftpDetails['username'])) {
                Mage::throwException(Mage::helper('exportorder')->__('Empty username specified'));
            }

            if (empty($this->_ftpDetails['password'])) {
                Mage::throwException(Mage::helper('exportorder')->__('Empty password specified'));
            }

            $ftpArray = array(
                'host' => trim($this->_ftpDetails['host']),
                'user' => trim($this->_ftpDetails['username']),
                'password' => trim($this->_ftpDetails['password']),
                //'port'      => trim($this->_ftpDetails['port']),
            );

            $connId = ftp_connect($ftpArray['host']);
            if ($connId) {
                ftp_login($connId, $ftpArray['user'], $ftpArray['password']);
            } else {
                Mage::throwException(Mage::helper('exportorder')->__('Host is unable to connect FTP.'));
            }
            if (strtolower($this->_ftpDetails['type']) != "ftp") {

                $ftpArray['ssl'] = 1;
            }
            if (!$ftp->open($ftpArray)) {

                Mage::throwException(Mage::helper('exportorder')->__('Unable to connect FTP.'));
            }
            foreach ($orderCreated as $oids => $filename) {
                $UpdateStatusorderId = $oids;
                $flocal = fopen($filename, 'r');
                $files = basename($filename);

                if ($ftp->write($folder . '/' . $files, $flocal)) {
                    $orderObj = Mage::getModel('sales/order')->load($UpdateStatusorderId)->setUploadStatus(3);
                    $orderObj->save();
                } else {
                    if (Mage::getSingleton('core/session')->getExportOrderWhenCheckout() != 'true') {
                        $orderObj = Mage::getModel('sales/order')->load($UpdateStatusorderId)->setUploadStatus(4);
                        $orderObj->save();
                        Mage::throwException(Mage::helper('exportorder')->__('Unable to upload file through FTP.'));
                    }
                }
            }

            $ftp->close();
        } catch (Exception $exception) {

            throw $exception;
        }
        Mage::getSingleton('core/session')->unsExportOrderWhenCheckout();
        return true;
    }

    public function exportOrderData()
    {

        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = "SELECT entity_id FROM " . Mage::getSingleton('core/resource')->getTableName('sales/order') . " WHERE (status IN('processing', 'complete')) AND (((upload_status = '1') OR (upload_status = '2') OR (upload_status = '4')))";
        $orderIds = $connection->fetchCol($sql);
        try {
            $orderCollection = $this->getOrderCollection($orderIds);
            $recipenewsletter = $keepdetailsonfile = 'n';
            $discountcodes = "na";
            $orderCreated = array();
            if ($orderCollection->getSize()) {
                foreach ($orderCollection as $order) {
                    $order = $this->getOrder($order->getId());
                    $customer = $this->getCustomer($order->getCustomerId());

                    $billingAddress = $order->getBillingAddress();
                    $shippingAddress = $order->getShippingAddress();
                    $orderedItems = $order->getAllItems();

                    $startsWith = substr($order->getIncrementId(), 0, 3);
                    if ($startsWith == 'Amz') {
                        $accountNo = 798888;
                    } else if ($startsWith == 'Eba') {
                        $accountNo = 798887;
                    } else {
                        $accountNo = 888;
                    }

                    $orderInfo = '[customerdetails]' . "\n";
                    $orderInfo .= 'title=' . $billingAddress->getPrefix() . "\n";
                    $orderInfo .= 'firstname=' . $billingAddress->getFirstname() . "\n";
                    $orderInfo .= 'surname=' . $billingAddress->getLastname() . "\n";
                    $orderInfo .= 'address1=' . $billingAddress->getStreet(1) . "\n";
                    $orderInfo .= 'address2=' . $billingAddress->getStreet(2) . "\n";
                    $orderInfo .= 'address3=' . '' . "\n";
                    $orderInfo .= 'fax=' . $billingAddress->getFax() . "\n";
                    $orderInfo .= 'town=' . $billingAddress->getCity() . "\n";
                    $orderInfo .= 'postcode=' . $billingAddress->getPostcode() . "\n";
                    $orderInfo .= 'county=' . $billingAddress->getRegion() . "\n";
                    $orderInfo .= 'country=' . $billingAddress->getCountryId() . "\n";
                    $orderInfo .= 'email=' . $billingAddress->getEmail() . "\n";
                    $orderInfo .= 'telephone=' . (int)$billingAddress->getTelephone() . "\n";
                    $orderInfo .= 'mobilenumber=' . (int)$billingAddress->getMobileNumber() . "\n";
                    $orderInfo .= 'customerref=' . $customer->getId() . "\n";
                    $orderInfo .= 'webref=' . $order->getIncrementId() . "\n";
                    $orderInfo .= 'orderdate=' . date('dmY', strtotime($order->getCreatedAt())) . "\n";
                    $orderInfo .= 'ordertime=' . date('His', strtotime($order->getCreatedAt())) . "\n";
                    $orderInfo .= 'ipaddress=' . $order->getRemoteIp() . "\n";
                    $orderInfo .= 'deliveryoption=' . $order->getShippingDescription() . "\n";
                    $orderInfo .= 'recipenewsletter=' . $recipenewsletter . "\n";
                    $orderInfo .= 'keepdetailsonfile=' . $keepdetailsonfile . "\n";
                    $orderInfo .= "\n\n";


                    $orderInfo .= '[deliverydetails]' . "\n";
                    $orderInfo .= 'locno=79' . "\n";
                    $orderInfo .= 'accountno=' . $accountNo . "\n";
                    $orderInfo .= 'contactname=' . $shippingAddress->getFirstname() . " " . $shippingAddress->getLastname() . "\n";
                    $orderInfo .= 'address1=' . $shippingAddress->getStreet(1) . "\n";
                    $orderInfo .= 'address2=' . $shippingAddress->getStreet(2) . "\n";
                    $orderInfo .= 'address3=' . '' . "\n";
                    $orderInfo .= 'fax=' . $shippingAddress->getFax() . "\n";
                    $orderInfo .= 'town=' . $shippingAddress->getCity() . "\n";
                    $orderInfo .= 'postcode=' . $shippingAddress->getPostcode() . "\n";
                    $orderInfo .= 'county=' . $shippingAddress->getRegion() . "\n";
                    $orderInfo .= 'country=' . $shippingAddress->getCountryId() . "\n";
                    $orderInfo .= 'telephone=' . (int)$shippingAddress->getTelephone() . "\n";
                    $orderInfo .= 'mobilenumber=' . (int)$shippingAddress->getMobileNumber() . "\n";
                    $orderInfo .= 'email=' . $shippingAddress->getEmail() . "\n";
                    $orderInfo .= "\n\n";

                    $itemInfo = '';
                    $goodssubtotalexvat = 0;
                    $cnt = 0;
                    foreach ($orderedItems as $item) {
                        $productType = $item->getProductType();
                        if ($productType == 'simple') {

                            $qty = (int)$item->getQtyOrdered();
                            $qty = $qty - $item->getQtyRefunded();

                            $sku = $item->getSku();

                            if ($qty < 1)
                                continue;

                            $cnt++;

                            $itemPricewithoutDiscount = ($item->getOriginalPrice() * $qty) - $item->getDiscountAmount();
                            $goodssubtotalexvat += $itemPricewithoutDiscount;

                            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
                            if ($product) {
                                $price = number_format($item->getOriginalPrice(), 2, '.', '');
                                $size = $product->getProductsize();
                                $unitcost = $itemPricewithoutDiscount;//$product->getPrice();
                                $vatAmmount = $item->getTaxAmount();

                                //Store Order Product Data in array
                                $itemInfo .= "\n" . '[orderline' . "$cnt" . ']' . "\n";
                                $itemInfo .= 'productcode=' . $sku . "\n";
                                $itemInfo .= 'recipeindicator=' . '0' . "\n";
                                $itemInfo .= 'recipetitle=' . '' . "\n";
                                $itemInfo .= 'productdescription=' . strip_tags($product->getName()) . "\n";
                                $itemInfo .= 'maxpacksize=1' . "\n";
                                $itemInfo .= 'minpacksize=1' . "\n";
                                $itemInfo .= 'maxpackcost=1' . "\n";
                                $itemInfo .= 'minpackcost=0' . "\n";
                                $itemInfo .= 'unitcost=' . $price . "\n";
                                $itemInfo .= 'discountcodes=' . $discountcodes . "\n";
                                $itemInfo .= 'qtyordered=' . $qty . "\n";
                                $itemInfo .= 'qtysupplied=' . $qty . "\n";
                                $itemInfo .= 'Sellunit=1' . "\n";
                                $itemInfo .= 'linecost=' . number_format($price * $qty, 2, '.', '') . "\n";
                                $itemInfo .= ($product->getTaxClassId() == 21) ? "vatcode=1 \n" : "vatcode=3 \n";
                                if ($product->getTaxClassId() == 21) {
                                    $itemInfo .= 'vatamount=' . number_format($vatAmmount, 2, '.', '') . "\n";
                                } else {
                                    $itemInfo .= 'vatamount=0' . "\n";
                                }
                            } else {
                                $itemInfo .= "\n" . '[orderline' . "$cnt" . ']' . "\n";
                                $itemInfo .= 'product issue' . "\n";
                            }
                        }
                    }

                    $orderInfo .= "[orderdetails]" . "\n";
                    $orderInfo .= 'orderlines=' . ($cnt + 1) . "\n";
                    $orderInfo .= 'numspecialinstructionlines=0' . "\n";
                    //$orderInfo .= 'specialinstructionsline1='.''."\n";
                    $orderInfo .= 'goodssubtotalexvat=' . number_format($goodssubtotalexvat, 2, '.', '') . "\n";
                    $orderInfo .= 'goodssubtotalvat=' . number_format(($order->getTaxAmount() - $order->getBaseTaxRefunded()), 2, '.', '') . "\n";
                    $orderInfo .= 'carriagesubtotal=' . number_format($order->getShippingAmount(), 2, '.', '') . "\n";
                    $orderInfo .= 'grossordervalue=' . number_format(($order->getGrandTotal() - $order->getBaseTotalRefunded()), 2, '.', '') . "\n";

                    $orderInfo .= "\n\n";

                    $orderInfo .= $itemInfo;

                    $cnt = $cnt + 1;
                    $orderInfo .= "\n" . '[orderline' . $cnt . ']' . "\n";
                    $orderInfo .= 'productcode=' . $order->getShippingSku() . "\n";
                    $orderInfo .= 'recipeindicator=' . '0' . "\n";
                    $orderInfo .= 'recipetitle=' . '' . "\n";
                    $orderInfo .= 'productdescription=' . $order->getShippingDescription() . "\n";
                    $orderInfo .= 'maxpacksize=1' . "\n";
                    $orderInfo .= 'minpacksize=1' . "\n";
                    $orderInfo .= 'maxpackcost=1' . "\n";
                    $orderInfo .= 'minpackcost=0' . "\n";
                    $orderInfo .= 'unitcost=' . number_format($order->getShippingTaxAmount() + $order->getShippingAmount(), 2, '.', '') . "\n";
                    $orderInfo .= 'discountcodes=' . $discountcodes . "\n";
                    $orderInfo .= 'qtyordered=1' . "\n";
                    $orderInfo .= 'qtysupplied=1' . "\n";
                    $orderInfo .= 'sellunit=1' . "\n";
                    $orderInfo .= 'linecost=' . number_format($order->getShippingTaxAmount() + $order->getShippingAmount(), 2, '.', '') . "\n";
                    $orderInfo .= 'vatcode=3' . "\n";
                    $orderInfo .= 'vatamount=0.00' . "\n";
                    $orderInfo .= '[END]';
                    $orderInfo .= '[AutoByCronJob]';
                    if ($order->getExportStatus() == 2) {
                        $path = Mage::getBaseDir('var') . DS . 'export' . DS . 'order';
                        $file = $path . DS . 'magento' . '_' . $order->getIncrementId() . '.txt';
                        if (file_exists($file)) {
                            $orderCreated[$order->getId()] = $file;
                        } else {
                            if ($file = $this->createFile($orderInfo, $order->getIncrementId())) {
                                $orderCreated[$order->getId()] = $file;
                            }
                        }
                    } else if ($file = $this->createFile($orderInfo, $order->getIncrementId())) {
                        $order->setExportStatus(2);
                        $order->save();
                        $orderCreated[$order->getId()] = $file;
                    } else {
                        Mage::throwException(Mage::helper('exportorder')->__('Unable to create export order File.'));
                    }
                }
                $this->uploadFileUsingFtp($orderCreated);
            } else {
                Mage::throwException(Mage::helper('exportorder')->__('Unable to Exprort order.Order status must be "Processing" Or "Complete".'));
            }
        } catch (Exception $exception) {
            $this->sendMail($exception->getMessage());
        }
    }

    public function sendMail($errrMsg)
    {
        $postObject = new Varien_Object();
        $maildata = array();
        $maildata['subject'] = "Admin Notification Email";
        $maildata['error'] = $errrMsg;
        $postObject->setData($maildata);

        $mailTemplate = Mage::getModel('core/email_template')->setDesignConfig(array('area' => 'backend'));
        $email = Mage::getStoreConfig('export_section/exportorder_group/admin_email');

        $sender = array('name' => 'Admin', 'email' => $email);
        $mailTemplate->sendTransactional(
            self::XML_PATH_EMAIL_ERROR_TEMPLATE,
            $sender,
            $email,
            "Store Admin",
            array('data' => $postObject)
        );
        if (!$mailTemplate->getSentSuccess()) {
            Mage::getSingleton('adminhtml/session')->addError($this->__("mail Sending Fail"));
        }
        $mailTemplate->setTranslateInline(true);
    }
}
