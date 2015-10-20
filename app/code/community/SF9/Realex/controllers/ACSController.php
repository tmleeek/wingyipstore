<?php

class SF9_Realex_ACSController extends Mage_Core_Controller_Front_Action{

	public function indexAction(){
        $this->loadLayout();
        $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('realex/aCS_redirect'));
        $this->renderLayout();
	}

	public function verifysigAction(){
		Mage::log($_POST);


		try{
			$session = Mage::getSingleton('checkout/session');

			$pares = $_POST['PaRes'];
			$md = $_POST['MD'];

			$url = "https://epage.payandshop.com/epage-3dsecure.cgi";

			$remote = Mage::getModel('realex/remote');
			$request = $remote->build3DSVerifySigRequest($pares, $md);
			$response = $remote->postVerifySigRequest($request, $url);

			$response_array = $remote->process3DSVerifySigReponse($response, $md);


			if($response_array){
				foreach($response_array as $item){
					Mage::log($item);
				}

				if($response_array['result'] == '110'){
					$session->setErrorMessage('The Authentication Failed and there is no liability shift.  This is being treated as a fraudulent transaction.');
					$this->_redirect('realex/response/failure');
				}
			}else{
				if($quotationId=$session->getQuotationId()){
					$quotation = Mage::getModel('Quotation/Quotation')->load($quotationId);
					$quotation->setStatus('expired');
					$quotation->save();
				}
				$this->_redirect('checkout/onepage/success');
			}
		}catch (Exception $e) {
			$session->setErrorMessage('<p>' . $e->getMessage(). '</p>');
			$this->_redirect('realex/remote/failure');
		}
	}

}