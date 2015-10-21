<?php

class NBSSystem_Nitrogento_Helper_Cache_Backend_File extends Cm_Cache_Backend_File
{
    public function __construct(array $options = array())
    {
        if (empty($options['cache_dir']) && class_exists('Mage', false)) {
            $options['cache_dir'] = BP . DS . 'var' . DS . 'cache';
        }
        parent::__construct($options);
    }
}