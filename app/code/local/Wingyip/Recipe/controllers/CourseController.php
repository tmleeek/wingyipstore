<?php
class Wingyip_Recipe_CourseController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
            $this->loadLayout();
            $this->renderLayout();
    }
    public function viewAction()
    {
        $this->loadLayout();
        $this->renderLayout(); 
    }
}
