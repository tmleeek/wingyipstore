<?php

class Wingyip_Importproducts_Block_Adminhtml_Importproducts_Renderer_Runcsv extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract

{

	public function render(Varien_Object $row)

	{

		//DebugBreak();

		$cronUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'cron.php';

		return '<a href="'.$cronUrl.'" style="color:#EA7601;" target="_blank">'.$cronUrl.'</a>';

	}

}

?>