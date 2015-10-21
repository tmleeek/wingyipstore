<?php 
class Wingyip_Recipe_Block_Adminhtml_Review_Rating extends Mage_Adminhtml_Block_Template{
    public function __construct()
    {
        $this->setTemplate('recipe/review.phtml');
        $this->setReviewId(Mage::registry('review_data')->getId());
    }
}
