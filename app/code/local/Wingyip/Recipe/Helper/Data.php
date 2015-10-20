<?php     class Wingyip_Recipe_Helper_Data extends Mage_Core_Helper_Abstract{            public function getCourseData(){                /*$file = dirname(dirname(__FILE__)).DS.'data'.DS.'course.csv';                    $csv = new Varien_File_Csv();                $data = $csv->getData($file);                $courseData = array();                    for($i=1; $i<count($data); $i++){            $courseData[] = array_combine($data[0],$data[$i]);        }        return $courseData;*/        $courses = Mage::getModel('recipe/course')->getCollection();        $courseData = array();        foreach($courses as $_course){                         $courseData[] = array('value'=>$_course->getId(),'label'=>$_course->getName());        }                return $courseData;            }            public function getOccasionData(){                $file = dirname(dirname(__FILE__)).DS.'data'.DS.'occasion.csv';                $csv = new Varien_File_Csv();                $data = $csv->getData($file);                $occasionData = array();                    for($i=1; $i<count($data); $i++)                    {                            $occasionData[] = array_combine($data[0],$data[$i]);                    }                return $occasionData;                }                public function getRecipeImageUrl($recipe){        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$recipe->getImage();        }                public function resizeImage($width=NULL, $height=NULL, $imagePath=NULL)        {           if($imagePath != NULL){            $imagePath = str_replace("/", DS, $imagePath);                            $imageName = end(explode(DS,$imagePath));                            $imagePathFull = Mage::getBaseDir('media') . DS . $imagePath;                }                              if(!file_exists($imagePathFull) || $imagePath=NULL){                       return Mage::getBaseUrl("media") . 'recipe' . "/" .'no-image.jpg';                }                        if($width == NULL && $height == NULL) {                        $width = 100;                        $height = 100;               }        $resizePath = $width . 'x' . $height;                        $resizePathFull = Mage::getBaseDir('media') . DS . 'recipe' . DS . $resizePath . DS . $imageName;        if (!file_exists($resizePathFull)) {                        $imageObj = new Varien_Image($imagePathFull);            $imageObj->constrainOnly(true);            $imageObj->keepAspectRatio(true);            $imageObj->keepFrame(true);            $imageObj->backgroundColor(array(255, 255, 255));            $imageObj->keepTransparency(true);            $imageObj->resize($width,$height);            $imageObj->save($resizePathFull);        }                                         $imagePath=str_replace(DS, "/", $imagePath);                return Mage::getBaseUrl("media") . 'recipe' . "/" . $resizePath . "/" . $imageName;        }                    public function getRequestPath($module_name,$obj){                                             if($obj->getUrlKey()){                        $identifier = $obj->getUrlKey();                }else{                        $name = $obj->getName();                        $nameArray = explode(' ',$name);                        $identifier = implode('-',$nameArray);                }                $stripedName = preg_replace('/[^a-zA-Z0-9-]/', '', $identifier);                $unchecked_path = "{$stripedName}.html";                $request_path = $this->checkUrlRewrite($unchecked_path);                $request_path = strtolower($request_path);                        $urlArray = explode('.',$request_path);                $urlKey = $urlArray[0];                $pathData[] = $request_path;                $pathData[] = $urlKey;                return $pathData;        }                    public function checkUrlRewrite($req_path){            $ReqpathCollection = Mage::getModel('core/url_rewrite')->getCollection()                ->addFieldToSelect('request_path')                ->addFieldToFilter('request_path',$req_path)                ->getFirstItem();                $duplicateReqpath =  $ReqpathCollection->getRequestPath();        $t = NULL;                if($duplicateReqpath!=''){            $splitagain = explode(".",$duplicateReqpath);            $splitreqpath = explode("-",$splitagain[0]);            $s = end($splitreqpath);            $g = ltrim(strrchr($splitagain[0],"-"),"-");                 $k = substr($splitagain[0], 0, strrpos($splitagain[0], '-') );            if($g==''){                $t = $splitagain[0]."-1.".$splitagain[1];            }                        else            {                $j = $g+1;                $t = $k."-".$j.".".$splitagain[1];            }            return $this->checkUrlRewrite($t);        }                else{            $t = $req_path;                }                return $t;        }                    public function handleUrlRewrite($module_name,$obj,$request_path)        {           $routersConfigNode = Mage::getConfig()->getNode('frontend/routers/recipe/args')->frontName;                //$short_path = $module_name."/index/list/id/".$obj->getId();                if($module_name == "recipe"){            $id_path = $module_name."/".$obj->getId();                    $target_path = $routersConfigNode."/index/view/id/".$obj->getId();                   }        else{            $id_path = "recipe_".$module_name."/".$obj->getId();            $target_path = $routersConfigNode."/".$module_name."/view/{$module_name}_id/".$obj->getId();        }        /**                * First, check if there is already a main url rewrite object.                */         /* @var $mainUrlRewrite Mage_Core_Model_Url_Rewrite */                $mainUrlRewrite = Mage::getModel('core/url_rewrite')->loadByIdPath($id_path);                /**                * If there already is a main url rewrite object, check if there are                * redirects to this one.        */        if((!$mainUrlRewrite->isObjectNew()) && isset($target_path)){            $oldRequestPathExp = explode('.html',$mainUrlRewrite->getRequestPath());            $oldRequestPath = $oldRequestPathExp[0]."/";                        $newRequestPathExp = explode('.html',$request_path);                        $newRequestPath = $newRequestPathExp[0]."/";                          $urlRewriteCollection = Mage::getModel('core/url_rewrite')->getCollection()                           ->addFieldToFilter('target_path',array('like'=>'%'.$target_path.'%'))                             ->addFieldToFilter('url_rewrite_id', array('neq' => $mainUrlRewrite->getUrlRewriteId()))                ->load();                        /**                        * If there are objects found, those must be redirected to the new                        * request_path.            */                            foreach ($urlRewriteCollection as $urlRewrite){                                /**                                * Remove those object where the request path equals the current target                                * path. This can occur if the user changes the url key back to                                * an old one.                */                                if($urlRewrite->getRequestPath() == $request_path){                                          $urlRewrite->delete();                }else{                    $newChildPath = "";                                        $newChildPath = str_replace($oldRequestPath,$newRequestPath,$urlRewrite->getRequestPath());                                        $urlRewrite->setRequestPath($newChildPath)                                            ->setIsSystem(true)                                            ->save();                                }                        }                }                /**                * Populate mainUrlRewrite with all data and save it. This way, for new                * objects, an Url rewrite is created too.        */                                  $request_path = $routersConfigNode.'/'.$request_path;                $mainUrlRewrite->setIdPath($id_path)                ->setStoreId(1)                ->setRequestPath($request_path)                ->setTargetPath($target_path)                ->setIsSystem(true)                ->save();                            /**        * Check if a redirect must be made.        */                $identifier_create_redirect = $obj->getData('identifier_create_redirect');                if (!empty($identifier_create_redirect)){                        /**            * A permanent redirect to the new url must be made.            */                        $rewrite = Mage::getModel('core/url_rewrite');                        $rewrite->setIdPath($module_name."/{$obj->getId()}_{$identifier_create_redirect}")                            ->setRequestPath($module_name."/{$identifier_create_redirect}.html")                            ->setStoreId(Mage::app()->getStore()->getId())                            ->setTargetPath($request_path)                            ->setIsSystem(true)                            ->save();        }                    }     /**    * Delete all url rewrites for this object.    */        public function deleteUrlRewrites($id,$module_name){         if($module_name == "recipe"){            $id_path = $module_name."/".$id;                }        else{            $id_path = "recipe_".$module_name."/".$id;        }                $mainUrlRewrite = Mage::getModel('core/url_rewrite')->loadByIdPath($id_path);                /**        * If there is a main url rewrite object, check if there are redirects                * to this one which must be deleted.        */                if(!$mainUrlRewrite->isObjectNew()){            $urlRewriteCollection = Mage::getModel('core/url_rewrite')->getCollection()                            ->addFilter('target_path', $mainUrlRewrite->getRequestPath())                            ->addFieldToFilter('url_rewrite_id', array('neq' => $mainUrlRewrite->getUrlRewriteId()))                            ->load();                        /**            * If there are objects found, those must be deleted.            */                        foreach ($urlRewriteCollection as $urlRewrite){                $urlRewrite->delete();            }                }    $mainUrlRewrite->delete();    }                    public function getIngredients(){                $ingredients = Mage::getModel('recipe/ingredient')->getCollection()->addFieldToFilter('status',1);        $ingredients->setOrder('sort', 'ASC');        $ingredients->load();                $ingredientsCheckbox = array();        foreach($ingredients as $_ingredient){            $ingredientsCheckbox[] = array('value'=>$_ingredient->getId(),'label'=>$_ingredient->getName());        }                return $ingredientsCheckbox;    }                    public function getCupboardIngredients(){        $cupboard = Mage::getModel('recipe/cupboard')->getCollection()->addFieldToFilter('status',1);        $cupboard->setOrder('sort', 'ASC');        $cupboard->load();                $cupboardCheckbox = array();        foreach($cupboard as $_cupboard){                         $cupboardCheckbox[] = array('value'=>$_cupboard->getId(),'label'=>$_cupboard->getName());        }                return $cupboardCheckbox;    }                    public function getCuisineType(){        $cuisine = Mage::getModel('recipe/cuisine')->getCollection()->addFieldToFilter('status',1);        $cuisine->setOrder('sort', 'ASC');        $cuisine->load();                $cuisineCheckbox = array();                foreach($cuisine as $_cuisine){                         $cuisineCheckbox[] = array('value'=>$_cuisine->getId(),'label'=>$_cuisine->getName());        }                return $cuisineCheckbox;    }             public function getCookingMethod(){                $cookingmethods = Mage::getModel('recipe/cookingmethod')->getCollection()->addFieldToFilter('status',1);        $cookingmethods->setOrder('sort', 'ASC');        $cookingmethods->load();                $cookingmethodsCheckbox = array();                foreach($cookingmethods as $_cookingmethod){                         $cookingmethodsCheckbox[] = array('value'=>$_cookingmethod->getId(),'label'=>$_cookingmethod->getName());                }                return $cookingmethodsCheckbox;        }                    public function getCategories(){                $categories = Mage::getModel('recipe/category')->getCollection()->addFieldToFilter('status',1);        $categories->setOrder('sort', 'ASC');        $categories->load();                $categoriesCheckbox = array();                foreach($categories as $_category){                         $categoriesCheckbox[] = array('value'=>$_category->getId(),'label'=>$_category->getName());        }                return $categoriesCheckbox;        }                    public function getAllSpecialDietTags(){                        return Mage::getResourceModel('recipe/recipe')->getAllSpecialDietTags();    }        public function getRecipeStatusesOptionArray()    {        return array(1 => 'Active',2 => 'Inactive');    }	    public function getReviewStatusesOptionArray()    {        return array(1 => 'Approved',2 => 'Pending',3 => 'Not Approved');    }    	public function getEmailFriendUrl($recipe){		        return $this->_getUrl('recipe/sendfriend/recipesend', array(            'id' => $recipe->getId()        ));	}		/**     * Check is enabled Module     *     * @param int $store     * @return bool     */    public function isEnabled($store = null)    {        return true;    }    /**     * Check allow send email for guest     *     * @param int $store     * @return bool     */    public function isAllowForGuest($store = null)    {        return false;    }}