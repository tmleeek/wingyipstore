<?php
class Wingyip_Importproducts_Block_Adminhtml_Importproducts_Renderer_Loglink extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		//DebugBreak();
		$log_file_name = str_replace(' ', '',$row->getData('title'));
		$logfile = Mage::getBaseDir() . DS .'var'. DS.'log'. DS .strtolower($log_file_name).'.log';        

		if(file_exists($logfile)){
			$value2 = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'var/log/'.strtolower($log_file_name).'.log'; 
			return '<a href="'.$value2.'" style="color:#EA7601;" target="_blank">'.strtolower($log_file_name).".log".'</a>';    
		}
		else {
			return "";
		}

	}
}
?>