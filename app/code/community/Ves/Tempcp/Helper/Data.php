<?php

class Ves_Tempcp_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getApplyPostUrl(){
        return $this->_getUrl('tempcp');
    }
    
    public function checkAvaiable($controller_name = "") {
        $arr_controller = array("Mage_Cms",
            "Mage_Catalog",
            "Mage_Tag",
            "Mage_Checkout",
            "Mage_Customer",
            "Mage_Wishlist",
            "Mage_CatalogSearch");
        if (!empty($controller_name)) {
            if (in_array($controller_name, $arr_controller)) {
                return true;
            }
        }
        return false;
    }

    public function checkMenuItem($menu_name = "", $config = array()) {
        if (!empty($menu_name) && !empty($config)) {
            $menus = isset($config["menuAssignment"]) ? $config["menuAssignment"] : "all";
            $menus = explode(",", $menus);
            if (in_array("all", $menus) || in_array($menu_name, $menus)) {
                return true;
            }
        }
        return false;
    }

    public function getListMenu() {
        $arrayParams = array(
            'all' => Mage::helper('adminhtml')->__("All"),
            'Mage_Cms_index' => Mage::helper('adminhtml')->__("Mage Cms Index"),
            'Mage_Cms_page' => Mage::helper('adminhtml')->__("Mage Cms Page"),
            'Mage_Catalog_category' => Mage::helper('adminhtml')->__("Mage Catalog Category"),
            'Mage_Catalog_product' => Mage::helper('adminhtml')->__("Mage Catalog Product"),
            'Mage_Customer_account' => Mage::helper('adminhtml')->__("Mage Customer Account"),
            'Mage_Wishlist_index' => Mage::helper('adminhtml')->__("Mage Wishlist Index"),
            'Mage_Customer_address' => Mage::helper('adminhtml')->__("Mage Customer Address"),
            'Mage_Checkout_cart' => Mage::helper('adminhtml')->__("Mage Checkout Cart"),
            'Mage_Checkout_onepage' => Mage::helper('adminhtml')->__("Mage Checkout"),
            'Mage_CatalogSearch_result' => Mage::helper('adminhtml')->__("Mage Catalog Search"),
            'Mage_Tag_product' => Mage::helper('adminhtml')->__("Mage Tag Product")
        );
        return $arrayParams;
    }

    public function get($attributes = NULL) {
        $data = array();
        $arrayParams = array(
		'font_setting' =>	array(
			'enable_customfont',
            'fonturl1',
			'fontfamily1',
			'fontselectors1',
			'fonturl2',
			'fontfamily2',
			'fontselectors2',
				'fonturl3',
			'fontfamily3',
			'fontselectors3',
			
			'fonturl4',
			'fontfamily4',
			'fontselectors4'
		), 
		'ves_tempcp' => array(
            'show',
			'templatewidth',
            'skin',
            'layout',
            'paneltool',
            'responsive',
			'enablejquery',
			'panelposition',
            'backgroundpattern'
			
		)	
        );

        foreach ($arrayParams as $tag => $vars) {
          
            foreach ($vars as $var ) {
               // if (Mage::getStoreConfig("ves_tempcp_info/$tag/$var") != "") {
                    $data[$var] = Mage::getStoreConfig("ves_tempcp/$tag/$var"); //item config
            //    }
            }
            if (isset($attributes[$var])) {
               $data[$var] = $attributes[$var];
            }
        }
 
        return $data;
    }

    public function getImageUrl($url = null) {
        return Mage::getSingleton('ves_tempcp/config')->getBaseMediaUrl() . $url;
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @param  boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @return string
     */
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array()) {
        $json = Zend_Json::encode($valueToEncode, $cycleCheck, $options);
        /* @var $inline Mage_Core_Model_Translate_Inline */
        $inline = Mage::getSingleton('core/translate_inline');
        if ($inline->isAllowed()) {
            $inline->setIsJson(true);
            $inline->processResponseBody($json);
            $inline->setIsJson(false);
        }

        return $json;
    }
    
    public function getThemeInfo($is = 0) {
        
		if($is){
			$path = str_replace('adminhtml'.DS,'',Mage::getSingleton('core/design_package')->getSkinBaseDir(array('_package' => 'frontend\default')));
			//var_dump($path); echo "<br/>";
			$path = str_replace( "\\", DS, $path );
		}else{
			$path = Mage::getSingleton('core/design_package')->getSkinBaseDir();
			//var_dump($path); echo "<br/>";
		}
		
		//die;
		
        $info = null;
        if(is_file($path . '/config.xml')){
            $info = simplexml_load_file($path . '/config.xml');
        }

        if (!$info || !isset($info->name) || !isset($info->positions)) {
            return null;
        }
        $p = (array) $info->positions;
        $output = array("skins" => "", "positions" => $p["position"], "name" => (string) $info->name);
        if (isset($info->skins)) {
            $tmp = (array) $info->skins;
            $output["skins"] = $tmp["skin"];
        }
        $output = $this->onGetInfo( $path, $output );
		
        return $output;
    }
    
    public function onGetInfo($fpath, $output = array()) {
        $output["patterns"] = array();
		
		$path = $fpath. '/venustheme/images/patterns';
		
        $regex = '/(\.gif)|(.jpg)|(.png)|(.bmp)$/i';
        if($dk = opendir($path)){
			$files = array();
			while (false !== ($filename = readdir($dk))) {
				if (preg_match($regex, $filename)) {
					$files[] = $filename;
				}
			}
			$output["patterns"] = $files;
		}
        return $output;
    }

    /**
     *
     */
    public function writeToCache( $folder, $file, $value, $e='css' ){
        $file = $folder  . preg_replace('/[^A-Z0-9\._-]/i', '', $file).'.'.$e ;
        if (file_exists($file)) {
            unlink($file);
        }

        $flocal = new Varien_Io_File();
        $flocal->write($file, $value);
        $flocal->close();
        @chmod($file, 0755);
    }

    public function getThemeCustomizePath($theme = "") {
        $tmp_theme = explode("/", $theme);
        if(count($tmp_theme) == 1) {
            $theme = "default/".$theme;
        }
        $customize_path = Mage::getBaseDir('skin')."/frontend/".$theme."/css/customize/";
        if(!file_exists($customize_path)) {
            $file = new Varien_Io_File();
            $file->mkdir($customize_path);
            $file->close();
        }
        return $customize_path;
    }

    public function getImportCMSPath($theme = ""){
        $tmp_theme = explode("/", $theme);
        if(count($tmp_theme) == 1) {
            $theme = "default".DS.$theme;
        } else{
            $theme = implode(DS, $tmp_theme);
        }
        $path = Mage::getBaseDir('skin') . DS.'frontend'.DS.$theme.DS.'import'.DS.'cms'.DS;

        if (is_dir_writeable($path) != true) {
            mkdir ($path, '0744', $recursive  = true );
        } // end

        return $path;
    }

    public function getImportPath($theme = ""){
        $tmp_theme = explode("/", $theme);
        if(count($tmp_theme) == 1) {
            $theme = "default".DS.$theme;
        } else{
            $theme = implode(DS, $tmp_theme);
        }
        $path = Mage::getBaseDir('skin') . DS.'frontend'.DS.$theme.DS.'import'.DS;

        if (is_dir_writeable($path) != true) {
            mkdir ($path, '0744', $recursive  = true );
        } // end

        return $path;
    }

    public function getImportPath2() {
        $path = Mage::getBaseDir('var') . DS . 'import' . DS . 'ves_tempcp' . DS;

        if (is_dir_writeable($path) != true) {
            mkdir ($path, '0744', $recursive  = true );
        } // end

        return $path;
    }
    //read href attr in a tag
    public function readHref( $mypath = "" ) {
        $pattern = '/\/[^\/]{0,}$/i';
        if($mypath[strlen($mypath)-1]=="/") {
            $mypath = substr($mypath,0, (strlen($mypath)-1));
            preg_match($pattern, $mypath, $matches);
            return (isset($matches[0])?$matches[0]:"")."/";
        }
        preg_match($pattern, $mypath, $matches);
        return isset($matches[0])?$matches[0]:"/";
    }
    /**
     *
     */
    public function getMinicartURL(){
        return Mage::getUrl('vestempcp/index/minicart');
    }
    /**
     *
     */
    public function getQuickviewURL( $product_url = "") {
        $prodHref = "";
        $prodHref = $this->readHref($product_url);
        $prodHref = $prodHref == "/" ? substr($prodHref, 1, strlen($prodHref)) : $prodHref;
        $prodHref = trim($prodHref);

        return Mage::getUrl('vestempcp/quickview/view').'path'.$prodHref;
    }

    public function checkProductIsNew( $_product = null ) {
        $from_date = $_product->getNewsFromDate();
        $to_date = $_product->getNewsToDate();
        $is_new = false;
        $is_new = $this->isNewProduct( $from_date, $to_date);
        $today = strtotime("now");

        if($from_date && $to_date) {
            $from_date = strtotime($from_date);
            $to_date = strtotime($to_date);
            if($from_date <= $today && $to_date >= $today) {
                $is_new =true;
            }
        } elseif( $from_date && !$to_date) {
            $from_date = strtotime($from_date);
            if($from_date <= $today) {
                $is_new =true;
            }
        } elseif( !$from_date && $to_date) {
            $to_date = strtotime($to_date);
            if($to_date >= $today) {
                $is_new =true;
            }
        }
        /*
         else {
            $modified_date = $_product->getUpdatedAt();
            $created_date = $_product->getCreatedAt();
            
            if($modified_date) {
                $is_new = $this->isNewProduct( $modified_date, $number_day_new);
            } else {
                $is_new = $this->isNewProduct( $created_date, $number_day_new);
            } 
        }
        */
        return $is_new;
    }
    
    public function isNewProduct( $created_date, $num_days_new = 3) {
        $check = false;

        $startTimeStamp = strtotime($created_date);
        $endTimeStamp = strtotime("now");

        $timeDiff = abs($endTimeStamp - $startTimeStamp);

        $numberDays = $timeDiff/86400;  // 86400 seconds in one day

        // and you might want to convert to integer
        $numberDays = intval($numberDays);
        if($numberDays <= $num_days_new) {
            $check = true;
        }

        return $check;
    }

    public function subString( $text, $length = 100, $replacer ='...', $is_striped=true ){
            $text = ($is_striped==true)?strip_tags($text):$text;
            if(strlen($text) <= $length){
                return $text;
            }
            $text = substr($text,0,$length);
            $pos_space = strrpos($text,' ');
            return substr($text,0,$pos_space).$replacer;
    }
    public function getCartSubtotal() {
        $include_tax = Mage::getStoreConfig("tax/cart_display/subtotal");
        $subtotal = 0;
        
        if($include_tax !=1 ) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $items = $quote->getAllItems();
            foreach ($items as $item) {
                $subtotal += $item->getRowTotalInclTax();
            }
        } else {
            $subtotal = Mage::helper('checkout/cart')->getQuote()->getGrandTotal();
        }

        return Mage::helper('checkout')->formatPrice(  $subtotal );
    }

    public function getBought($product_sku = "") {
        $sku = nl2br($product_sku);
        $product = Mage::getResourceModel('reports/product_collection')
            ->addOrderedQty()
            ->addAttributeToFilter('sku', $sku)
            ->setOrder('ordered_qty', 'desc')
            ->getFirstItem();
        return (int)$product->getOrderedQty();
    }
}

?>
