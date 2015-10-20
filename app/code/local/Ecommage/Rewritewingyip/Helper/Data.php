<?php
class Ecommage_Rewritewingyip_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @param $data
     */
    public function exportXml($data,$orderId)
    {
        try {
            $part = $this->getPart();
            $xmlPath = $part . 'ups_'.$orderId.'.xml';
            $writer = new XMLWriter();

            $writer->openMemory();
            $writer->startDocument('1.0', "WINDOWS-1252");
            $writer->startElement('OpenShipments');
            $writer->writeAttribute('xmlns','x-schema:OpenShipments.xdr');

            $writer->startElement('OpenShipment');
            $writer->writeAttribute('ProcessStatus','');
            $writer->writeAttribute('ShipmentOption','');
            $writer->startElement('ShipTo');
            $writer->startElement('CompanyOrName');
            $writer->text($data['company_or_name']);
            $writer->endElement();

            $writer->startElement('Attention');
            $writer->text($data['attention']);
            $writer->endElement();


            $writer->startElement('Telephone');
            $writer->text($data['telephone']);
            $writer->endElement();

            $writer->startElement('Address1');
            $writer->text($data['address1']);
            $writer->endElement();

            $writer->startElement('Address2');
            $writer->text($data['address2']);
            $writer->endElement();

            $writer->startElement('CountryTerritory');
            $writer->text($data['country_territory']);
            $writer->endElement();

            $writer->startElement('PostalCode');
            $writer->text($data['postal_code']);
            $writer->endElement();

            $writer->startElement('CityOrTown');
            $writer->text($data['city_or_town']);
            $writer->endElement();

            $writer->startElement('LocationID');
            $writer->text($data['location_id']);
            $writer->endElement();
            $writer->endElement();

            $writer->startElement('ShipmentInformation');
            $writer->startElement('ServiceType');
            $writer->text($data['service_type']);
            $writer->endElement();

            $writer->startElement('DescriptionOfGoods');
            $writer->text($data['description_of_goods']);
            $writer->endElement();

            $writer->startElement('BillTransportationTo');
            $writer->text($data['bill_transportation_to']);
            $writer->endElement();

            $writer->startElement('ProfileName');
            $writer->text($data['profile_name']);
            $writer->endElement();

            $writer->startElement('ShipperNumber');
            $writer->text($data['shipper_number']);
            $writer->endElement();
            $writer->endElement();

            $writer->startElement('Package');
            $writer->startElement('PackageType');
            $writer->text($data['package_type']);
            $writer->endElement();

            $writer->startElement('Weight');
            $writer->text($data['weight']);
            $writer->endElement();

            $writer->startElement('Reference1');
            $writer->text($data['reference1']);
            $writer->endElement();

            $writer->startElement('Reference2');
            $writer->text($data['reference2']);
            $writer->endElement();

            $writer->startElement('Reference3');
            $writer->text($data['reference3']);
            $writer->endElement();

            $writer->startElement('Reference4');
            $writer->text($data['reference4']);
            $writer->endElement();

            $writer->startElement('Reference5');
            $writer->text($data['reference5']);
            $writer->endElement();

            $writer->endElement();
            $writer->endElement();
            $writer->endElement();
            file_put_contents($xmlPath, $writer->outputMemory(true));
            Header('Content-type: text/xml');
            return true;
        }catch (Exception $e ){
            return $e;
        }
    }

    /**
     * @param $orderId
     * @return string
     * @throws Exception
     */
    public function getPart()
    {
        $csvPath = BP . DS . 'var' . DS . 'order' . DS;
        $io = new Varien_Io_File();
        $io->checkAndCreateFolder($csvPath);
        $io->open(array('path' => $csvPath));
        return $csvPath;
    }


    /**
     * @param $order_id
     */
    public function sendFileToFtp($order_id)
    {
         try {
             $userName = Mage::getStoreConfig('shipping/upsclassic/username');
             $passWord = Mage::getStoreConfig('shipping/upsclassic/password');
             $ftpUrl = Mage::getStoreConfig('shipping/upsclassic/ftp_url');
             $ftpPart = Mage::getStoreConfig('shipping/upsclassic/ftp_part');
             $fileName = 'ups_' . $order_id . '.xml';
             $filePart = $this->getPart() . $fileName;
             $ftpdirPart = $ftpPart;
             if (empty($userName)) {
                 Mage::throwException(Mage::helper('rewritewingyip')->__('Empty User Name Ftp'));
             }
             if (empty($passWord)) {
                 Mage::throwException(Mage::helper('rewritewingyip')->__('Empty PassWord Ftp'));
             }
             if (empty($ftpUrl)) {
                 Mage::throwException(Mage::helper('rewritewingyip')->__('Empty Url Ftp'));
             }
             if (empty($ftpPart)) {
                 Mage::throwException(Mage::helper('rewritewingyip')->__('Empty Dir Path Ftp'));
             }
             if (empty($filePart)) {
                 Mage::throwException(Mage::helper('rewritewingyip')->__('Empty File Path Xml'));
             }

             //Remote FTP server using Varien_Io_Ftp
             $ftp = new Varien_Io_Ftp();
             $io = new Varien_Io_File();
             //Open Connection FTP
             $ftp->open(
                 array(
                     'host' => trim($ftpUrl),
                     'user' => trim($userName),
                     'password' => trim($passWord),
                 )
             );
             if (!file_exists($ftpdirPart)) {
                 mkdir($ftpdirPart);
             }
             if ($flocal = fopen($filePart, 'r')) {
                 //Write file to server
                 if ($ftp->write($ftpPart . DS . $fileName, $flocal)) {
                     $message = $this->__('Update file via ftp Successfully');
                     Mage::getSingleton('core/session')->addSuccess($message);
                     return true;
                 } else {
                     $message = $this->__('FTP Failed to upload UPS XML File');
                     Mage::getSingleton('core/session')->addError($message);
                     return false;
                 }
             } else {
                 $message = $this->__('can not open xml file');
                 Mage::getSingleton('core/session')->addError($message);
                 return false;
             }
             // close connection
             $ftp->close();
         }catch( Exception $e){
             Mage::getSingleton('core/session')->addError($e);
         }
    }



    public function getbyOrderId($orderId){
        $model = Mage::getModel('rewritewingyip/ups')->getCollection();
        $model->addFieldToFilter('order_id', array('eq' => $orderId))->load();
        $data = $model->getData();
        return $data[0];
    }
}
	 