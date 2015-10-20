<?php
class Wingyip_Shipping_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getdigitCodeDpd($shippingMethod)
    {
        $digitCode='1^12';
        if(!is_null($shippingMethod))
        {
            if($shippingMethod==='productmatrix_Next_working_day_after_dispatch'){
                $digitCode='1^12';
            }else if($shippingMethod==='productmatrix_Next_working_day_after_dispatch_by_noon'){
                $digitCode='1^13';
            }else if($shippingMethod==='productmatrix_Sunday_delivery'){
                $digitCode='1^07';
            }else if($shippingMethod==='productmatrix_Saturday_delivery'){
                $digitCode='1^16';
            }else if($shippingMethod==='productmatrix_Next_working_day_after_dispatch_by_10am'){
                $digitCode='1^14';
            }else if($shippingMethod==='productmatrix_Saturday_delivery_by_noon'){
                $digitCode='1^17';
            }else if($shippingMethod==='productmatrix_Saturday_delivery_by_10am'){
                $digitCode='1^18';
            }
        }
        return $digitCode;
    }
}