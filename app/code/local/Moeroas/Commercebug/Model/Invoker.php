<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
class Moeroas_Commercebug_Model_Invoker extends Mage_Core_Model_Event_Invoker_InvokerDefault
{
    protected $_commerceBugCollector;
    public function getCommerceBugCollector()
    {
        if(!$this->_commerceBugCollector)
        {
            $this->_commerceBugCollector = new Moeroas_Commercebug_Model_Collectorevents;                    
        }
        return $this->_commerceBugCollector;
    }
    
    public function dispatch(array $configuration, Varien_Event_Observer $observer)
    {            
        $event_name = $observer->getEvent()->getName();
        $this->getCommerceBugCollector()->collectObserverMage2($event_name, $configuration);
        return parent::dispatch($configuration, $observer);
    }
}