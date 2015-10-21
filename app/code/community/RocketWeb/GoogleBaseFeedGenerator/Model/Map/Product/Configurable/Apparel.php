<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   RocketWeb
 * @package    RocketWeb_GoogleBaseFeedGenerator
 * @copyright  Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */

class RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Configurable_Apparel extends RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Simple_Apparel {

	protected $_is_variants = false;
	protected $_variants_rows = false;
	protected $_original_variants_rows = false;
	protected $_assoc_ids;
	protected $_assocs;
	protected $_cache_associated_prices;

    public function initialize() {
		parent::initialize();
		$this->setApparelCategories();
	}

    /**
     * @return $this
     */
    public function _beforeMap() {

		$assocMapArr = array();
		$this->_assocs = array();
		$this->_cache_associated_prices = array();

		foreach ($this->getAssocIds() as $assocId) {
			$assoc = Mage::getModel('catalog/product');
	    	$assoc->setStoreId($this->getStoreId());
	    	$assoc->getResource()->load($assoc, $assocId);
	    	
	    	if (!$this->getConfigVar('add_out_of_stock_configurable_assoc')) {
				$stockItem = Mage::getModel('cataloginventory/stock_item');
				$stockItem->setStoreId($this->getStoreId());
				$stockItem->getResource()->loadByProductId($stockItem, $assoc->getId());
				$stockItem->setOrigData();
		
				if ($stockItem->getId() && $stockItem->getIsInStock()) {
					$this->_assocs[$assocId] = $assoc;
				}
			} else {
				$this->_assocs[$assocId] = $assoc;
			}
			
			if (!($this->_setCacheAssociatedPricesByProduct($assoc) === true)) {
				unset($this->_assocs[$assocId]);
				continue;
			}
    	
			$assocMap = $this->getAssocMapModel($assoc);
			if ($assocMap->checkSkipSubmission()->isSkip()) {
				if ($this->getConfigVar('log_skip')) {
	    			$this->log(sprintf("product id %d product sku %s, skipped - product has 'Skip from Being Submitted' = 'Yes'.", $assoc->getId(), $assoc->getSku()));
	    		}
	    		continue;
			}
			$assocMapArr[$assoc->getId()] = $assocMap;
		}
		
		$this->setAssocMaps($assocMapArr);
		
    	return $this;
    }
    
    /**
     * Array with associated products ids in current store.
     *
     * @return array
     */
    public function getAssocIds() {

    	if (is_null($this->_assoc_ids))
			$this->_assoc_ids = $this->loadAssocIds($this->getProduct(), $this->getStoreId());
		return $this->_assoc_ids;
    }

    /**
     * @return array
     */
    public function _map() {

		$rows = array();
		$this->_variants_rows = array();
		foreach ($this->getAssocMaps() as $assocId => $assocMap) {
			$row = $assocMap->map();
			reset($row);
			$row = current($row);
			if (!$assocMap->isSkip())
				$this->_variants_rows[] = $row;
			$rows[] = $row;
		}
		$this->_original_variants_rows = $this->_variants_rows;
		
		$this->_is_variants = $this->validateVariants($this->_variants_rows);
		
		if ($this->_is_variants) {
			$rows = $this->_variants_rows;
		} else {
			if ($this->getConfigVar('for_us', 'apparel')) {
				// As stand alone apparel product - no variants.
			    $rows = parent::_map();
				$rows = $this->formUsConfigurableNonVariant($rows);
			} else {
				$rows = parent::_map();
				$rows = $this->formOtherConfigurableNonVariant($rows);
			}
		}
		
		return $rows;
	}

    /**
     * @param $rows
     * @return $this
     */
    public function _afterMap($rows) {

		if (!$this->_is_variants) {
			parent::_afterMap($rows);
		}
		return $this;
	}
	
	/**
     * @param Mage_Catalog_Model_Product $product
     * @return RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Abstract
     */
    protected function getAssocMapModel($product) {

    	$params = array(
    		'store_code' => $this->getData('store_code'),
    		'store_id' => $this->getData('store_id'),
    		'website_id' => $this->getData('website_id'),
    	);
    	
    	$productMap = Mage::getModel('googlebasefeedgenerator/map_product_associated_apparel', $params);
    	$productMap->setProduct($product)
			->setColumnsMap($this->_columns_map)
			->setEmptyColumnsReplaceMap($this->_empty_columns_replace_map)
			->setParentMap($this)
			->initialize();
    	
    	return $productMap;
    }
    
    /**
     * @param array $params
     * @return string
     */
    protected function mapAttributeWeight($params = array()) {

    	$map = $params['map'];
    	$product = $this->getProduct();
    	/** @var $product Mage_Catalog_Model_Product */
    	
    	$default_value = isset($map['default_value']) ? $map['default_value'] : "";
    	if ($default_value != "") {
    		$weight = $default_value;
    		$weight .= ' '.$this->getConfigVar('weight_unit_measure', 'columns');

    		return $this->cleanField($weight);
    	}
    	
    	$weight_attribute = $this->getGenerator()->getAttribute($map['attribute']);
		if ($weight_attribute === false)
			Mage::throwException(sprintf('Couldn\'t find attribute \'%s\'.', $map['attribute']));
		
		$weight = $this->getAttributeValue($product, $weight_attribute);
		if ($weight != "")
			$weight .= ' '.$this->getConfigVar('weight_unit_measure', 'columns');
		
		// Configurable doesn't have weight of it's own.
		if ($weight == "") {
			$min_price = PHP_INT_MAX;
			foreach ($this->_assocs as $assoc) {
				if ($this->getCacheAssociatedPrice($assoc->getId()) !== false && $min_price > $this->getCacheAssociatedPrice($assoc->getId())) {
					$min_price = $this->getCacheAssociatedPrice($assoc->getId());
					$weight = $this->getAttributeValue($assoc, $weight_attribute);
					break;
				}
			}
		}

        if ($weight != "" && strpos($weight, $this->getConfigVar('weight_unit_measure', 'columns')) === false) {
            $weight .= ' '.$this->getConfigVar('weight_unit_measure', 'columns');
        }
    	
    	return $this->cleanField($weight);
    }

    /**
     * Check the value of apparel rows that should vary to have different values.
     * Returns: false, if no combination varies meaning the product is a non-variant apparel
     *
     * @param $rows
     * @return bool
     */
    public function validateVariants(&$rows) {

    	$is_variants = true;
    	$only_varies = array();
    	$only_varies_all = array('material', 'pattern');
    	foreach ($only_varies_all as $column) {
    		if (isset($this->_columns_map[$column]))
    			$only_varies[] = $column;
    	}
    	unset($only_varies_all);
    	
    	$changed = false;
    	foreach ($only_varies as $column) {
    		$tt = array();
	    	foreach ($rows as $line => $row){
	    		if (isset($row[$column]) && $row[$column] != "")
	    			$tt[strtolower($row[$column])] = true;
	    	}
	    	
	    	// Doesn't vary, no value in feed.
	    	if (count($tt) <= 1) {
	    		foreach ($rows as $line => $row)
	    			$rows[$line][$column] = "";
	    	}
    	}
    	
    	reset($rows);
    	$row = current($rows);
    	$gb_category = (isset($row['google_product_category']) ? $row['google_product_category'] : "");
    	$must_vary = array('color');
    	if (isset($this->_columns_map['material']))
			$must_vary[] = 'material';
		if (isset($this->_columns_map['pattern']))
			$must_vary[] = 'pattern';
		
    	if ($this->matchApparelClothingCategory($gb_category) || $this->matchApparelShoesCategory($gb_category))
    		$must_vary[] = 'size';

    	// More than 1 line for a combination of variants values => choose by image and minimal price.
    	$variants_values = array();
    	$configurable_image = $this->mapColumn('image_link');
    	foreach ($rows as $line => $row) {
    		$tt = "";
			foreach ($must_vary as $column) {
				if (array_key_exists($column, $row)) {
                    $tt .= $row[$column];
                } else {
                    $this->log('Product id:'. $row['id']. ' is missing the apparel variant column "'. $column. '". Please add this column to the feed.');
                }
            }
			if (!isset($variants_values[$tt]))
				$variants_values[$tt] = array();
			$variants_values[$tt][] = $line;
    	}
    	
    	foreach ($variants_values as $v) {
    		if (count($v) > 0) {
    			$keep = false;
    			$minimal_price = PHP_INT_MAX;
    			foreach ($v as $line) {
    				// It should have and image and is minimal price.
    				if ((!$this->getConfigVar('variant_submit_no_img', 'apparel') && isset($rows[$line]['image_link']) && $rows[$line]['image_link'] != "") && $rows[$line]['price'] < $minimal_price) {
    					$keep = $line;
    					$minimal_price = $rows[$line]['price'];
    				}
    			}
    			
    			if ($keep !== false) {
    				foreach ($v as $line) {
    					if ($keep !== $line) {
    						unset($rows[$line]);
    						$changed = true;
    					}
    				}
    			} else {
    				// Get image from configurable.
    				$keep = false;
    				$minimal_price = PHP_INT_MAX;
    				foreach ($v as $line) {
	    				if ($rows[$line]['price'] < $minimal_price) {
	    					$keep = $line;
	    					$minimal_price = $rows[$line]['price'];
	    				}
	    			}
	    			
	    			foreach ($v as $line) {
    					if ($keep !== $line) {
    						unset($rows[$line]);
    						$changed = true;
    					}
    				}
    				
    				$rows[$keep]['image_link'] = $configurable_image;
    			}
    		}
    	}
    	
    	if ($this->getConfigVar('submit_no_img', 'apparel') && $this->getConfigVar('variant_submit_no_img', 'apparel')) {
    		// No image -> get configurable image.
	    	foreach ($rows as $line => $row) {
	    		if (isset($rows[$line]['image_link']) && $rows[$line]['image_link'] == "") {
	    			$rows[$line]['image_link'] = $configurable_image;
	    		}
	    	}
    	}
    	
    	if (!$this->getConfigVar('submit_no_img', 'apparel')) {
    		// Should have at least configurable image.
    		$crows = $rows;
    		foreach ($crows as $line => $row) {
    			if (!isset($row['image_link']) || (isset($row['image_link']) && $row['image_link'] == "")) {
	    			unset($rows[$line]);
	    			$changed = true;
	    		}
    		}
    	}
    	
    	if (count($rows) <= 1) {
    		$is_variants = false;
    		// no variants, clear item_group_id
    		foreach ($rows as $line => $row) {
    			if (isset($row['item_group_id']))
    				$rows[$line]['item_group_id'] = "";
    		}
    	} else {
    		if ($changed) {
    			// Change title and description with configurable data
    			$varies = array('color');
    			if ($this->matchApparelClothingCategory($gb_category) || $this->matchApparelShoesCategory($gb_category))
    				$varies[] = 'size';
    			
	    		if (isset($this->_columns_map['material']))
					$varies[] = 'material';
				if (isset($this->_columns_map['pattern']))
					$varies[] = 'pattern';
    			
    			$parent_title = $this->mapColumn('title');
    			$parent_description = $this->mapColumn('description');
    			foreach ($rows as $line => $row) {
    				if (isset($row['description']) && $row['description'] == "")
    					$rows[$line]['description'] = $parent_description;
    				
    				if (isset($row['title']))
    					$rows[$line]['title'] = $parent_title;
    			}
    		}
    	}
    	
    	return $is_variants;
    }

    /**
     * @param $rows
     * @return array
     */
    protected function formUsConfigurableNonVariant($rows) {

    	reset($rows);
    	$fields = current($rows);

    	$gb_category = (isset($fields['google_product_category']) ? $fields['google_product_category'] : "");
    	$must_have = array('color');
    	if ($this->matchApparelClothingCategory($gb_category) || $this->matchApparelShoesCategory($gb_category))
    		$must_have[] = 'size';
    	
    	// If empty color or size try replace configurable with valid associated product color/size/price that has minimal price.
    	$minimal_price = PHP_INT_MAX;
    	$keep = false;
    	if ((isset($fields['color']) && $fields['color'] == "") || (array_search("size", $must_have) !== false && isset($fields['size']) && $fields['size'] == "")) {
    		foreach ($this->_original_variants_rows as $line => $row) {
    			$all = true;
    			foreach ($must_have as $column) {
    				if (!isset($row[$column]) || (isset($row[$column]) && $row[$column] == ""))
    				$all = false;
    			}
    			
    			if ($all && $row['price'] < $minimal_price) {
    				$keep = $line;
    				$minimal_price = $row['price'];
    			}
    		}
    	}
    	
    	if ($keep !== false && $this->_original_variants_rows[$keep]['image_link'] == "") {
    		// Get configurable image.
    		$configurable_image = $this->mapColumn('image_link');
    		if ($configurable_image != "") {
    			$this->_original_variants_rows[$keep]['image_link'] = $configurable_image;
    		} else {
    			$keep = false;
    		}
    	}
    	
    	if ($keep !== false) {
    		if (isset($fields['color']) && $fields['color'] == "")
    			$fields['color'] = $this->_original_variants_rows[$keep]['color'];
    		
    		if (isset($fields['size']) && $fields['size'] == "")
    			$fields['size'] = $this->_original_variants_rows[$keep]['size'];
    		
    		if ($this->_original_variants_rows[$keep]['price'] > 0)
    			$fields['price'] = $this->_original_variants_rows[$keep]['price'];
    		
    		if ($this->_original_variants_rows[$keep]['sale_price'] > 0) {
    			$fields['sale_price'] = $this->_original_variants_rows[$keep]['sale_price'];
    			if ($this->_original_variants_rows[$keep]['sale_price_effective_date'] != "")
    				$fields['sale_price_effective_date'] = $this->_original_variants_rows[$keep]['sale_price_effective_date'];
    		}
    		
    		// Configurable does not have weight of it's own, fill with child's weight.
    		if (isset($fields['shipping_weight'])) {
    			$fields['shipping_weight'] = $this->_original_variants_rows[$keep]['shipping_weight'];
    		}
    	} else {
    		// Pass intact configurable values.
    	}
    	
    	return array($fields);
    }

    /**
     * @param $rows
     * @return array
     */
    protected function formOtherConfigurableNonVariant($rows) {

    	reset($rows);
    	$fields = current($rows);
    	
    	// compact apparel fields
    	$varies = array('color', 'size', 'material', 'pattern', 'gender', 'age_group');
    	foreach ($varies as $column) {
    		if (isset($fields[$column])) {
    			$values = array();
    			if ($fields[$column] != "") {
    				$arr = explode(",", $fields[$column]);
    				foreach ($arr as $k => $v)
    					$values[trim($v)] = trim($v);
    			}
    			
    			foreach ($this->_variants_rows as $line => $row) {
					if (isset($row[$column]) && $row[$column] != "") {
						$arr = explode(",", $row[$column]);
						foreach ($arr as $k => $v)
							$values[trim($v)] = trim($v);
					}
				}
				
				$fields[$column] = implode(",", $values);
    		}
    	}

    	return array($fields);
    }
    
    /**
     * Redundant code with RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Configurable
     *
     * @return float
     */
    public function getPrice($product = null) {

    	if (is_null($product)) {
    		$product = $this->getProduct();
    	}

    	if (!$this->hasSpecialPrice($product, $this->getSpecialPrice($product))) {
    		$price = $this->calcMinimalPrice($product);
    	} else {
    		$price = $product->getPrice();
    	}
    	
    	if ($price <= 0) {
			$this->skip = true;
			if ($this->getConfigVar('log_skip')) {
    			$this->log(sprintf("product id %d product sku %s, skipped - can't determine the minimal price: '%s'.", $product->getId(), $product->getSku(), $price));
    		}
		}
		
		return $price;
    }
    
    /**
     * Redundant code with RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Configurable
     *
     * @return float
     */
    public function calcMinimalPrice($product) {

    	$price = 0.0;
    	$minimal_price = PHP_INT_MAX;
		foreach ($this->_assocs as $assoc) {
			if ($minimal_price > $this->getCacheAssociatedPrice($assoc->getId())) {
				$minimal_price = $this->getCacheAssociatedPrice($assoc->getId());
			}
		}
		if ($minimal_price < PHP_INT_MAX) {
			$price = $minimal_price;
		}
		
		return $price;
    }

    /**
     * @param $assoc
     * @return bool
     */
    protected function _setCacheAssociatedPricesByProduct($assoc) {

        $configurable_attributes = $this->getTools()->getConfigurableAttributesAsArray($this->getProduct());
		$base_price = $this->getProduct()->getPrice();
		$price = $base_price;
		$all = true;

		if (is_array($configurable_attributes)) {
			foreach ($configurable_attributes as $res) {
				$f = false;
				if (is_array($res['values'])) {
					foreach ($res['values'] as $value) {
						if (isset($value['value_index']) && $assoc->getData($res['attribute_code']) == $value['value_index']) {
							if (isset($value['is_percent']) && $value['is_percent']) {
								$price += $base_price * $value['pricing_value'] / 100;
							} else {
								$price += $value['pricing_value'];
							}
							$f = true;
							break;
						}
					}
				}
				if (!$f) {
					$all = false;
				}
			}
		}
		if ($all) {
			$this->_cache_associated_prices[$assoc->getId()] = $price;
			return true;
		} else {
			return false;
		}
    }

    /**
     * @param $assocId
     * @return bool
     */
    public function getCacheAssociatedPrice($assocId) {

    	if (isset($this->_cache_associated_prices[$assocId])) {
    		return $this->_cache_associated_prices[$assocId];
    	}
    	return false;
    }

    public function getIsVariants() {
    	$this->_is_variants;
    }

    /**
     * Aside from clearing the Map object, also clears the associated Map objects
     */
    public function __destruct() {

        if (is_array($this->getAssocMaps())) {
            foreach ($this->getAssocMaps() as $assocMap) {
                $assocMap->__destruct();
            }
        }
    	parent::__destruct();
    }
}
