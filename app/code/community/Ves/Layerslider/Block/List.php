<?php
/*------------------------------------------------------------------------
 # VenusTheme Layer slider Module 
 # ------------------------------------------------------------------------
 # author:    VenusTheme.Com
 # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
 # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # Websites: http://www.venustheme.com
 # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Layerslider_Block_List extends Mage_Core_Block_Template 
{
	/**
	 * @var string $_config
	 * 
	 * @access protected
	 */
	protected $_config = '';
	
	/**
	 * @var string $_config
	 * 
	 * @access protected
	 */
	protected $_listDesc = array();
	
	/**
	 * @var string $_config
	 * 
	 * @access protected
	 */
	protected $_show = 0;
	protected $_theme = "";

	protected $_banner = null;
	
	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{	
		$this->_show = $this->getConfig("show");
 		
		if(!$this->_show) return;
		/*End init meida files*/
		$mediaHelper =  Mage::helper('ves_layerslider/media');

		$banner_id = $this->getConfig("bannerId");
		$banner_id = $banner_id?$banner_id:0;
		$this->_banner  = Mage::getModel('ves_layerslider/banner')->load( $banner_id );
		if($this->_banner->getData("is_flexslider") ) {
			$mediaHelper->addMediaFile("js_css", "ves_layerslider/lush/flexslider/flexslider.css" );
			$mediaHelper->addMediaFile( "js", "ves_layerslider/lush/flexslider/jquery.flexslider-min.js" );
		}
		parent::__construct();		
	}

	public function getSliderBanner() {
		return $this->_banner;
	}
	/**
     * Rendering block content
     *
     * @return string
     */
	function _toHtml() 
	{
		$this->_show = $this->getConfig("show");
 		$banner  = $this->getSliderBanner();
		if(!$this->_show || empty($banner)) return;

		$is_active =  $banner->getData("is_active");
		if($is_active) {
			$banners = array();
			$setting = array();
			$params = $banner->getData("params");
			$params = unserialize(base64_decode($params) );

			if($params) {
				foreach($params as $key => $slider) {
						if(strpos($key, "slide-container-") !== false && $slider) {
							if(isset($slider['type']) && $slider['type'] == 'image' && $slider['src']) {
								$slider['src'] = Mage::helper("ves_layerslider")->getImageBaseUrl().$slider['src'];
							}
							$banners[] = $slider;
							
						}
				}
				$setting['slider_width'] = isset($params['ss']['width'])?(int)$params['ss']['width']:1040;
				$setting['slider_height'] = isset($params['ss']['height'])?(int)$params['ss']['height']:450;
				$setting['full_width'] = isset($params['fw'])?$params['fw']:0;
				$setting['flexslider'] = isset($params['fs'])?$params['fs']:array();
				$setting['general'] = isset($params['bg'])?$params['bg']:array();

				if(isset($setting['general']['src']) && $setting['general']['src']) {
					$setting['general']['src'] = Mage::helper("ves_layerslider")->getImageBaseUrl().$setting['general']['src'];
				}
			}

			$this->assign("setting", $setting);
			$this->assign("params", $params);
			$this->assign("banners", $banners);

			if($banner->getData("is_flexslider") ) {
				$this->setTemplate("ves/layerslider/flexslider.phtml");
			} else {
				$this->setTemplate("ves/layerslider/default.phtml");
			}
			
			return parent::_toHtml();
		}
		return ;
    }

	public function getConfig( $val ){ 
		return Mage::getStoreConfig( "ves_layerslider/general_setting/".$val );
	}

	public function renderBannerElements( $banners = array() ) {
		$html = Mage::helper("ves_layerslider/slider")->renderBannerElements( $banners );
		return $html;
	}
}
