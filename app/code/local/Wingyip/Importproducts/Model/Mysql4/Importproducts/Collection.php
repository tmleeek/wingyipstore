<?php



class Wingyip_Importproducts_Model_Mysql4_Importproducts_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract

{

    public function _construct()

    {

        parent::_construct();

        $this->_init('importproducts/importproducts');

    }

}