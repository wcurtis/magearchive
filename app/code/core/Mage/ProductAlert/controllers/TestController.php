<?php

class Mage_ProductAlert_TestController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        Mage::dispatchEvent('catalog_product_recalc', array());
    }
}