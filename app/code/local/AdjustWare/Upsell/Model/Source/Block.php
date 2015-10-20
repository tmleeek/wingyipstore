<?php
/**
 * Customers Who Purchased
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Upsell
 * @version      2.1.2
 * @license:     cYj8qLquCcta0g5aoIvU1NU680GWIdT5W8W05jfDJH
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Upsell_Model_Source_Block extends Varien_Object
{
    public function toOptionArray()
    {
        $options = array();
        
        $options[] = array(
                'value'=> 'Related',
                'label' => Mage::helper('adjupsell')->__('Related Products')
        );
        $options[] = array(
                'value'=> 'UpSell',
                'label' => Mage::helper('adjupsell')->__('Up-sells')
        );
        $options[] = array(
                'value'=> 'CrossSell',
                'label' => Mage::helper('adjupsell')->__('Cross-sells')
        );
        
        return $options;
    }
}
