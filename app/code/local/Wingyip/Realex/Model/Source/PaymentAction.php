<?php
/**
 * Wingyip_Realex extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Wingyip
 * @package    Wingyip_Realex
 
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Wingyip
 * @package    Wingyip_Realex
 * @author     Wingyip
 */
class Wingyip_Realex_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Mage_Realex_Model_Remote::ACTION_AUTHORIZE,
                'label' => Mage::helper('realex')->__('Defer Settlement')
            ),
            array(
                'value' => Mage_Realex_Model_Remote::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('realex')->__('Settle Immediately')
            ),
        );
    }
}

?>
