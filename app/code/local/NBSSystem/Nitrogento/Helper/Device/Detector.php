<?php

class NBSSystem_Nitrogento_Helper_Device_Detector extends Mobile_Detect implements NBSSystem_Nitrogento_Helper_Device_IDetector
{
	public function getDeviceKey()
	{
		$deviceKey = 0;
	
		if ($this->isTablet())
		{
			$deviceKey = 2;
		}
		elseif ($this->isMobile())
		{
			$deviceKey = 1;
		}
	
		return $deviceKey;
	}
}