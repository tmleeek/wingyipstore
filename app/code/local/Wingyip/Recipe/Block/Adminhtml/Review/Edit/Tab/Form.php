<?php
class Wingyip_Recipe_Block_Adminhtml_Review_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        $review = Mage::registry('review_data');
        $customer = Mage::getModel('customer/customer')->load($review->getCustomerId());
        
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array('add_variables' => false, 'add_widgets' => false,'files_browser_window_url'=>$this->getBaseUrl().'admin/cms_wysiwyg_images/index/'));
        
        $fieldset = $form->addFieldset('review_form', array('legend'=>Mage::helper('recipe')->__('Review Information')));
       
        if ($customer->getId()) {
            $customerText = Mage::helper('recipe')->__('<a href="%1$s" onclick="this.target=\'blank\'">%2$s %3$s</a> <a href="mailto:%4$s">(%4$s)</a>', $this->getUrl('*/customer/edit', array('id' => $customer->getId(), 'active_tab'=>'review')), $this->escapeHtml($customer->getFirstname()), $this->escapeHtml($customer->getLastname()), $this->escapeHtml($customer->getEmail()));
        } else {
            if (is_null($review->getCustomerId())) {
                $customerText = Mage::helper('recipe')->__('Guest');
            } elseif ($review->getCustomerId() == 0) {
                $customerText = Mage::helper('recipe')->__('Administrator');
            }
        }

        $fieldset->addField('customer', 'note', array(
            'label'     => Mage::helper('recipe')->__('Posted By'),
            'text'      => $customerText,
        ));

        $fieldset->addField('rating', 'note', array(
            'label'     => Mage::helper('recipe')->__('Summary Rating'),
            'text'      => $this->getLayout()->createBlock('recipe/adminhtml_review_rating')->toHtml(),
        ));
        
        $fieldset->addField('new_rating', 'radios', array(
            'label'     => Mage::helper('recipe')->__('Rating'),
            'name'      => 'new_rating',
            'onclick' => "",
            'onchange' => "",
            'value'  => '2',
            'values' => array(
                            array('value'=>'1','label'=>'1 star'),
                            array('value'=>'2','label'=>'2 star'),
                            array('value'=>'3','label'=>'3 star'),
                            array('value'=>'4','label'=>'4 star'),
                            array('value'=>'5','label'=>'5 star'),
                       ),
            'disabled' => false,
            'readonly' => false,
            'after_element_html' => '',
            'tabindex' => 1
        ));
        
        $fieldset->addField('nickname', 'text', array(
            'label'     => Mage::helper('recipe')->__('Nickname'),
            'required'  => true,
            'name'      => 'nickname'
        ));

        $fieldset->addField('subject', 'text', array(
            'label'     => Mage::helper('recipe')->__('Summary of Your Review'),
            'required'  => true,
            'name'      => 'title',
        ));

        $fieldset->addField('description', 'textarea', array(
            'label'     => Mage::helper('recipe')->__('Review'),
            'required'  => true,
            'name'      => 'detail',
            'style'     => 'height:24em;',
        ));       
        
        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('recipe')->__('Status'),
            'name'      => 'status',
            'required'  => true,
            'values'    => array(
                array(
                    'value'     => 0,
                    'label'     => Mage::helper('recipe')->__('Pending'),
                ),
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('recipe')->__('Not Approved'),
                ),
                array(
                    'value'     => 2,
                    'label'     => Mage::helper('recipe')->__('Approved'),
                ),
            ),
        ));
        
        if ( Mage::getSingleton('adminhtml/session')->getReviewData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getReviewData());
            Mage::getSingleton('adminhtml/session')->setReviewData(null);
        } elseif ( Mage::registry('review_data') ) {
            
            $collection = Mage::getModel('recipe/review')->getCollection();
            $collection->getSelect()
                  ->join(
                          array('de'=>Mage::getConfig()->getTablePrefix().'recipe_review_description'),
                         'de.review_id =  main_table.review_id',
                          array('de.*')
                          )
                  ->where('de.review_id = ?', Mage::registry('review_data')->getId()) ;
            
            $recipeReview = $collection->getFirstItem();
            $recipeReview['new_rating'] = $recipeReview['rating'];
            $form->setValues($recipeReview);  
            
            //echo '<pre>';print_r(Mage::registry('review_data')->getData()); die();
        }
        return parent::_prepareForm();
    }
}
