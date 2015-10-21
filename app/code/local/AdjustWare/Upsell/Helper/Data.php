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
class AdjustWare_Upsell_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isBlockSelected($block)
    {
        $blocks = explode(',',(string)Mage::getStoreConfig('catalog/adjupsell/block'));
        return in_array($block, $blocks);
    }
}
