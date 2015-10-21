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
class Wingyip_Realex_Block_Realmpi3dsecure_Form extends Mage_Payment_Block_Form_Ccsave
{
    /**
     * @return void
     */
    protected function _construct()
    {
                            
        parent::_construct();
        $method = Mage::getModel('realex/realmpi3dsecure');
        $this->setMethod($method);
        $info = Mage::getModel('payment/info');
        $method->setData('info_instance', $info);
        $this->setTemplate('realex/realmpi3dsecure/form.phtml');
    }
    
    /**
     * Retrieve credit card start years
     *
     * @return array
     */
    public function getCcStartYears()
    {
    	$years = array();
        $first = date("Y");

        for ($index=0; $index<10; $index++){
            $year = $first - $index;
            $years[$year] = $year;
        }
        return $years;
    }
}
