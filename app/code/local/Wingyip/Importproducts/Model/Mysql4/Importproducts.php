<?php



class Wingyip_Importproducts_Model_Mysql4_Importproducts extends Mage_Core_Model_Mysql4_Abstract

{

    public function _construct()

    {    

        // Note that the groupimport_id refers to the key field in your database table.

        $this->_init('importproducts/importproducts', 'importproducts_id');

    }

}