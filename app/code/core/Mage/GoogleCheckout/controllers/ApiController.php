<?php

class Mage_GoogleCheckout_ApiController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
#error_log('test', 3,'/home/moshe/dev/test/callback.log');
#error_log('googlecheckout/api/index: '.(@date('Y-m-d H:i:s')).' '.$_SERVER['REQUEST_URI'].print_r(file_get_contents('php://input'),1)."\n", 3, '/home/moshe/dev/test/callback.log');
#ob_start();
        Mage::getModel('googlecheckout/api')->processCallback();
#$response = ob_get_flush();
#error_log(__METHOD__.' '.(@date('Y-m-d H:i:s')).' '.$response."\n", 3, '/home/moshe/dev/test/callback.log');
        exit;
    }

    public function beaconAction()
    {
        Mage::getModel('googlecheckout/api')->processBeacon();
    }

}