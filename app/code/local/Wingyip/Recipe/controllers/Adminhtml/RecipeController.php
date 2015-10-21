<?php
class Wingyip_Recipe_Adminhtml_RecipeController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('recipe/recipe')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Recipe Manager'), Mage::helper('adminhtml')->__('Recipe Manager'));
        return $this;
    }   
   
    public function indexAction() {
        $this->_initAction();     
        $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_recipe'));
        $this->renderLayout();
    }
 
    public function editAction()
    {
        $recipeId     = $this->getRequest()->getParam('id');
        $recipeModel  = Mage::getModel('recipe/recipe')->load($recipeId);
    
        if ($recipeModel->getId() || $recipeId == 0) {
            
            Mage::register('recipe_data', $recipeModel);
 
            $this->loadLayout();
            $this->_setActiveMenu('recipe/recipe');
           
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Recipe Manager'), Mage::helper('adminhtml')->__('Recipe Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Recipe Detail'), Mage::helper('adminhtml')->__('Recipe Detail'));
           
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
           
            $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_recipe_edit'))
                 ->_addLeft($this->getLayout()->createBlock('recipe/adminhtml_recipe_edit_tabs'));
               
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('Recipe does not exist'));
            $this->_redirect('*/*/');
        }
    }
   
    public function newAction()
    {
        $this->_forward('edit');
    }
   
    public function saveAction()
    {
        if ($data = $this->getRequest()->getParams()) {
            try {
                 
                /*if(!empty($data['categories'])){
                   $data['categories'] =  implode(',',$data['categories']);
                }
                
                if(!empty($data['ingredients'])){
                   $data['ingredients'] =  implode(',',$data['ingredients']);
                }
                
                if(!empty($data['cupboard_ingredients'])){
                   $data['cupboard_ingredients'] =  implode(',',$data['cupboard_ingredients']);
                }
                
                if(!empty($data['cuisine_type'])){
                   $data['cuisine_type'] =  implode(',',$data['cuisine_type']);
                }
                
                if(!empty($data['cooking_method'])){
                   $data['cooking_method'] =  implode(',',$data['cooking_method']);
                }*/
                
                if($data['special_dietary_tags']){
                    $data['special_dietary_tags'] = explode(',',trim($data['special_dietary_tags'],','));
                }
                
                if($code = $data['code']){
                    $uniqueCode = Mage::getResourceModel('recipe/recipe')->getUniqueCode($code);
                    if($uniqueCode != false){
                        if($uniqueCode['recipe_id'] != $this->getRequest()->getParam('id')){
                        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__($code.' Code already exist'));
                        if($catId){
                                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));    
                            }else{
                                $this->_redirect('*/*/new');   
                            }
                            return;
                       }
                    }
                }

				// For Upload Image Start
                $error=false;
                $imageCount = count($_FILES['image']['name']);    
                for($i=0;$i<$imageCount;$i++){
                    if (isset($_FILES['image']['name']['option_'.$i]) && $_FILES['image']['name']['option_'.$i] != ''){
                        $result = $this -> uploadImage($_FILES['image']['name']['option_'.$i],$i);
                        $imgData[] = $result;
						
						if(count($data['default'])){ 
							$defaultData=$data['default']; 
							foreach($defaultData as $def){ 
								$value=str_replace('option_','',$def);
								if($value==$i)
								{
									$defaultValue=$result;
								}
							}
						}
                        if(!isset($result) && empty($result))
                        {
                            $error = true;    
                        }

                    } else{
                        $error = true;
                    }
                }
				 
                if ($error)
                {  
                    throw new Exception($this->__('You must have to upload image.'));
                } 
                // For Upload Image End
                
                //if(@$data['image']['delete']!='1' &&  $_FILES['image']['name']=='')
//                {
//                    $data['image']= @$data['image']['value'];
//                }
//
//                if((@$data['image']['delete']=='1'))
//                {
//                    $removeFile = Mage::getBaseDir('media').DS.$data['image']['value'];
//                    @unlink($removeFile);
//                    $data['image']=""; 
//                }
//
//                if($_FILES['image']['name']!="" )
//                {    
//                    $removeFile = @Mage::getBaseDir('media').DS.$data['image']['value'];
//                    @unlink($removeFile);            
//                }
//
//                if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') 
//                {
//                        /* Starting upload */    
//                        $uploader = new Varien_File_Uploader('image');
//
//                        // Any extention would work
//                        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
//                        $uploader->setAllowRenameFiles(false);
//
//                        // Set the file upload mode 
//                        // false -> get the file directly in the specified folder
//                        // true -> get the file in the product like folders 
//                        // (file.jpg will go in something like /media/f/i/file.jpg)
//                        $uploader->setFilesDispersion(false);
//
//                        $ext = strtolower(str_replace('.', '', strrchr($_FILES['image']['name'], '.')));
//                        /* Each new uploaed file will renamed with current time*/
//                        $newFileTime = time();
//                        $newFileName = $newFileTime.'.'.$ext;
//                        // We set media as the upload dir
//                        $path = Mage::getBaseDir('media') . DS .'recipe' . DS ;
//                        $uploader->save($path,$newFileName);
//                        
//                        //this way the name is saved in DB
//                        $data['image'] = 'recipe/'.$newFileName;
//                }
                
                $id = $this->getRequest()->getParam('id');
                
                $model = Mage::getModel('recipe/recipe');

                $model->setData($data)
                ->setId($id);
               

                if ($id==0 || $id==''){
                    $model->setCreatedAt(now())
                    ->setUpdatedAt(now());
                } else {
                    
                    $model->setUpdatedAt(now());
                }
                // echo "<pre>"; print_r($data); exit;
                $model->save();
                $recipeId =  $model->getId();
				if(!empty($imgData)){
                        $this->addRecipieImage($recipeId,$imgData,$defaultValue);
                }
				
				if(empty($imgData) && $data['default'] ){
					$tableName=Mage::getSingleton('core/resource')->getTableName('recipe/image');
					
					$writeConnection =Mage::getSingleton('core/resource')->getConnection('core_write');
					$fields = array();
					$fields['is_default'] = '0';
					$where = $writeConnection->quoteInto('recipe_id =?', $recipeId);
					$writeConnection->update($tableName, $fields, $where);
					$writeConnection->commit();	
					
					 $imageModel = Mage::getModel('recipe/image');
		    		$imageModel->setRecipeId($recipeId)
						->setIsDefault(1)
						->setId($data['default'][0])
						->save();
				}
				
				
                if (isset($data['links']['associated'])) {  
                    $associatedData=Mage::helper('adminhtml/js')->decodeGridSerializedInput($data['links']['associated']);
                    Mage::getModel('recipe/associated')->addAssociated($associatedData,$model->getId());                                 
                }
                
                if (isset($data['links']['related'])) {  
                    $relatedData=Mage::helper('adminhtml/js')->decodeGridSerializedInput($data['links']['related']);
                    Mage::getModel('recipe/related')->addRelated($relatedData,$model->getId());                                 
                }
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Recipe was successfully saved'));
                //Mage::getSingleton('adminhtml/session')->setRecipeData(false);
 
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(
                        '*/*/edit',
                        array(
                            'id' => $model->getId(),
                            'store' => Mage::app()->getStore()->getStoreId()
                        )
                    );
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setRecipeData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }
	
	public function uploadImage($fileName,$key)
    {
        $adapter  = new Zend_File_Transfer();

        if ($adapter->isUploaded($fileName))
        { 
            $fileExt        = strtolower(substr(strrchr($fileName, ".") ,1));
            $fileNamewoe    = rtrim($fileName, $fileExt);             
            $fileName       = preg_replace('/\s+', '', $fileNamewoe) . time() . '.' . $fileExt;

            $uploader = new Varien_File_Uploader( array(
            'name' => $_FILES['image']['name']['option_'.$key],
            'type' => $_FILES['image']['type']['option_'.$key],
            'tmp_name' => $_FILES['image']['tmp_name']['option_'.$key],
            'error' => $_FILES['image']['error']['option_'.$key],
            'size' => $_FILES['image']['size']['option_'.$key]
            ));
            $uploader->setAllowedExtensions(array('jpg','jpeg','png','gif'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            //if ($uploader->chechAllowedExtension($uploader->getFileExtension()))
            //{
                $path = Mage::getBaseDir('media') . DS . 'recipe';
                if(!is_dir($path))
                {
                    mkdir($path, 0777, true);
                }
                //$uploader->save($path . DS, $fileName );
                if ($uploader->save($path . DS,$fileName)) 
                {
                    return $uploader->getUploadedFileName();
                }
            //}
        }
        return false;
    }
	
	public function addRecipieImage($recipeId,$imgData,$defaultValue){
        $tableName=Mage::getSingleton('core/resource')->getTableName('recipe/image');
					
		$writeConnection =Mage::getSingleton('core/resource')->getConnection('core_write');
		$fields = array();
		$fields['is_default'] = '0';
		$where = $writeConnection->quoteInto('recipe_id =?', $recipeId);
		$writeConnection->update($tableName, $fields, $where);
		$writeConnection->commit();	
					
		
		foreach($imgData as $imgName){
			if($imgName==$defaultValue){
				$default=1;
			}
			else{
				$default=0;
			}
            $imageModel = Mage::getModel('recipe/image');
            $imageModel->setRecipeId($recipeId)
            ->setImage("recipe/".$imgName)
			->setIsDefault($default)
            ->save(); 
        }
        return;
    }
   
    public function deleteAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $recipeModel = Mage::getModel('recipe/recipe');
               
                $recipeModel->setId($this->getRequest()->getParam('id'))
                    ->delete();
                   
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Recipe was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
    /**
     * Product grid for AJAX request.
     * Sort and filter result for example.
     */
    public function gridAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('recipe/adminhtml_recipe_grid'));
        $this->renderLayout();
    }
    
    public function massDeleteAction()
    {
        $recipeIds = $this->getRequest()->getParam('recipe_id');
        if(!is_array($recipeIds)) {
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('Please select Recipe(s).'));
        } else {
        try {
            $recipeModel = Mage::getModel('recipe/recipe');
            foreach ($recipeIds as $recipeId) {
            $recipeModel->load($recipeId)->delete();
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('recipe')->__(
            'Total of %d record(s) were deleted.', count($recipeIds)
            )
            );
            } 
            catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function massUpdateStatusAction()
    {
        $recipeIds = $this->getRequest()->getParam('recipe_id');
        $session    = Mage::getSingleton('adminhtml/session');

        if(!is_array($recipeIds)) {
             $session->addError(Mage::helper('adminhtml')->__('Please select recipe(s).'));
        } else {
            /* @var $session Mage_Adminhtml_Model_Session */
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($recipeIds as $_recipeId) {
                    $model = Mage::getModel('recipe/recipe')->load($_recipeId);
                    $model->setStatus($status)->save();
                        
                }
                $session->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) have been updated.', count($recipeIds))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while updating the selected recipe(s).'));
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }
    
    public function massUpdateRecipeUrlAction()
    {
        $recipeIds = $this->getRequest()->getParam('recipe_id');
        if(!is_array($recipeIds)) {
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('recipe')->__('Please select Recipe(s).'));
        } else {
        try {
            $module_name = "recipe";
            $recipeModel = Mage::getModel('recipe/recipe');
            foreach ($recipeIds as $recipeId) {
                Mage::helper('recipe')->deleteUrlRewrites($recipeId,$module_name);
            }
            foreach ($recipeIds as $recipeId) {
                $recipeModel->load($recipeId);
                $pathData = $pathData = Mage::helper('recipe')->getRequestPath($module_name,$recipeModel);
                Mage::helper('recipe')->handleUrlRewrite($module_name,$recipeModel,$pathData[0]); 
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('recipe')->__(
            'Total of %d record(s) url were udated.', count($recipeIds)
            )
            );
            } 
            catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function associatedAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('recipe.edit.tab.associated')
            ->setProductsAssociated($this->getRequest()->getPost('products_associated', null));
        $this->renderLayout();
    }
    
    public function associatedGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('recipe.edit.tab.associated')
            ->setProductsAssociated($this->getRequest()->getPost('products_associated', null));
        $this->renderLayout();
    }
    
   public function relatedAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('recipe.edit.tab.related')
           ->setRecipeRelated($this->getRequest()->getPost('recipe_related', null));
        $this->renderLayout();
    }
    
    public function relatedGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('recipe.edit.tab.related')
            ->setRecipeRelated($this->getRequest()->getPost('recipe_related', null));
        $this->renderLayout();
    }
    
    public function recipeAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.recipe')
            ->setRecipeAssociated($this->getRequest()->getPost('recipe_associated', null))
            ->setRecipeRelated($this->getRequest()->getPost('recipe_related', null));
        $this->renderLayout();
    }
    
    public function recipeGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.recipe')
            ->setRecipeAssociated($this->getRequest()->getPost('recipe_associated', null))
            ->setRecipeRelated($this->getRequest()->getPost('recipe_related', null));
        $this->renderLayout();
    }
	
	 public function imagedeleteAction()
    {
        $isAjax = Mage::app()->getRequest()->isAjax();
        if ($isAjax) {
          $imageIdArr = explode('_',$this->getRequest()->getPost('imageId'));
          $imageId = $imageIdArr[1];
          if( $imageId > 0 ) {
            try {
                    $recipeImageModel = Mage::getModel('recipe/image')->load($imageId);                    
                    $imageName = $recipeImageModel->getImage();

                    if($imageName !="" )
                    {    
                        $removeFile = @Mage::getBaseDir('media').DS.$imageName;
                        @unlink($removeFile);            
                    }
                    $recipeImageModel->setId($imageId)
                    ->delete();
                    
                    $jsonData = json_encode(array('message'=>'success'));

            } catch (Exception $e) {
                    $jsonData = json_encode(array('message'=>'error'));
            }
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody($jsonData);
          }
        }
    }
}