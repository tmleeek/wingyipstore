<?php
class Wingyip_Exportorder_Model_Ftparray {
   public function toOptionArray()
    {
        return array(
            array('value' => 'FTP', 'label'=>Mage::helper('adminhtml')->__('FTP')),
            array('value' => 'SFTP', 'label'=>Mage::helper('adminhtml')->__('SFTP'))
        );
    }
}
