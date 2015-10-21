<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
class Moeroas_Commercebug_Model_Graphviz
{
    public function capture()
    {    
        $collector  = new Moeroas_Commercebug_Model_Collectorgraphviz; 
        $o = new stdClass();
        $o->dot = Moeroas_Commercebug_Model_Observer_Dot::renderGraph();
        $collector->collectInformation($o);
    }
    
    public function getShim()
    {
        $shim = Moeroas_Commercebug_Model_Shim::getInstance();        
        return $shim;
    }    
}