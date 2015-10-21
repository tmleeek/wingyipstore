<?php
class Wingyip_Recipe_Block_Search extends Mage_Core_Block_Template{
    
    
    
    
    
    public function getSearchStr()
    {
        $data = $this->htmlEscape($this->getRequest()->getParams());
        return $data['search'];
    }
}
