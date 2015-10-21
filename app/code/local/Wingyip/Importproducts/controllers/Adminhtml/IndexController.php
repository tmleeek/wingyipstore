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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Cms manage blocks controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
//global $_importNewData;
class Wingyip_Importproducts_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    const XML_PATH_EMAIL_ADMIN  		= 'trans_email/ident_general/email';
    const XML_PATH_EMAIL_ERROR_TEMPLATE   = 'importproducts_error_email_template';
    const XML_PATH_EMAIL_ADMINLIST =   "importproducts/general/adminemail";

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('system/importproducts')
            ->_addBreadcrumb(Mage::helper('importproducts')->__('Import Products'), Mage::helper('importproducts')->__('Import Products'));

        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('importproducts'))->_title($this->__('Manage Import Products'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('importproducts/adminhtml_importproducts'));
        $this->renderLayout();
    }
    public function importfileAction() {

        $serverTime=date("Y-m-d H:i:s");
        $scheduleTime = strtotime("+15 minutes", strtotime($serverTime));

        Mage::getModel('cron/schedule')
            ->setJobCode('importproduct_run_ftp')
            ->setStatus('pending')
            ->setCreatedAt(now())
            ->setScheduledAt($scheduleTime)
            ->save();

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('importproducts')->__("Successfully Added entry for cron and run the cron shortly"));

        $this->_redirect('*/*/index');
    }

    public function directFileImportAction() {

        $serverTime=date("Y-m-d H:i:s");
        $scheduleTime = strtotime("+15 minutes", strtotime($serverTime));

        Mage::getModel('cron/schedule')
            ->setJobCode('importproduct_run')
            ->setStatus('pending')
            ->setCreatedAt(now())
            ->setScheduledAt($scheduleTime)
            ->save();

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('importproducts')->__("Successfully Added entry for cron and run the cron shortly"));

        $this->_redirect('*/*/index');
    }

    public function sendMail($errrMsg)
    {
        $postObject = new Varien_Object();
        $Adminemail=explode(",",Mage::getStoreConfig(self::XML_PATH_EMAIL_ADMINLIST));

        if(count($Adminemail)>0){

            foreach($Adminemail as $recEmail){
                $maildata['error'] = $this->__("Couldn't connect to ftp server") .":".$errrMsg;

                $postObject->setData($maildata);

                $mailTemplate = Mage::getModel('core/email_template')->setDesignConfig(array('area'=>'backend'));
                $email= $recEmail;;//Mage::getStoreConfig("importproducts/general/adminemail");

                $sender = array('name' =>'Admin', 'email' => Mage::getStoreConfig(self::XML_PATH_EMAIL_ADMIN));
                $mailTemplate->sendTransactional(
                    self::XML_PATH_EMAIL_ERROR_TEMPLATE,
                    $sender,
                    $email,
                    "Store Admin",
                    array('data' =>$postObject)
                );


                if (!$mailTemplate->getSentSuccess()){
                    Mage::getSingleton('adminhtml/session')->addError($this->__("mail Sending Fail"));
                    //throw new Exception('mail Sending Fail');
                }

                $mailTemplate->setTranslateInline(true);
            }
        }


    }

    public function startimportAction()
    {
        Mage::getModel('importproducts/importproducts')->importProducts();
        //Mage::getModel('importproducts/observer')->process();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('importproducts')->__("Successfully created Product"));
        $this->_redirect('*/*/');


    }
    public function editAction() {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('importproducts/importproducts')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('importproducts_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('system/importproducts');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('importproducts/adminhtml_importproducts_edit'))
                ->_addLeft($this->getLayout()->createBlock('importproducts/adminhtml_importproducts_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('importproducts')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {
        //DebugBreak();
        if ($data = $this->getRequest()->getPost()) {

            if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('filename');
                    //$_helper = Mage::helper('csvupload');
                    //$ext = $_helper->getFileExtension($_FILES['filename']['name']);                  
                    $uploader->setAllowedExtensions(array('txt'));
                    $uploader->setAllowRenameFiles(false);
                    // Set the file upload mode 
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders 
                    // (file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(false);
                    // We set media as the upload dir

                    $path_parts = pathinfo($_FILES["filename"]["name"]);
                    $csv_path = $path_parts['filename'].'_'.time().'.'.$path_parts['extension'];

                    $path = Mage::getBaseDir('var') . DS ."import/";    // set path                        
                    $uploader->save($path, $csv_path);           // SAve file


                    $data['filename'] = $csv_path;
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    Mage::getSingleton('adminhtml/session')->setFormData($data);
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }

                //this way the name is saved in DB
                //$data['filename'] = 'groupimport/' . $_FILES['filename']['name'];
            }
            else {
                if(isset($data['filename']) && $data['filename']['delete'] == 1) {
                    $data['filename'] = "";
                }
                else {
                    unset($data['filename']);
                }
            }

            //  Create log file on base for title 
            /*  if(!$this->getRequest()->getParam('id')){
                  $log_file_name= str_replace(' ','', $data['title']);
                  $path_log = Mage::getBaseDir() . DS ."var" . DS ."log" . DS;
                  $myFile = $path_log . strtolower($log_file_name) . ".log";
                  $fo = fopen($myFile, 'w') or die("can't open file");
              }*/
            //  Create log file on base for title finish


            $model = Mage::getModel('importproducts/importproducts');
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                        ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('importproducts')->__('Importproducts file was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('importproducts')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('importproducts/importproducts');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Importproducts File was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
    public function massDeleteAction() {
        $groupimportIds = $this->getRequest()->getParam('importproducts');
        if(!is_array($groupimportIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('importproducts')->__('Please select item(s)'));
        } else {
            try {
                foreach ($groupimportIds as $groupimportId) {
                    $groupimport = Mage::getModel('importproducts/importproducts')->load($groupimportId);
                    $groupimport->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($groupimportIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

}
