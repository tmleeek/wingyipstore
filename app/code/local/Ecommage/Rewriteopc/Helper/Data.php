<?php
class Ecommage_Rewriteopc_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param $postcode
     * @param $countryId
     * @return bool
     */
    public function validatePostcodeUK($postcode){
            $postcode = str_replace(' ', '', $postcode); // remove any spaces;
            $postcode = strtoupper($postcode); // force to uppercase;
            $valid_postcode_exp = "/^(([A-PR-UW-Z]{1}[A-IK-Y]?)([0-9]?[A-HJKS-UW]?[ABEHMNPRVWXY]?|[0-9]?[0-9]?))\s?([0-9]{1}[ABD-HJLNP-UW-Z]{2})$/i";
            if(preg_match($valid_postcode_exp, strtoupper($postcode))){
                return true;
            }
        return false;
    }
}