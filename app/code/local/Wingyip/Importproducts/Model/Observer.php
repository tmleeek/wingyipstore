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
 * @package     Mage_ProductAlert
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ProductAlert observer
 *
 * @category   Mage
 * @package    Mage_ProductAlert
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Wingyip_Importproducts_Model_Observer
{
    const XML_PATH_EMAIL_ADMIN = 'trans_email/ident_general/email';
    const XML_PATH_EMAIL_ADMINLIST = 'importproducts/general/adminemail';
    const XML_PATH_EMAIL_ERROR_TEMPLATE = 'importproducts_error_email_template';

    protected function _processImport()
    {

        $getCountCollection = Mage::getModel('importproducts/importproducts')->getRunningImportProduct();
        if ($getCountCollection == 0) {
            Mage::getModel('importproducts/importproducts')->importProducts();
        }
    }

    /**
     * Send email to administrator if error
     *
     * @return Mage_ProductAlert_Model_Observer
     */
    protected function _sendErrorEmail($error)
    {
        $postObject = new Varien_Object();
        $Adminemail = explode(",", Mage::getStoreConfig(self::XML_PATH_EMAIL_ADMINLIST));

        if (count($Adminemail) > 0) {

            foreach ($Adminemail as $recEmail) {
                $maildata['error'] = Mage::helper('importproducts')->__("Couldn't connect to ftp server") . ":" . $error;

                $postObject->setData($maildata);

                $mailTemplate = Mage::getModel('core/email_template')->setDesignConfig(array('area' => 'backend'));
                $email = $recEmail;;//Mage::getStoreConfig("importproducts/general/adminemail");

                $sender = array('name' => 'Admin', 'email' => Mage::getStoreConfig(self::XML_PATH_EMAIL_ADMIN));
                $mailTemplate->sendTransactional(
                    self::XML_PATH_EMAIL_ERROR_TEMPLATE,
                    $sender,
                    $email,
                    "Store Admin",
                    array('data' => $postObject)
                );


                if (!$mailTemplate->getSentSuccess()) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('importproducts')->__("mail Sending Fail"));
                    //throw new Exception('mail Sending Fail');
                }
                $mailTemplate->setTranslateInline(true);
            }
        }
        return $this;
    }

    protected function _downloadFile()
    {
        $error = false;
        $ftpServer = Mage::getStoreConfig('importproducts/importproducts/host');
        $ftpLogin = Mage::getStoreConfig('importproducts/importproducts/user_name');
        $ftpPwd = Mage::helper('core')->decrypt(Mage::getStoreConfig('importproducts/importproducts/password'));

        $path = Mage::getBaseDir('var') . DS . 'import';// . DS;

        try {

            $connId = ftp_connect($ftpServer);

            try {
                $loginResult = ftp_login($connId, $ftpLogin, $ftpPwd);
                // check connection
                if ((!$connId) || (!$loginResult)) {
                    Mage::throwException($this->__("FTP connection has failed !"));
                }

                $dirName = Mage::getStoreConfig('importproducts/importproducts/directoy_path');
                $dirNameTo = Mage::getStoreConfig('importproducts/importproducts/move_to');

                // try to change the directory to somedir
                if (ftp_chdir($connId, $dirName)) {
                    // get contents of the current directory
                    $contents = ftp_nlist($connId, ".");

                    //echo "<pre>"; print_r($contents);
                    foreach ($contents as $fileName) {
                        if ($fileName != "." && $fileName != "..") {
                            $file = $path . DS . $fileName;
                            $handle = fopen($file, 'w');

                            if (ftp_fget($connId, $handle, $fileName, FTP_ASCII, 0)) {
                                $title = "Import " . time();
                                Mage::getModel('importproducts/importproducts')
                                    ->setTitle($title)
                                    ->setFilename($fileName)
                                    ->setStatus(1)
                                    ->setStartTime(time())
                                    ->setEndTime(time())
                                    ->setCreatedTime(time())
                                    ->setUpdateTime(time())
                                    ->save();
                                ftp_put($connId, $dirNameTo . '/' . $fileName, $file, FTP_ASCII);
                                ftp_delete($connId, $fileName);
                            } else {
                                Mage::throwException(Mage::helper('importproducts')->__("There was a problem while downloading $fileName to $fileName\n"));

                            }


                            //$io->streamWrite($this->_headers);
                            //echo $cont;
                        }
                    }

                    //echo "Current directory is now: " . ftp_pwd($connId) . "\n";
                } else {
                    $error = true;
                    $this->_sendErrorEmail(Mage::helper('importproducts')->__("FTP connection has failed. Wrong Username and Password !"));

                }

                // close the connection
                ftp_close($connId);
            } catch (Exception $e) {
                // send mail to admin
                $error = true;
                $this->_sendErrorEmail($e->getMessage());

            }
        } catch (Exception $e) {
            // send mail to admin
            $error = true;
            $this->_sendErrorEmail($e->getMessage());
        }

        return $error;
    }

    /**
     * Run process send product alerts
     *
     * @return Mage_ProductAlert_Model_Observer
     */
    public function process()
    {
        $result = $this->_downloadFile();
        if (!$result) {
            $this->_processImport();
        }
        return $this;
    }

    public function directProcesss()
    {
        $getCountCollection = Mage::getModel('importproducts/importproducts')->getRunningImportProduct();
        if ($getCountCollection == 0) {
            mail("emailtestertwo@gmail.com", "cron test", "It's a test message from product import cron staging start" . now());
            $this->_processImport();
            mail("emailtestertwo@gmail.com", "cron test", "It's a test message from product import cron staging end" . now());
            return $this;
        }
    }
}
