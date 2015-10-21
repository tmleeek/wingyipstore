<?php 
/*------------------------------------------------------------------------
 # Ves Blog Module 
 # ------------------------------------------------------------------------
 # author:    Ves.Com
 # copyright: Copyright (C) 2012 http://www.ves.com. All Rights Reserved.
 # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # Websites: http://www.ves.com
 # Technical Support:  http://www.ves.com/
-------------------------------------------------------------------------*/
class Ves_Blog_Block_Latestcomment extends Ves_Blog_Block_List 
{

	
	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
		//die("AAAAAAAAA");
		
		parent::__construct( $attributes );
		$this->setTemplate( "ves/blog/block/lcomment.phtml" );
	}
	
	public function _toHtml(){

 		if(!$this->getConfig("enable_latest_comment")) {
 			return ;
 		}

		$collection = Mage::getModel( 'ves_blog/comment' )
						->getCollection();
		
		$store_id = Mage::app()->getStore()->getId();
	    if($store_id){
	        $collection->addStoreFilter($store_id);
	    }

	    //$collection->addCategoriesFilter(0);

		$collection ->setOrder( 'created', 'DESC' );

		$collection->setPageSize( $this->getConfig("latest_comment_limit") )->setCurPage( 1 );
 
		$this->assign( 'comments', $collection );	
		
		  
		return parent::_toHtml();
		
	}
	public function getCountingComment( $post_id = 0){

	      $comment = Mage::getModel('ves_blog/comment')->getCollection()
	        ->addEnableFilter( 1  )
	        ->addPostFilter( $post_id  );
	      return count($comment);
 	}

 	public function subString( $text, $length = 100, $replacer ='...', $is_striped=true ){
 			$text = trim($text);
            $text = ($is_striped==true)?strip_tags($text):$text;
            if(strlen($text) <= $length){
                return $text;
            }
            $text = substr($text,0,$length);
            $pos_space = strrpos($text,' ');
            return substr($text,0,$pos_space).$replacer;
    }
}	
