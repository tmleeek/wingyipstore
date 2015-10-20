<?php
class Wingyip_Recipe_Block_Advancedsearch extends Wingyip_Recipe_Block_List{
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
            $this->_collection = Mage::getModel('recipe/recipe')->getCollection()
            ->addFieldToFilter('main_table.status',1);
            
            if(!empty($data['categories'])){
               $this->_collection->addCategoryFilter($data['categories']);
            }
            
            if(!empty($data['ingredients'])){
               $this->_collection->addIngredientFilter($data['ingredients']);
            }
            
            if(!empty($data['cupboard_ingredients'])){
               $this->_collection->addCupboardIngredientFilter($data['cupboard_ingredients']); 
            }
  
            $cookingTime = explode(';',$data['cooking_time']);
            if(!empty($cookingTime)){
               $this->_collection->addFieldToFilter('cooking_time',array('from' => $cookingTime[0],'to' => $cookingTime[1])); 
            }
            
            if(strlen($data['cooking_method']) > 0){
               $this->_collection->addCookingMethodFilter($data['cooking_method']); 
            }
            
            if(strlen($data['cuisine_type']) > 0){
               $this->_collection->addCuisineTypeFilter($data['cuisine_type']); 
            }
            
            if(strlen($data['special_diet_tag']) > 0){
               $this->_collection->addSpecialDietTagFilter($data['special_diet_tag']); 
            } 
            
            if(strlen($data['course']) > 0){
               $this->_collection->addFieldToFilter('course',array("eq"=>$data['course'])); 
            }
            
            if(strlen($data['occasion']) > 0){
               $this->_collection->addFieldToFilter('occasion',array("like"=>$data['occasion'])); 
            }
            
            if(strlen($data['serving_size']) > 0){
               $this->_collection->addFieldToFilter('serving_size',array("lteq"=>$data['serving_size'])); 
            }
            
            if(strlen($data['heat_spice_level']) > 0){
               $this->_collection->addFieldToFilter('heat_spice_level',array("lteq"=>$data['heat_spice_level'])); 
            } 
            
            if(strlen($data['no_of_ingredients']) > 0){
               $this->_collection->addFieldToFilter('no_of_ingredients',array("lteq"=>$data['no_of_ingredients'])); 
            }
            
            if($data['order'] && $data['dir']) {
                $this->_collection->setOrder($data['order'],$data['dir']);
            }
            elseif(!$data['order'] && $data['dir']){
                $this->_collection->setOrder('main_table.name',$data['dir']);
            }
            else{
                $this->_collection->setOrder('main_table.name','ASC');
            } 
            
            $this->_collection->getSelect()->columns(array("name"=>"main_table.name","description"=>"main_table.description","sort"=>"main_table.sort","url_key"=>"main_table.url_key"));
            
            
            //$this->_collection->load();
            
            //echo "<pre>";
            //print_r($this->_collection->getData());die;
            
            //echo $this->_collection->getSelect();
        }
        return $this->_collection;
    }
}
