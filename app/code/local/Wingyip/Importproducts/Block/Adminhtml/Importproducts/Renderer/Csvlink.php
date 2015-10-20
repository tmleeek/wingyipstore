<?php

class Wingyip_Importproducts_Block_Adminhtml_Importproducts_Renderer_Csvlink extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract

{

	public function render(Varien_Object $row)

	{

		$value2 = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'var/import/'.$row->getData($this->getColumn()->getIndex());

		$csv_file_name = explode("/", $value2);        



		return '<a href="'.$value2.'" style="color:#EA7601;">'.end($csv_file_name).'</a>';

	}

}

?>