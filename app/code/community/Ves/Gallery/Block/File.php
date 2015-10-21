<?php
class Ves_Gallery_Block_File extends Ves_Gallery_Block_List 
{
	function _toHtml() { 		 
		if( !$this->_show || !$this->getConfig('show') ) return;
		$theme = ($this->getConfig('theme')!="") ? $this->getConfig('theme') : "default";

		// check the source used ?
	 	$this->__renderSlideShowImagegroup();

		$this->_config['template'] = 'ves/gallery/file.phtml';
		$this->setTemplate($this->_config['template']);	
        return parent::_toHtml();
	}
	/**
	 * render block content for the slideshow using the list of products.
	 */
	private function __renderSlideShowImagegroup() 
	{
		$_model = Mage::getModel('ves_gallery/banner');
		$theme = ($this->getConfig('theme')!="") ? $this->getConfig('theme') : "default";
		$list = $_model->getCollection()
						->addFieldToFilter('label', $this->_config['imagecategory'])
						->addFieldToFilter('is_active', 1)
						->setOrder('position', 'asc');
						
		 
		$items = array();
		$i = 0;
		$maxTitle = $this->getConfig('titleMaxchar',15);
		$maxDesc = $this->getConfig('descMaxchar',200);
		$replacer = '...';		
		$isStriped = 1;
		$date = date("Y-m-d H:i:s");
		$today = strtotime($date);
		$mediaURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
		foreach($list as $item){			
			$tmp = new stdClass();
			$tmp->filename = $item->getTitle();
			$tmp->imageURL = $mediaURL. str_replace( DS, "/", $item->getFile() );
			$tmp->thumbnailURL = $mediaURL.$this->resizeImage( $item->getFile(), $this->getConfig("thumbWidth",200), $this->getConfig("thumbHeight",200) );
			$tmp->title = $item->getTitle();
			$tmp->description = $item->getDescription();
			$items[] = $tmp;
				
			$i++;
		}
		$this->assign( 'images', $items);

	}
	
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
}
