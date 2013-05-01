<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Checkout_TestController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $quote = Mage::getModel('sales/quote');
        echo "<pre>".print_r($quote,1)."</pre>";

    }

    public function createEntitiesAction()
    {
        $setup = Mage::getModel('sales_entity/setup', 'sales_setup');
        $setup->installEntities($setup->getDefaultEntities());
    }

    public function mailAction()
    {
    	$order = Mage::getModel('sales/order')->load(23);
    	$billing = $order->getBillingAddress();
    	Mage::getModel('sales/email_template')
    		->sendTransactional('new_order', $billing->getEmail(), $billing->getName(), array('order'=>$order, 'billing'=>$billing));
    }

    public function trackingAction()
    {
        $carrier= Mage::getModel('Usa/shipping_carrier_ups');
        //$carrier->getTracking(array('1231230011','2342340011','7897890011'));
        //$carrier->getTracking(array('EQ944289016US','EQ944290195US'));
        $carrier->getTracking(array('1Z020FF91260351815','1Z020FF90360351074','1ZV953560349447013'));
        //$carrier->getTracking(array('749059830009648','749059830009358'));

    }

    public function paymentAction()
    {


        //payflow testing
        /*
        $payment= Mage::getModel('Paygate/payflow_pro');
        //Mage_Payment_Model_Info
        $paymentinfo= Mage::getModel('Payment/info');
        $paymentinfo->setCcTransId('V19A0CEB3717');
        */

        //authorizenet testing
         $payment= Mage::getModel('Paygate/authorizenet');
        //Mage_Payment_Model_Info
        $paymentinfo= Mage::getModel('Payment/info');
        $paymentinfo->setCcTransId('V19A0CEB3717');

        $payment->canVoid($paymentinfo);

echo "<pre><hr>";
echo "AFTER CAN VOID:";
print_r($paymentinfo->getData());

        if($paymentinfo->getStatus()==Mage_Payment_Model_Method_Abstract::STATUS_VOID){
                //void the transaction
                $payment->void($paymentinfo);
           echo "AFTER VOID:";
           print_r($paymentinfo->getData());
        }

        if($paymentinfo->getStatus()!=Mage_Payment_Model_Method_Abstract::STATUS_ERROR &&
            $paymentinfo->getStatus()!=Mage_Payment_Model_Method_Abstract::STATUS_VOID &&
            $paymentinfo->getStatus()!=Mage_Payment_Model_Method_Abstract::STATUS_SUCCESS){
            $paymentinfo->setAmount('0.6');
            //credit the transaction
            $payment->refund($paymentinfo);
            echo "AFTER CREDIT2:";
            print_r($paymentinfo->getData());
        }

        if($paymentinfo->getStatus()==Mage_Payment_Model_Method_Abstract::STATUS_ERROR){
           //error in retreiving transaction*
           echo "#####ERROR:".$paymentinfo->getStatusDescription();
        }
echo "<hr>";





    }
}