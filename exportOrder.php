<?php 
require 'app/Mage.php';
umask(0);
Mage::app('admin');  
Mage::getModel('exportorder/exportorder')->exportOrderData();// read data from txt

