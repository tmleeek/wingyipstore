<?php

class Wingyip_Realex_Model_Setup extends Mage_Eav_Model_Entity_Setup
{

	public function createStaticBlocks(){
		$error = Mage::getModel('cms/block');
		$error->setTitle('Realex Error Message')
				->setIdentifier('realex_error')
				->setContent('{{var response.message}}')
				->setCreationTime(date('Y-m-d H:i:s'))
				->setUpdateTime(date('Y-m-d H:i:s'))
				->setIsActive(1)
				->setStores(0)
				->save();
						
		$success = Mage::getModel('cms/block');
		$success->setTitle('Realex Success Message')
				->setIdentifier('realex_success')
				->setContent('{{var response.message}}')
				->setCreationTime(date('Y-m-d H:i:s'))
				->setUpdateTime(date('Y-m-d H:i:s'))
				->setIsActive(1)
				->setStores(0)
				->save();
		
		$redirect = Mage::getModel('cms/block');
		$redirect->setTitle('Realex Redirect Message')
				->setIdentifier('realex_redirect')
				->setContent('You will be redirected to Realex in a few seconds.')
				->setCreationTime(date('Y-m-d H:i:s'))
				->setUpdateTime(date('Y-m-d H:i:s'))
				->setIsActive(1)
				->setStores(0)
				->save();
	}

}