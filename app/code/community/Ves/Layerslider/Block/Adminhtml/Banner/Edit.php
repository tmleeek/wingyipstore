<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Ves * @package     Ves_Layerslider
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Banner edit block
 *
 * @category   Ves
 * @package     Ves_Layerslider
 * @author    
 */

class Ves_Layerslider_Block_Adminhtml_Banner_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    var $data = null;
    var $params = array();
    public function __construct()
    {
        parent::__construct();

        $this->_objectId    = 'id';
        $this->_blockGroup  = 'ves_layerslider';
        $this->_controller  = 'adminhtml_banner';

        $this->_updateButton('save', 'label', Mage::helper('ves_tempcp')->__('Save Theme'));
        $this->_updateButton('delete', 'label', Mage::helper('ves_tempcp')->__('Delete Theme'));

        $this->setTemplate('ves_layerslider/form/edit.phtml');
        
        $mediaHelper = Mage::helper('ves_layerslider/media');
        $mediaHelper->loadMedia();
        $this->data = Mage::registry("banner_data");
        
    }
    protected function _prepareLayout() {
         /**
         * Display store switcher if system has more one store
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->setChild('store_switcher',
                   $this->getLayout()->createBlock('adminhtml/store_switcher')
                   ->setUseConfirm(false)
                   ->setSwitchUrl($this->getUrl('*/*/*/id/'.Mage::registry('banner_data')->get('banner_id'), array('store'=>null)))
           );
        }

        return parent::_prepareLayout();
    }

    public function getPreviewUrl($id = ""){
        $field = !empty($id)?"/id/".$id:"";
        return $this->getUrl('*/adminhtml_banner/preview'.$field);
    }

    public function getBannerData(){
        return $this->data;
    }
    public function getBannerParams(){
        $this->params = $this->data->getData("params");

        if($this->params) {
            $this->params = unserialize(base64_decode($this->params));
        }

        return $this->params;
    }

    public function getParamsJSON() {
        $slider_data = $this->getBannerParams();
        if($slider_data) {
            $image_background = isset($slider_data['bg'])?$slider_data['bg']:array();
            $image = "";
            if($image_background) {
                $image_src = $image_background['src'];
                $slider_data['bg']['src64'] = Mage::helper("ves_layerslider/uploadHandler")->getImage($image_src);
            }

            foreach($slider_data as $key => $slider) {
                if(strpos("slide-container-") !== false) {
                    
                    foreach($slider as $k => $v) {

                        if(isset($v['itemData']) && is_array($v['itemData'])) {
                            $image_src = $v['itemData']['src'];
                            $image_code = $v['itemData']['src64'];
    
                            $image_code = Mage::helper("ves_layerslider/uploadHandler")->getImage($image_src);
                            $slider_data[$key][$k]['itemData']['src64'] = $image_code;
                            if(isset($v['itemData']['videosrc']) && $v['itemData']['videosrc']) {
                                $base64img = str_replace('data:image/jpeg;base64,', '', $v['itemData']['videosrc']);
                                $videosrc = base64_decode($base64img);
                                $slider_data[$key][$k]['itemData']['videosrc'] = "";
                            }
                        }
                    }
                }
            }

            return Mage::helper('core')->jsonEncode($slider_data);
        }
           
        return "";
    }
    
    public function getHeaderText()
    {
        $banner_id = Mage::registry('banner_data')->getData('banner_id');

        if ($banner_id) {
            return Mage::helper('ves_tempcp')->__("Venus Layer Slider - Edit Banner '%s'", $this->htmlEscape(Mage::registry('banner_data')->getData('title')));
        } else {
            return Mage::helper('ves_tempcp')->__("Venus Layer Slider - New Banner");
        }
        
    }

    public function getCancelLink(){
        return $this->getUrl('*/adminhtml_banner/index');
    }
   
    public function getStoreSwitcherHtml() {
       return $this->getChildHtml('store_switcher');
    }
}
