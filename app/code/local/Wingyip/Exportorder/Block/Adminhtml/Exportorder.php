<?php
class Wingyip_Exportorder_Block_Adminhtml_Exportorder extends Mage_Adminhtml_Block_Sales_Order
{
    public function __construct()
    {
        parent::__construct();
             $this->_addButton('cron', array(
                'label'     => 'Cron Export' ,
                'onclick'   => 'setLocation(\'' . $this->getConUrl() . '\')',
                'class'     => 'cron',
             ));
    }
    public function getConUrl()
    {
        return $this->getUrl('exportorder/adminhtml_exportorder/runcron');
    }

}