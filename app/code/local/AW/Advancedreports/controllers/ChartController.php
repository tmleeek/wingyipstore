<?php

class AW_Advancedreports_ChartController extends Mage_Adminhtml_Controller_Action
{
    public function ajaxBlockAction()
    {
        $width = $this->getRequest()->getParam('width');
        $block = $this->getRequest()->getParam('block');
        $option = $this->getRequest()->getParam('option');
        $type = $this->getRequest()->getParam('type');
        $output = $this->getLayout()->createBlock('advancedreports/chart')
            ->setType($type)
            ->setOption($option)
            ->setWidth($width)
            ->setRouteOption($block)
            ->setHeight(Mage::helper('advancedreports')->getChartHeight())
            ->toHtml()
        ;
        $this->getResponse()->setBody($output);
        return;
    }

    public function tunnelAction()
    {
        if ($h = $this->getRequest()->getParam('h')) {
            $params = unserialize(urldecode(base64_decode($h)));
            $httpClient = new Zend_Http_Client(AW_Advancedreports_Block_Chart::API_URL);
            $response = $httpClient->setParameterGet($params)->request('GET');
            $headers = $response->getHeaders();
            $this->getResponse()
                ->setHeader('Content-type', $headers['Content-type'])
                ->setBody($response->getBody());
            return;
        }
    }
}