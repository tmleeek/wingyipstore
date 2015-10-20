<?php
class Wingyip_Recipe_Block_List extends Mage_Core_Block_Template{
    protected $_collection;
    
    protected function _prepareLayout()
    {                
        parent::_prepareLayout();
        
        $pager = $this->getLayout()->createBlock('page/html_pager','productlist.pager')->setTemplate("page/html/pager.phtml");
        $pager->setAvailableLimit(array(9=>9,15=>15,20=>20,'all'=>'all'));
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        
        return $this;
    }
    
    public function getOrderUrl($order, $direction)
    {
        if (is_null($order)) {
            $order = $this->getCurrentOrder() ? $this->getCurrentOrder() : $this->_availableOrder[0];
        }
        return $this->getPagerUrl(array(
            "order"=>$order,
            "dir"=>$direction,
        ));
    }
    
    public function getPagerUrl($params=array())
    {
        $urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }
    
    public function getAvailableOrders()
    {
        return array(
            'name' => $this->__('Name'),
            'sort' => $this->__('Sort')
        );
    }
    
    public function isOrderCurrent($order)
    {
        return ($order == $this->getCurrentOrder());
    }
    
    public function getCurrentOrder(){
        return $this->getRequest()->getParam('order');
    }
    
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    
    public function getRecipeUrl($recipe)
    {
        $id_path = "recipe/{$recipe->getId()}";
        $mainUrlRewrite = Mage::getModel('core/url_rewrite')->loadByIdPath($id_path);
        return Mage::getUrl().$mainUrlRewrite->getRequestPath(); 
    }
 
    protected function getCollection()
    {
        $data = $this->getRequest()->getParams(); 
        
        if (is_null($this->_collection)) {
            $this->_collection = Mage::getModel('recipe/recipe')->getCollection()->addFieldToFilter('main_table.status',1);
            
            if($data['category_id'] != 0){
               $this->_collection->addCategoryFilter(array($data['category_id']));
            }
            
            
            if(strlen($data['course_id']) > 0){
               $this->_collection->addFieldToFilter('course',array("eq"=>$data['course_id'])); 
            }
            
            if($data['order'] && isset($data['dir']) && $data['dir']) {
                $this->_collection->setOrder($data['order'],$data['dir']);
            }
            elseif(!$data['order'] && isset($data['dir']) && $data['dir']){
                $this->_collection->setOrder('main_table.name',$data['dir']);
            }
            else{
                $this->_collection->setOrder('main_table.name','ASC');
            }
            
            $this->_collection->getSelect()->columns(array("name"=>"main_table.name","description"=>"main_table.description","sort"=>"main_table.sort","url_key"=>"main_table.url_key"));
           // echo $this->_collection->getSelect(); exit;
            /*$this->_collection->load(); */  
        }
        return $this->_collection;
    }
	
	public function getRecipeImage($recipeId){
		$collection=Mage::getModel('recipe/image')->getCollection()
					->addFieldToFilter('recipe_id',$recipeId)
					->addFieldToFilter('is_default','1')
					->getFirstItem()
					->load();
					
		return  $collection->getImage();
	}
}
