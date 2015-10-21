<?php

class Ves_Tempcp_Helper_ImportSample extends Mage_Core_Helper_Abstract {
     /*Import sample data from json*/
    public function importSample( $content = "", $module ="", $type = "json", $override = true) {
        /**
         * Get the resource model
         */
        $resource = Mage::getSingleton('core/resource');
         
        /**
         * Retrieve the write connection
         */
        $writeConnection = $resource->getConnection('core_write');

        /**
         * Retrieve the read connection
         */
        $readConnection = $resource->getConnection('core_read');
        
        switch ($type) {
            case 'csv' :

            break;
            case 'json':
            default:
               
                $data = Mage::helper('core')->jsonDecode($content);

                if(!empty($data) && is_array($data)) {
                    foreach($data as $key=>$val) {
                        /*Import Module Config*/
                        if($key == "config" && $val) {
                            foreach($val as $tmp_key => $tmp_val) {
                               if( is_numeric($tmp_key) ) { //Check multil stores to import
                                    /*For each config field group to store data for fields*/
                                    foreach($tmp_val as $k2=>$v2) {
                                        foreach($v2 as $config_key => $config_value) {
                                            if((int)$tmp_key > 0) {
                                                if($config_value) {
                                                    Mage::getConfig()->saveConfig($module.'/'.$k2.'/'.$config_key, $config_value, "stores", (int)$tmp_key );
                                                }
                                            } else {
                                                Mage::getConfig()->saveConfig($module.'/'.$k2.'/'.$config_key, $config_value);
                                            }
                                            
                                        }
                                    }
                                    
                               } else {
                               
                                    /*For each config field group to store data for fields*/
                                    foreach($tmp_val as $config_key => $config_value) {
                                        Mage::getConfig()->saveConfig($module.'/'.$tmp_key.'/'.$config_key, $config_value );
                                    }
                               }
                               
                            }
                           
                            
                        } else if($val) { //Import Table Data
                            $table_name = $resource->getTableName($key);
                            if($table_name) {

                                foreach($val as $item_query){
                                    if($item_query) {
                                        $query_data = $this->buildQueryImport( $item_query, $table_name, $override);
                                        $writeConnection->query($query_data[0], $query_data[1]);
                                    }
                                }
                            }
                        }
                    }
                }
                break;
        }
        return true;
    }

    public function buildQueryImport($data = array(), $table_name = "", $override = true) {
        $query = false;
        if($data) {
            if($override) {
                $query = "REPLACE INTO `".$table_name."` ";
                
            } else {
                $query = "INSERT IGNORE INTO `".$table_name."` ";
            }
            $fields = array();
            $values = array();
            $binds = array();
            foreach($data as $key=>$val) {
                if($val) {
                   $fields[] = "`".$key."`";
                   $values[] = ":".strtolower($key);
                   $binds[strtolower($key)] = $val;
                }
            }
            $query .= " (".implode(",", $fields).") VALUES (".implode(",", $values).")";
        }
        return array($query, $binds);
    }
}
