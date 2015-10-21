<?php

class Wingyip_Importproducts_Model_Importproducts extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('importproducts/importproducts');
    }

    const XML_PATH_EMAIL_ADMIN = 'trans_email/ident_general/email';
    const XML_PATH_EMAIL_LOG_TEMPLATE = 'importproducts_log_email_template';
    const XML_PATH_EMAIL_ADMINLIST = "importproducts/general/adminemail";


    public $newSimpleProductAry = array();

    public $updatedSimpleProductAry = array();

    public $newGroupedProductAry = array();

    public $updatedGroupedProductAry = array();

    public $updatedGroupedAttributesAry = array();

    public $updatedAssociatedSimpleAry = array();

    public $rootCategoryPath = '1/2';

    public $prdctCategoryData = array();

    public $prdctSubCategoryData = array();

    public $categoryAndIdMapping = array();

    public $csvGrpToSimDataAry = array();

    public $dbGrpToSimDataAry = array();

    public $csvSimPrdctDataAry = array();

    public $productIdToSkuAry = array();

    public $simProductsNeedToAdd = array();

    public $grpProductsNeedToAdd = array();

    public $simProductsNeedToDelete = array();

    public $grpProductsNeedToDelete = array();

    public $simProductsNeedToHide = array();

    public $csvproductArr = array();

    public $QtyupdatedSimpleProductAry = array();

    protected $_csvBedData = array();

    protected $_noBrand = array();

    protected $_log = '';

    protected $_bedData = '';

    protected $_isStatus = true;

    /////////////////////////   Get CSVs which are in Pending Status    ////////////////////////////////////////////////////////////////////////
    public function getCsvFiles()
    {
        $dirFiles = $this->getCollection()->addFieldToFilter('status', 1)->getFirstItem();
        $checkhavefileProcessing = $this->getCollection()->addFieldToFilter('status', 2);
        if ($checkhavefileProcessing->count() > 0) {
            $flag = false;
            foreach ($checkhavefileProcessing as $process_file) {
                $date_now = date('Y-m-d h:i:s');
                $update_tim = $process_file->getData('update_time');
                if (strtotime($date_now) - strtotime($update_tim) > 1800) {
                    $model = $this->load($process_file->getData('importproducts_id'));
                    try {
                        $model->setStatus('4');
                        $model->save();
                    } catch (Exception $e) {
                        Mage::log($e->getMessage());
                    }
                    $flag = false;
                } else {
                    $flag = true;
                }
            }
            if ($flag) {
                exit;
            }
        }
        if (count($dirFiles)) {

            $isInProcessing = 0;
            $pendingFiles = array();
            //$i = 0;
            //foreach($dirFiles as $dirFile){

            if ($dirFiles->getStatus() == 2) {
                //  Check any CSV is in Processing
                $isInProcessing = 1;
                $processingDirFileExp = explode('/', $dirFiles->getFilename());
                $processingDirString = end($processingDirFileExp);
                exit;
            } elseif ($dirFiles->getStatus() == 1) {
                //  Get CSVs which are in pedning status
                $pendingFiles[0]['id'] = $dirFiles->getId();
                $pendingFiles[0]['name'] = $dirFiles->getFilename();
                $pendingFiles[0]['logfile'] = strtolower(str_replace(' ', '', $dirFiles->getTitle())) . time() . '.log';
                //$i++;
            }
            //}
            if ($isInProcessing) {
                // echo "Import Failed". "\n"; ;
                //echo "File : ".$processingDirString." is in Processing.". "\n";
                exit;
            } else {
                if (count($pendingFiles)) {
                    return $pendingFiles;
                } else {
                    //echo "There isn't any File with Pending Status.";
                    exit;
                }
            }
        } else {
            //echo "There isn't any File to Import.";
            exit;
        }

    }

    /////////////////////////   Get CSVs Path    ///////////////////////////////////////////////////////////////////////////////////////////////
    public function getCsvFilesPath()
    {
        $csvPath = BP . DS . 'var' . DS . 'import' . DS;//. 'csvs' . DS;
        return $csvPath;
    }

    /////////////////////////   Function which will be called by Cron     //////////////////////////////////////////////////////////////////////
    public function importProducts()
    {
        //DebugBreak();
        ini_set("auto_detect_line_endings", "1");
        $csvAry = $this->getCsvFiles();
        Mage::log("\n Count csvAry: ".count($csvAry), null, 'logcasesuccess.log');
        /* disable auto reindex if there is any pending file */
        if (count($csvAry) > 0) {
            Mage::log("\n Case disable_auto_reindex", null, 'logcasesuccess.log');
            $this->disableAutoReindex();
        }
        $csvCounter = 0; //echo "<pre>"; print_r($csvAry); exit;
        foreach ($csvAry as $csvName) {
            // Reset All Private Variables
            if ($csvCounter) {
                $this->resetAllVariables();
            }
            // Set CSV's status as Processing
            $csvFileObj = $this->load($csvName['id']);
            $csvFileObj->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
            $csvFileObj->setCreatedTime(date('Y-m-d h:i:s'));
            $csvFileObj->setUpdateTime(NULL);
            $csvFileObj->save();
            if ($processedCsvDataAsAry = $this->processCsvData($csvName['name'])) {
                $newSimpleProductString = implode(', ', $this->newSimpleProductAry);
                $updatedSimpleProductString = implode(', ', $this->updatedSimpleProductAry);
                $QtyupdatedSimpleProductString = implode(', ', $this->QtyupdatedSimpleProductAry);
                $BadDataProductString = implode(', ', $this->_csvBedData);
                // Mage::log("////////////////////////////////////////////////////", null, $csvName['logfile']);
                // Mage::log("Processed CSV : ".$csvName['name'], null, $csvName['logfile']);
                // if(count($this->newSimpleProductAry)){
                //     Mage::log("Added Products : Count=".count($this->newSimpleProductAry)." \n" . $newSimpleProductString, null, $csvName['logfile']);
                // }
                // if(count($this->updatedSimpleProductAry)){
                //     Mage::log("Updated Products : Count=".count($this->updatedSimpleProductAry)." \n" . $updatedSimpleProductString, null, $csvName['logfile']);
                // }
                // if(count($this->QtyupdatedSimpleProductAry)){
                //     Mage::log("Qty Updated Products : Count=".count($this->QtyupdatedSimpleProductAry)." \n" . $QtyupdatedSimpleProductString, null, $csvName['logfile']);
                // }
                // if(count($this->_csvBedData)){
                //     Mage::log("Bad Data in Products : Count=".count($this->_csvBedData)." \n" . $BadDataProductString, null, $csvName['logfile']);
                // }
                // Mage::log("////////////////////////////////////////////////////", null, $csvName['logfile']);
                $csvFileObj2 = $this->load($csvName['id']);
                $csvFileObj2->setStatus(3);
                $csvFileObj->setUpdateTime(date('Y-m-d h:i:s'));
                $email = Mage::getStoreConfig(self::XML_PATH_EMAIL_ADMINLIST);
                $message = "Successful import" . "\n" . $this->_bedData;
                $this->sendMailAttach($email, "Product Import Success Report", $message, "import.txt");

                $csvFileObj2->save();
            } else {
                $csvFileObj2 = $this->load($csvName['id']);
                $csvFileObj2->setStatus(4);
                $csvFileObj->setUpdateTime(date('Y-m-d h:i:s'));
                $csvFileObj2->save();
            }
            if ($this->_isStatus == false) {
                $email = Mage::getStoreConfig(self::XML_PATH_EMAIL_ADMINLIST);
                $message = date("d-m-Y H:i:s") . " - Import Failed. Because File is No End";
                mail($email, "Product Import Failed", $message);
                $csvFileObj2->save();
            }
            /* log send in mail end*/
            $csvCounter++;
        }

        /* reindex all after importing */
        $this->reindexAll();

        /* enable auto reindex after importing */
        $this->enableAutoReindex();

//        Mage::app()->getResponse()->setRedirect(str_replace("/index.php", "", Mage::getBaseUrl()) . 'import.txt')->sendResponse();
    }

    /**
     * @return get all import product have status is 2
     * it use check for file importproduct.php
     */
    public function getRunningImportProduct()
    {
        $getCollection = $this->getCollection()->addFieldToFilter('status', 2);
        return count($getCollection);
    }

    private function disableAutoReindex()
    {
        $indexCollection = Mage::getModel('index/process')->getCollection();
        foreach ($indexCollection as $index) {
            $index->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
        }
    }

    private function reindexAll()
    {
        $indexCollection = Mage::getModel('index/process')->getCollection();
        foreach ($indexCollection as $index) {
            $index->reindexAll();
        }
    }

    private function enableAutoReindex()
    {
        $indexCollection = Mage::getModel('index/process')->getCollection();
        foreach ($indexCollection as $index) {
            $index->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
        }
    }

    public function processCsvData($curCsvName)
    {
        //DebugBreak();
        /////////////////////////   Generating Array of Current CSV Data    ////////////////////////////////////////////////////////////////////
        $curExpCsvName = explode('/', $curCsvName);
        $filePath = $this->getCsvFilesPath() . end($curExpCsvName);
        $fp = fopen($filePath, 'r');
        if(!$fp){
            $csvFileObj = $this->load($curCsvName['id']);
            $csvFileObj->setStatus(4);
            $this->_isStatus = false;
            Mage::log("\n Case can not open file", null, 'logcasesuccess.log');
            return;
        }
        $prdctData = array();
        $count = 0;
        $i = 0;

        $_isValid = !$this->isNoEndFile($filePath);

        if ($_isValid == true) {
            while ($csvLine = fgets($fp, 1024)) {
                if (trim($csvLine) == "[product]" || trim($csvLine) == "[END]") {
                    $i++;
                } else {
                    if (strlen(trim($csvLine))) {
                        $prdctData[$i][] = $csvLine;

                    }
                }
                $count++;
            }
        } else {
            $csvFileObj = $this->load($curCsvName['id']);
            $csvFileObj->setStatus(4);
            $this->_isStatus = false;
            return;
        }

        fclose($fp) or die("Can't close File");

        //  Collect All Skus Of Csv's Simple + Grouped Products
        $csvSimProductsAry = array();
        foreach ($prdctData as $prdctDataItem) {
            $simProductsAry = $this->getSimpleProducts($prdctDataItem);
            if ($simProductsAry === false) {
                continue;
            }
            $csvSimProductsAry[] = trim($simProductsAry);
            $csvSimProductsQtyAry[trim($simProductsAry)] = number_format($this->csvproductArr[trim($simProductsAry)]['stockl79'], 4, ".", "");
            $this->csvSimPrdctDataAry[trim($simProductsAry)] = $this->csvproductArr[trim($simProductsAry)];

        }
        $dbSimProductsAry = array();
        $curCsvPrdctsCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('price')->joinField(
            'qty',
            'cataloginventory/stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );
        foreach ($curCsvPrdctsCollection as $curCsvPrdct) {
            if ($curCsvPrdct->getTypeId() == 'simple') {
                if (!is_null($curCsvPrdct->getSku())) {
                    $this->productIdToSkuAry[$curCsvPrdct->getSku()] = $curCsvPrdct->getId();
                    $curSimpleQty = explode('_', $curCsvPrdct->getSku());
                    $dbSimProductsAry[] = $curCsvPrdct->getSku();
                    $dbSimProductDataAry = array();
                    $dbSimProductDataAry['sku'] = $curCsvPrdct->getSku();
                    $dbSimProductqtyAry[$curCsvPrdct->getSku()] = $curCsvPrdct->getQty();
                    $dbSimProductDataAry['price'] = $curCsvPrdct->getPrice();
                    $this->dbGrpToSimDataAry[$curSimpleQty[0]][] = $dbSimProductDataAry;
                }
            }
        }

        try {
            $this->simProductsNeedToUpdateQty = array_diff_assoc($csvSimProductsQtyAry, $dbSimProductqtyAry);
            $this->simProductsNeedToAdd = array_diff($csvSimProductsAry, $dbSimProductsAry);
            $this->simProductsNeedToUpdate = array_intersect($csvSimProductsAry, $dbSimProductsAry);
            Mage::log("\n Case 01", null, 'logcasesuccess.log');
            $this->createUpdateSimpleProduct($this->simProductsNeedToAdd, true);
            Mage::log("\n Case 02", null, 'logcasesuccess.log');
            $this->createUpdateSimpleProduct($this->simProductsNeedToUpdateQty, false, true);
            Mage::log("\n Case 03", null, 'logcasesuccess.log');
            $this->createUpdateSimpleProduct($this->simProductsNeedToUpdate, false);
            Mage::log("\n Case 04", null, 'logcasesuccess.log');
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }

        foreach ($dbSimProductsAry as $value) {
            if (!in_array($value, $csvSimProductsAry)) {
                $this->simProductsNeedToHide[] = $value;
            }
        }
        Mage::log("\n Case 05", null, 'logcasesuccess.log');
        //$this->simProductsNeedToHide=array('391086');
        $this->setStockAndStatus($this->simProductsNeedToHide);
        Mage::log("\n Case 06", null, 'logcasesuccess.log');
        $this->hidePrdctsWhichNotInCsv($this->simProductsNeedToHide);
        Mage::log("\n Case 07", null, 'logcasesuccess.log');
        $this->createFile('import.txt', $this->_log);
        Mage::log("\n Case 08", null, 'logcasesuccess.log');
        Mage::log($this->_log, null, 'importproducts.log');

        return $prdctData;
    }

    public function setStockAndStatus($arrayProductsHidden = array())
    {
        foreach ($this->simProductsNeedToUpdateQty as $key => $value) {
            $arrayProducts[] = (string)$key;
        }
        $arrayProducts = array_unique(array_merge($arrayProducts, $this->simProductsNeedToAdd, $this->simProductsNeedToUpdate));
        foreach ($arrayProducts as $arrayProduct) {
            if (!in_array($arrayProduct, $arrayProductsHidden)) {
                $result[] = $arrayProduct;
            }
        }
        if (count($result) > 0) {
            $loadProductBySku = Mage::getModel('catalog/product');
            $product = Mage::getModel('catalog/product');
            $stock = Mage::getModel('cataloginventory/stock_item');

            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $writeConnection = $resource->getConnection('core_write');
            foreach ($result as $sku) {
                $getIdProductBySku = $loadProductBySku->getIdBySku($sku);
                if($getIdProductBySku){
                    $storeView = array(1, 0);
                    foreach ($storeView as $storeId) {
                        $product = $product->setStoreId($storeId)->load($getIdProductBySku);
                        if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                            if ($product->getPrice() > 0) {
                                // enabled the product
                                $entity_type = 'catalog_product';
                                $attributeCode = 'status';
                                $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode($entity_type, $attributeCode);
                                $tableNameCatalogInt = $resource->getTableName('catalog_product_entity_int');
                                $queryStatus = 'SELECT * FROM ' . $tableNameCatalogInt . ' where entity_id=' . $product->getId() . ' and attribute_id=' . $attributeModel->getId();
                                $resultstatus = $readConnection->fetchOne($queryStatus);
                                if ($resultstatus) {
                                    $updateDisableQuery = "UPDATE {$tableNameCatalogInt} SET value  = '1' WHERE entity_id=" . $product->getId() . " and attribute_id=" . $attributeModel->getId();
                                    $writeConnection->query($updateDisableQuery);
                                }
                            }
                        }
                    }
                    /* update the query Start */
                    $tableName = $resource->getTableName('cataloginventory_stock_item');
                    $query = 'SELECT * FROM ' . $tableName . ' where product_id=' . $product->getId();
                    $results = $readConnection->fetchOne($query);

                    $stock = $stock->loadByProduct($product);
                    $isInStock = 0;
                    if (number_format($stock->getQty()) > 0) {
                        $isInStock = 1;
                    }
                    if ($results) {
                        if ($this->isUpdatedQty($product)) {
                            $updateQuery = "UPDATE {$tableName} SET is_in_stock  = '" . $isInStock . "' WHERE product_id=" . $product->getId();
                            $writeConnection->query($updateQuery);
                        }
                    }
                }

            }
        }
        unset($resource);
    }

    /////////////////////////   For Finding Associated Products of specific Group Product from CSV      /////////////////////////////////////
    public function getSimpleProducts($prdctData, $onlySku = false)
    {


        $category = "";
        foreach ($prdctData as $prod) {
            $str = explode("=", trim($prod));
            $simpleArray[$str[0]] = $str[1];
            if ($str[0] == "productcode") {
                $simpleProducts = $str[1];
            }
        }

        if ($this->isBadData($simpleArray)) {

            $this->_csvBedData[] = trim($simpleProducts);
            $this->_bedData .= "SKU: " . $simpleArray['productcode'] . " is Bad Data\n";

            $this->createFile('baddata.txt', $this->_bedData);

            return false;

        } else {
            $this->csvproductArr[trim($simpleProducts)] = $simpleArray;
        }

        if ($simpleArray['sellbrand'] == 0 || $simpleArray['sellbrand'] == '') {
            $this->_noBrand[] = trim($simpleProducts);
        }
        return $simpleProducts;
    }

    public function isNoEndFile($filePath)
    {
        $data = file($filePath);
        $line = trim($data[count($data) - 1]);

        return $line != '[END]';
    }

    public function isBadData($simpleArray)
    {
        // if a record not enough 62 fields
        if (count($simpleArray) < 62) {
            return true;
        }

        // check if qty or price is empty
        if (empty($simpleArray['stockl79']) || (empty($simpleArray['retail-price']) && empty($simpleArray['sell-price10']))) {
            return false;
        }

        return false;
    }

    /////////////////////////   Create Simple Products which are not created yet      //////////////////////////////////////////////////////////
    /**
     * @param $simpleProductSkus
     * @param bool $forceCreate
     * @param bool $qtyUpdate
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function createUpdateSimpleProduct($simpleProductSkus, $forceCreate = false, $qtyUpdate = false)
    {
        if (count($simpleProductSkus) > 0) {

            $header = "\nList Product display by SKU/ TITLE / PRICE / STOCK\n";
            $insertProduct = $header . "\nNewly created products\n";
            $updatedProducts = "\nUpdated products\n";

            //Missing infomation
            $titleMissing = "\n\n\n\n\n\n-------Missing Infomation-------";
            $missingNoPrice = "\nMissing Prices\n";
            $missingDescription = "\nMissing Descriptions\n";
            $missingImages = "\nMissing Images\n";
            $missingSubCategory = "\nMissing Categorys\n";
            $missingBrand = "\nMissing Brand\n";

            $messageInfo = "Import Report " . date("d-m-Y H:i:s");
            foreach ($simpleProductSkus as $key => $simpleSku) {
                $isNewSimple = 0;
                $oldPrice = 0;
                $oldDescription = "";
                $oldImage = "";
                $oldCategory = 0;
                if ($forceCreate) {
                    $simProduct = Mage::getModel('catalog/product');
                    $isNewSimple = 1;
                    $skuImported = $simpleSku;
                    $sPrdctCsvData = $this->csvSimPrdctDataAry[$simpleSku];
                    $simProduct->setData($sPrdctCsvData);
                    $simProduct->setSku($simpleSku);
                    $simProduct->setTypeId('simple');
                    $simProduct->setAttributeSetId(4);     // Default
                    $simProduct->setWebsiteIds(array(1));
                    //$simProduct->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
                    $simProductName = $sPrdctCsvData['productname'];
                    $simProduct->setName($simProductName);
                    $simProduct->setDescription($simProductName);
                    $simProduct->setShortDescription($simProductName);
                    $simProduct->setWeight($sPrdctCsvData['productweight']);
                    $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);     // Enabled
                    $simProduct->setVisibility(4);     // Not Visible Individually
                    //$simProduct->setPrice($sPrdctCsvData['retail-price']);
                    $simProduct->setCost($sPrdctCsvData['retail-price']);
                    //PRICE
                    $_isPrice = false;
                    if ($sPrdctCsvData['l79sell-price10'] > 0) {
                        $simProduct->setPrice($sPrdctCsvData['l79sell-price10']);
                    } else if ($sPrdctCsvData['l79sell-price10'] == 0) {
                        if ($sPrdctCsvData['sell-price10'] > 0) {
                            $simProduct->setPrice($sPrdctCsvData['sell-price10']);
                        } else {
                            if ($simProduct->getUpdatePrice() == "1" && $sPrdctCsvData['sell-price10'] == 0) {
                                $_isPrice = true;
                                $this->_bedData .= "SKU: " . $simpleSku . " is Bad Data\n";
                            }
                        }
                    }
                    if ($_isPrice == true) {
                        $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                    } else {
                        //VATCODE
                        if (trim($sPrdctCsvData['vatcode']) == '1') {
                            $simProduct->setTaxClassId(21);     // Taxable Goods
                        } else if (trim($sPrdctCsvData['vatcode']) == '3') {
                            $simProduct->setTaxClassId(2);
                        } else {
                            $simProduct->setTaxClassId(0); //None
                        }
                        $stockData = $simProduct->getStockData();
                        if (trim($sPrdctCsvData['replacecode']) == "888") {
                            $stockData['qty'] = "10000";
                            $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                        } else {
                            $stockData['qty'] = $sPrdctCsvData['stockl79'] + $sPrdctCsvData['stockl1'];
                        }
                        if ($stockData['qty'] == null || $stockData['qty'] == 0) {
                            $stockData['is_in_stock'] = '0';
                            $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                        } else {
                            $stockData['is_in_stock'] = '1';
                        }
                        if ($simProduct->getQty() > 0) {
                            $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                        }
                        //if product has price = 0 then disable
                        if ($simProduct->getPrice() == 0) {
                            $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                        }
                        //if price has stock = 0 then disable
                        if ($stockData['is_in_stock'] == '0' || $stockData['qty'] == '0') {
                            $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                        }
                        //if product has a price > 0 and has stock > 0 then enable
                        if ($simProduct->getPrice() && $stockData['is_in_stock'] == '1') {
                            $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                        }
                        $simProduct->setStockData(array(
                                'use_config_manage_stock' => 1, //'Use config settings' checkbox
                                'manage_stock' => 1, //manage stock
                                'min_sale_qty' => 1, //Minimum Qty Allowed in Shopping Cart
                                'is_in_stock' => 1, //Stock Availability
                                'qty' => $stockData['qty'] //qty
                            )
                        );
                        $simProduct->save();
                        unset($this->simProductsNeedToUpdateQty[$simpleSku]);
                    }

                } else {
                    if (in_array($simpleSku, $this->simProductsNeedToUpdate) && !$qtyUpdate) {
                        $sPrdctCsvData = $this->csvSimPrdctDataAry[$simpleSku];
                        $skuImported = $simpleSku;
                        $simPrdctId = Mage::getModel("catalog/product")->getIdBySku($simpleSku);
                        $simProduct = Mage::getModel('catalog/product')->load($simPrdctId);
                        // echo '<pre>';
                        // print_r($simProduct);
                        // echo  '</pre>';
                        $oldPrice = $simProduct->getPrice();
                        $oldDescription = $simProduct->getDescription();
                        $oldImage = $simProduct->getImage();
                        $oldCategory = $simProduct->getCategoryIds();
                        //PRICE
                        $_isPr = false;
                        if ($sPrdctCsvData['l79sell-price10'] > 0) {
                            $simProduct->setPrice($sPrdctCsvData['l79sell-price10']);
                        } else if ($sPrdctCsvData['l79sell-price10'] == 0) {
                            if ($sPrdctCsvData['sell-price10'] > 0) {
                                $simProduct->setPrice($sPrdctCsvData['sell-price10']);
                            } else {
                                if ($simProduct->getUpdatePrice() == "1" && $sPrdctCsvData['sell-price10'] == 0) {
                                    $_isPr = true;
                                    $this->_bedData .= "SKU: " . $sPrdctCsvData['productcode'] . " is Bad Data\n";
                                }
                            }
                        }
                        //VATCODE
                        if ($_isPr == true) {
                            $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                        } else {
                            if (trim($sPrdctCsvData['vatcode']) == '1') {
                                $simProduct->setTaxClassId(21);     // Taxable Goods
                            } else if (trim($sPrdctCsvData['vatcode']) == '3') {
                                $simProduct->setTaxClassId(2);
                            } else {
                                $simProduct->setTaxClassId(0); //None
                            }
                            if ($simProduct->getPrice() > 0) {
                                $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

                            } else {
                                $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                            }
                            $simProduct->setWeight($sPrdctCsvData['productweight']);

                            $simProduct->setVisibility(4);

                            //
                            if (trim($sPrdctCsvData['replacecode']) == "888") {
                                $stockData['qty'] = "10000";

                                $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

                            } else {
                                $stockData['qty'] = $sPrdctCsvData['stockl79'] + $sPrdctCsvData['stockl1'];
                            }
                            if ($stockData['qty'] == null || $stockData['qty'] == '0') {
                                $stockData['is_in_stock'] = '0';
                                $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                            } else {
                                $stockData['is_in_stock'] = '1';

                                $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

                            }
                            //if price has stock = 0 then disable
                            if ($stockData['is_in_stock'] == '0' || $stockData['qty'] == '0') {
                                $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                            }
                            //if product has a price > 0 and has stock > 0 then enable
                            if ($simProduct->getPrice() > 0 && $stockData['is_in_stock'] == '1') {
                                $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                            }
                        }

                    }
                    $isQtySimpleUpdate = null;
                    if (array_key_exists($key, $this->simProductsNeedToUpdateQty) && $qtyUpdate) {
                        $sPrdctCsvData = $this->csvSimProductsQtyAry[$key];
                        $sPrdctMainCsvData = $this->csvSimPrdctDataAry[$key];
                        $skuImported = $key;
                        $simPrdctId = Mage::getModel("catalog/product")->getIdBySku($key);
                        if ($simPrdctId) {
                            $simProduct = Mage::getModel('catalog/product')->load($simPrdctId);
                            /* update the query Start */
                            $resource = Mage::getSingleton('core/resource');
                            $readConnection = $resource->getConnection('core_read');
                            $writeConnection = $resource->getConnection('core_write');
                            $tableName = $resource->getTableName('cataloginventory_stock_item ');
                            $query = 'SELECT * FROM ' . $tableName . ' where product_id=' . $simPrdctId;
                            $results = $readConnection->fetchOne($query);
                            //PRICE
                            $_ispriCe = false;
                            if ($sPrdctMainCsvData['l79sell-price10'] > 0) {
                                $simProduct->setPrice($sPrdctMainCsvData['l79sell-price10']);
                            } else if ($sPrdctMainCsvData['l79sell-price10'] == 0) {
                                if ($sPrdctMainCsvData['sell-price10'] > 0) {
                                    $simProduct->setPrice($sPrdctMainCsvData['sell-price10']);
                                } else {
                                    if ($simProduct->getUpdatePrice() == "1" && $sPrdctMainCsvData['sell-price10'] == 0) {
                                        $_ispriCe = true;
                                        $this->_bedData .= "SKU: " . $sPrdctMainCsvData['productcode'] . " is Bad Data\n";
                                    }
                                    if ($simProduct->getUpdatePrice() == "1" && $sPrdctMainCsvData['sell-price10'] == 0) {

                                    }
                                }
                            }
                            if ($_ispriCe == true) {
                                $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                            } else {
                                //VATCODE
                                if (trim($sPrdctMainCsvData['vatcode']) == '1') {
                                    $simProduct->setTaxClassId(21);     // Taxable Goods
                                } else if (trim($sPrdctMainCsvData['vatcode']) == '3') {
                                    $simProduct->setTaxClassId(2);
                                } else {
                                    $simProduct->setTaxClassId(0); //None
                                }
                                if (trim($sPrdctMainCsvData['replacecode']) == "888") {
                                    $stockData['qty'] = "10000";
                                    $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                                } else {
                                    $stockData['qty'] = $sPrdctMainCsvData['stockl79'] + $sPrdctMainCsvData['stockl1'];
                                }
                                if ($stockData['qty'] == null || $stockData['qty'] == '0') {
                                    $stockData['is_in_stock'] = '0';

                                } else {
                                    $stockData['is_in_stock'] = '1';
                                    $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                                }
                                //if price has stock = 0 then disable
                                if ($stockData['is_in_stock'] == '0' || $stockData['qty'] == '0') {
                                    $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                                }
                                //if product has a price > 0 and has stock > 0 then enable
                                if ($simProduct->getPrice() > 0 && $stockData['is_in_stock'] == '1') {
                                    $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
                                }

                                if ($stockData['qty'] == '0') {
                                    $simProduct->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
                                }
                            }

                            if ($results) {
                                if ($this->isUpdatedQty($simProduct)) {
                                    $updateQuery = "UPDATE {$tableName} SET is_in_stock  = '" . $stockData['is_in_stock'] . "' , qty='" . $stockData['qty'] . "' WHERE product_id=" . $simPrdctId;
                                    $writeConnection->query($updateQuery);
                                }
                            }
                        }
                        $isQtySimpleUpdate = 1;
                    }
                }
                try {
                    if (!is_null($simProduct) && !empty($simProduct)) {
                        //$simProduct->save();
                        if (count($simProduct->getData()) > 0 && !$isQtySimpleUpdate) {
                            $simProduct->getResource()->save($simProduct);
                        }
                    }

                    $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($simProduct);
                    $infomationsProduct = $simpleSku . "/ " . trim($simProduct->getName()) . "/ " . $simProduct->getPrice() . "/ " . number_format($stock->getQty()) . "\n";
                    if ($isNewSimple) {
                        $insertProduct .= "Inserted Product: " . $infomationsProduct;
                        $this->newSimpleProductAry[] = $simpleSku;
                        $this->productIdToSkuAry[$simpleSku] = $simProduct->getId();
                        // echo "Added New Simple Product: " . $simpleSku . "\n";
                    } elseif ($isQtySimpleUpdate) {
                        $this->QtyupdatedSimpleProductAry[] = $simpleSku;
                    } else {
                        if ($simProduct->getPrice() == 0 || !$simProduct->getPrice()) {
                            $missingNoPrice .= "Missing Price : " . $infomationsProduct;
                        } elseif ($oldPrice == $simProduct->getPrice()) {
                            $updatedProducts .= "Update Product Price: " . $infomationsProduct;
                        }

                        if ($simProduct->getDescription() == "" || !$simProduct->getDescription()) {
                            $missingDescription .= "Missing Description : " . $infomationsProduct;
                        } elseif ($oldDescription == $simProduct->getDescription()) {
                            $updatedProducts .= "Update Product Description: " . $infomationsProduct;
                        }
                        if ($simProduct->getImage() == "no_selection" || $simProduct->getImage() == "" || $simProduct->getImage() == null || !$simProduct->getImage()) {
                            $missingImages .= "Missing Image : " . $infomationsProduct;
                        }
                        if (!$simProduct->getCategoryIds() || $simProduct->getCategoryIds() == 0) {
                            $missingSubCategory .= "Missing Categories : " . $infomationsProduct;
                        } elseif ($oldCategory == $simProduct->getCategoryIds()) {
                            $updatedProducts .= "Update Product Categories: " . $infomationsProduct;
                        }
                        if (!$simProduct->getBrand() || $simProduct->getBrand() == "") {
                            $missingBrand .= "Missing Brand: " . $infomationsProduct;
                        }
                        $this->updatedSimpleProductAry[] = $key;
                    }
                } catch (Mage_Core_Exception $e) {
                    $result = $e->getMessage();
                    Mage::throwException($result);
                }
            }
            $messageInfo .= $insertProduct . $updatedProducts . $titleMissing . $missingNoPrice . $missingDescription . $missingImages . $missingSubCategory . $missingBrand . "\n[END]";
            $this->createFile('import.txt', $messageInfo);
            $this->_log = "\n Import Report " . date("d-m-Y H:i:s") . $insertProduct . $updatedProducts . $titleMissing . $missingNoPrice . $missingDescription . $missingImages . $missingSubCategory . $missingBrand . "\n[END]";
        }
    }

    public function isUpdatedQty($product)
    {
        $typeId = $product->getTypeId();
        $compositType = array('grouped', 'bundle', 'configurable');
        if (in_array($typeId, $compositType)) {
            return false;
        }

        return true;
    }


    public function createFile($filename, $messageInfo)
    {
        try {
            $io = new Varien_Io_File();
            $path = Mage::getBaseDir();
            $file = $path . DS . $filename;
            $io->setAllowCreateFolders(true);
            $io->open(array('path' => $path));
            $io->streamOpen($file, 'w+');
            $io->streamLock(true);
            $io->streamWrite($messageInfo);
            $io->streamClose();
            $io->close($file);
        } catch (Exception $exception) {
            throw $exception;
        }
        return $file;
    }

    //Send mail attach file
    public function sendMailAttach($mail, $subject, $message, $fileAttach)
    {

        $path = Mage::getBaseDir();
        $file = $path . DS . $fileAttach;
        $mails = explode(',', $mail);
        foreach ($mails as $mail) {
            $email = new Zend_Mail();
            $email->setBodyHtml($message);
            $email->setFrom('enquiries@wingyipstore.co.uk', 'WingYipStore');
            $email->addTo($mail, $mail);
            $email->setSubject($subject);

            $content = file_get_contents($file); // e.g. ("attachment/abc.pdf")
            $attachment = new Zend_Mime_Part($content);
            $attachment->type = 'application/txt';
            $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
            $attachment->encoding = Zend_Mime::ENCODING_BASE64;
            $attachment->filename = $fileAttach; // name of file

            $email->addAttachment($attachment);

            $email->send();
        }
    }
    /////////////////////////   Create or Update Group Product      ////////////////////////////////////////////////////////////////////////////
    /////////////////////////   Get Category ids from Category Names of CSV      ///////////////////////////////////////////////////////////////
    public function getCategoryIds($curCat, $curSubCat)
    {
        $curCatAry = array();
        $clearedCurCat = $this->clearPrdctCategoryName($curCat);
        $clearedSubCat = $this->clearPrdctCategoryName($curSubCat);

        if ($clearedCurCat) {
            $curCatAry[] = $this->categoryAndIdMapping[$clearedCurCat];
        }
        if ($clearedSubCat) {
            $curCatAry[] = $this->categoryAndIdMapping[$clearedSubCat];
        }
        return $curCatAry;
    }

    /////////////////////////   Get Category Id from Category Name      ////////////////////////////////////////////////////////////////////////
    public function getPrdctCategoryId($myCatName, $myParCatName = false)
    {
        $clearCatName = $this->clearPrdctCategoryName($myCatName);
        $myCatObj = Mage::getResourceModel('catalog/category_collection')->addFieldToFilter('url_key', $clearCatName)->getFirstItem();
        if ($myCatObj->getEntityId()) {
            return $myCatObj->getEntityId();
        } else {
            return $this->createNewCategory($myCatName, $clearCatName, $myParCatName);
        }
    }

    /////////////////////////   Remove WhiteSpace from Category Names and make it Lowercase      ///////////////////////////////////////////////
    public function clearPrdctCategoryName($myCatName2)
    {
        $clearedCatName = str_replace(' ', '', $myCatName2);
        $clearedCatName2 = strtolower($clearedCatName);
        return $clearedCatName2;
    }

    /////////////////////////   Create Category if it doesnt Existe Yet      ///////////////////////////////////////////////////////////////////
    public function createNewCategory($cName, $cUrlKey, $cParCatName = false)
    {
        if ($cParCatName) {
            $addedCatPath = $this->categoryAndIdMapping[$this->clearPrdctCategoryName($cParCatName)];
            $catPath = $this->rootCategoryPath . '/' . $addedCatPath;
        } else {
            $catPath = $this->rootCategoryPath;
        }
        $category = new Mage_Catalog_Model_Category();
        $category->setName($cName);
        $category->setIsActive(1);
        $category->setUrlKey($cUrlKey);
        $category->setDescription($cName);
        $category->setDisplayMode('PRODUCTS');
        $category->setIsAnchor(1);
        $category->setAttributeSetId($category->getDefaultAttributeSetId());
        $category->setPath($catPath);
        $category->setIncludeInMenu("0");
        try {
            $category->save();
        } catch (Mage_Core_Exception $e) {
            $result = $e->getMessage();
            Mage::throwException($result);
        }
        return $category->getId();
    }

    /////////////////////////   Delete Products which are in Site but not in CSV rightnow      /////////////////////////////////////////////////
    public function hidePrdctsWhichNotInCsv($hideProudct)
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        foreach ($hideProudct as $product) {
            $delPrdctId = Mage::getModel("catalog/product")->getIdBySku($product);
            if($delPrdctId){
                try{
                    /* update the query Start */
                    // disable the product
                    $entity_type = 'catalog_product';
                    $attributeCode = 'status';
                    $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode($entity_type, $attributeCode);
                    $tableNameCatalogInt = $resource->getTableName('catalog_product_entity_int');
                    $queryStatus = 'SELECT * FROM ' . $tableNameCatalogInt . ' where entity_id=' . $delPrdctId . ' and attribute_id=' . $attributeModel->getId();
                    $resultstatus = $readConnection->fetchOne($queryStatus);
                    if ($resultstatus) {
                        $updateDisableQuery = "UPDATE {$tableNameCatalogInt} SET value  = '2' WHERE entity_id=" . $delPrdctId . " and attribute_id=" . $attributeModel->getId();
                        $writeConnection->query($updateDisableQuery);
                    }
                    $tableName = $resource->getTableName('cataloginventory_stock_item');
                    $tableNameStatus = $resource->getTableName('cataloginventory_stock_status');

                    $query = 'SELECT * FROM ' . $tableName . ' where product_id=' . $delPrdctId;
                    $results = $readConnection->fetchOne($query);
                    if ($results) {

                        $updateQuery = "UPDATE {$tableName} SET is_in_stock  = '0' WHERE product_id=" . $delPrdctId;
                        $writeConnection->query($updateQuery);

                        $updateStatusQuery = "UPDATE {$tableNameStatus} SET stock_status  = '0' WHERE product_id=" . $delPrdctId;
                        $writeConnection->query($updateStatusQuery);
                    }
                    /* update the query End */
                }catch(Exception $e){
                    Mage::log("\n".$delPrdctId.'----'.$e->getMessage(), null, 'logimporterror.log');

                }

            }
        }
        unset($resource);
    }

    //  Reset All Private Variables
    public function resetAllVariables()
    {
        $this->newSimpleProductAry = array();
        $this->updatedSimpleProductAry = array();
        $this->newGroupedProductAry = array();
        $this->updatedGroupedProductAry = array();
        $this->updatedGroupedAttributesAry = array();
        $this->updatedAssociatedSimpleAry = array();
        $this->prdctCategoryData = array();
        $this->prdctSubCategoryData = array();
        $this->categoryAndIdMapping = array();
        $this->csvGrpToSimDataAry = array();
        $this->dbGrpToSimDataAry = array();
        $this->csvSimPrdctDataAry = array();
        $this->productIdToSkuAry = array();
        $this->simProductsNeedToAdd = array();
        $this->grpProductsNeedToAdd = array();
        $this->simProductsNeedToDelete = array();
        $this->grpProductsNeedToDelete = array();
    }
}