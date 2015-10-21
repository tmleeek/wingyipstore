<?php
 /*------------------------------------------------------------------------
  # VenusTheme Brand Module 
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Layerslider_Helper_Slider extends Mage_Core_Helper_Abstract {
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
	
	public function resizeImage( $image, $width, $height ){
		$image= str_replace("/",DS, $image);
		$_imageUrl = Mage::getBaseDir('media').DS.$image;
		$imageResized = Mage::getBaseDir('media').DS."resized".DS."{$width}x{$height}".DS.$image;

		if (!file_exists($imageResized)&&file_exists($_imageUrl)) {
			$imageObj = new Varien_Image($_imageUrl);
			$imageObj->quality(100);
			$imageObj->constrainOnly(TRUE);
			$imageObj->keepAspectRatio(TRUE);
			$imageObj->keepFrame(FALSE);
			$imageObj->resize( $width, $height);
			$imageObj->save($imageResized);
			
		}
		return 'resized/'."{$width}x{$height}/".str_replace(DS,"/",$image);
	}
	
	public function renderBannerElements( $banners = array() ) {
		$html = "";
		if($banners){
			foreach($banners as $key=>$banner) {
				$html .= $this->renderBannerElement( $banner );
			}
		}
		return $html;
	}
	public function renderBannerElement( $banner = array() ) {
		$item_data = isset($banner['itemData'])?$banner['itemData']:array();
		//Duration in/out
		$in = isset($item_data['in'])?$item_data['in']:array();
		$out = isset($item_data['out'])?$item_data['out']:array();

		//Basic config
		$top = isset($item_data['top'])?$item_data['top']:'0px';
		$left = isset($item_data['left'])?$item_data['left']:'0px';
		$clase = isset($item_data['clase'])?$item_data['clase']:"";
		$style = isset($item_data['style'])?$item_data['style']:"";
		$content = isset($item_data['content'])?$item_data['content']:"";
		$href = isset($item_data['href'])?$item_data['href']:"";
		$target = isset($item_data['target'])?$item_data['target']:"";
		$opacity = isset($item_data['opacity'])?$item_data['opacity']:"";
		$videosrc = isset($item_data['videosrc'])?$item_data['videosrc']:"";
		$videotype = isset($item_data['videotype'])?$item_data['videotype']:"youtube";
		$videoid = isset($item_data['videoid'])?$item_data['videoid']:"";

		$base_dir = Mage::helper("ves_layerslider")->getImageBaseDir();

		$html = "";
		if($item_data) {
			switch($item_data['type']) {

				case 'text': 

				 	$width = isset($item_data['width'])?$item_data['width']:"";
				 	$height = isset($item_data['height'])?$item_data['height']:"";

				 	$css = "";
				 	if(isset($item_data['width']) && $item_data['width']){
				 		$css .= " width:".$item_data['width'].";";
				 	}
				 	if(isset($item_data['height']) && $item_data['height']){
				 		$css .= " height:".$item_data['height'].";";
				 	}
				 	$tag = isset($item_data['tag'])?$item_data['tag']:"h3";
				 	$tag = 'div';

				 	$force = (isset($out['force']) && $out['force'])?'force':"";

				 	$html = '<'.$tag.' class="'.$clase.'"
			                  data-slide-in="at '.(isset($in['at'])?(int)$in['at']:5000).' from '.(isset($in['from'])?$in['from']:"left").' use '.(isset($in['use'])?$in['use']:"swing").' during '.(isset($in['during'])?$in['during']:"3000").'" 
			                  data-slide-out="at '.(isset($out['at'])?(int)$out['at']:8000).' to '.(isset($out['to'])?$out['to']:"right").' use '.(isset($out['use'])?$out['use']:"swing").' during '.(isset($out['during'])?$out['during']:"600").' '.$force.'" 
			                  style="top: '.$top.'; left: '.$left.';'.$css.$style.'">'.$content.'</'.$tag.'>';

				 
				break;
				case 'image':
					if($item_data['src'] && file_exists($base_dir.$item_data['src'])) {
					 	$width = isset($item_data['width'])?$item_data['width']:"";
					 	$height = isset($item_data['height'])?$item_data['height']:"";

					 	$ignore = (isset($item_data['ignore']) && $item_data['ignore'])?true:false;

					 	$css = "";
					 	if(isset($item_data['width']) && $item_data['width']){
					 		$css .= " width:".$item_data['width'].";";
					 	}
					 	if(isset($item_data['height']) && $item_data['height']){
					 		$css .= " height:".$item_data['height'].";";
					 	}

					 	if($ignore) {
					 		$html = '<img src="'.Mage::helper("ves_layerslider")->getImageBaseUrl().$item_data['src'].'" class="ignore" style="'.$css.'" alt="ignore"/>';
					 	} else {
					 		
						 	$force = (isset($out['force']) && $out['force'])?'force':"";

						 	$html = '<img src="'.Mage::helper("ves_layerslider")->getImageBaseUrl().$item_data['src'].'"
					                  data-slide-in="at '.(isset($in['at'])?(int)$in['at']:5000).' from '.(isset($in['from'])?$in['from']:"left").' use '.(isset($in['use'])?$in['use']:"swing").' during '.(isset($in['during'])?$in['during']:"3000").'" 
					                  data-slide-out="at '.(isset($out['at'])?(int)$out['at']:8000).' to '.(isset($out['to'])?$out['to']:"right").' use '.(isset($out['use'])?$out['use']:"swing").' during '.(isset($out['during'])?$out['during']:"600").' '.$force.'" 
					                  style="top: '.$top.'; left: '.$left.';'.$css.$style.'" alt="image" />';

					 	}
					 	
					}
				 
				break;
				case 'video':
					$width = isset($item_data['width'])?$item_data['width']:"";
				 	$height = isset($item_data['height'])?$item_data['height']:"";

				 	$css = "";
				 	if(isset($item_data['width']) && $item_data['width']){
				 		$css .= " width:".$item_data['width'].";";
				 	}
				 	if(isset($item_data['height']) && $item_data['height']){
				 		$css .= " height:".$item_data['height'].";";
				 	}
				 	$tag = isset($item_data['tag'])?$item_data['tag']:"h3";

				 	$force = (isset($out['force']) && $out['force'])?'force':"";


				 	$video_link = "";

				 	if($videotype == "youtube") {
				 		$video_link = "http://www.youtube.com/embed/".$videoid;
				 	} elseif($videotype == "vimeo") {
				 		$video_link = "http://www.youtube.com/embed/".$videoid;
				 	}

				 	$html = '<iframe src="'.$video_link.'" 
				 					class="'.$clase.'"
				 					width="'.$width.'" height="'.$height.'"
				 					frameborder="0" 
					                webkitallowfullscreen="webkitAllowFullScreen" 
					                mozallowfullscreen="mozallowfullscreen" 
					                allowfullscreen="allowFullScreen"
					                data-slide-in="at '.(isset($in['at'])?(int)$in['at']:5000).' from '.(isset($in['from'])?$in['from']:"left").' use '.(isset($in['use'])?$in['use']:"swing").' during '.(isset($in['during'])?$in['during']:"3000").'" 
					                data-slide-out="at '.(isset($out['at'])?(int)$out['at']:8000).' to '.(isset($out['to'])?$out['to']:"right").' use '.(isset($out['use'])?$out['use']:"swing").' during '.(isset($out['during'])?$out['during']:"600").' '.$force.'" 
			                  		style="top: '.$top.'; left: '.$left.';'.$css.$style.'"></iframe>';
				break;
				case 'imglink':
					if($item_data['src'] && file_exists($base_dir.$item_data['src'])) {
					 	$width = isset($item_data['width'])?$item_data['width']:"";
					 	$height = isset($item_data['height'])?$item_data['height']:"";

					 	$css = "";
					 	if(isset($item_data['width']) && $item_data['width']){
					 		$css .= " width:".$item_data['width'].";";
					 	}
					 	if(isset($item_data['height']) && $item_data['height']){
					 		$css .= " height:".$item_data['height'].";";
					 	}

					 	$force = (isset($out['force']) && $out['force'])?'force':"";

					 	$html = '<a href="'.$href.'" target="'.$target.'"
				                  data-slide-in="at '.(isset($in['at'])?(int)$in['at']:5000).' from '.(isset($in['from'])?$in['from']:"left").' use '.(isset($in['use'])?$in['use']:"swing").' during '.(isset($in['during'])?$in['during']:"3000").'" 
				                  data-slide-out="at '.(isset($out['at'])?(int)$out['at']:8000).' to '.(isset($out['to'])?$out['to']:"right").' use '.(isset($out['use'])?$out['use']:"swing").' during '.(isset($out['during'])?$out['during']:"600").' '.$force.'" 
				                  style="top: '.$top.'; left: '.$left.';'.$style.'"><img src="'.Mage::helper("ves_layerslider")->getImageBaseUrl().$item_data['src'].'" style="'.$css.'"/></a>';

					}
				break;
				case 'link':
					$width = isset($item_data['width'])?$item_data['width']:"";
				 	$height = isset($item_data['height'])?$item_data['height']:"";

				 	$css = "";
				 	if(isset($item_data['width']) && $item_data['width']){
				 		$css .= " width:".$item_data['width'].";";
				 	}
				 	if(isset($item_data['height']) && $item_data['height']){
				 		$css .= " height:".$item_data['height'].";";
				 	}

				 	$force = (isset($out['force']) && $out['force'])?'force':"";

				 	$html = '<a href="'.$href.'" target="'.$target.'" class="'.$clase.'"
			                  data-slide-in="at '.(isset($in['at'])?(int)$in['at']:5000).' from '.(isset($in['from'])?$in['from']:"left").' use '.(isset($in['use'])?$in['use']:"swing").' during '.(isset($in['during'])?$in['during']:"3000").'" 
			                  data-slide-out="at '.(isset($out['at'])?(int)$out['at']:8000).' to '.(isset($out['to'])?$out['to']:"right").' use '.(isset($out['use'])?$out['use']:"swing").' during '.(isset($out['during'])?$out['during']:"600").' '.$force.'" 
			                  style="top: '.$top.'; left: '.$left.';'.$css.$style.'">'.$content.'</a>';
				break;
				case 'block':

				 	$width = isset($item_data['width'])?$item_data['width']:"";
				 	$height = isset($item_data['height'])?$item_data['height']:"";

				 	$css = "";
				 	if(isset($item_data['width']) && $item_data['width']){
				 		$css .= " width:".$item_data['width'].";";
				 	}
				 	if(isset($item_data['height']) && $item_data['height']){
				 		$css .= " height:".$item_data['height'].";";
				 	}
				 	
				 	$css .= ' opacity:'.$opacity.';';

				 	$tag = isset($item_data['tag'])?$item_data['tag']:"h3";

				 	$force = (isset($out['force']) && $out['force'])?'force':"";

				 	$html = '<div class="'.$clase.'"
			                  data-slide-in="at '.(isset($in['at'])?(int)$in['at']:5000).' from '.(isset($in['from'])?$in['from']:"left").' use '.(isset($in['use'])?$in['use']:"swing").' during '.(isset($in['during'])?$in['during']:"3000").'" 
			                  data-slide-out="at '.(isset($out['at'])?(int)$out['at']:8000).' to '.(isset($out['to'])?$out['to']:"right").' use '.(isset($out['use'])?$out['use']:"swing").' during '.(isset($out['during'])?$out['during']:"600").' '.$force.'" 
			                  style="top: '.$top.'; left: '.$left.';'.$css.$style.'"></div>';

				break;
			}
		}
		return $html;
	}
}
