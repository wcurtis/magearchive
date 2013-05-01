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


class Mage_Checkout_Model_Type_Onepage
{
    /**
     * Enter description here...
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Enter description here...
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * Enter description here...
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function initCheckout()
    {
        $checkout = $this->getCheckout();
        if (is_array($checkout->getStepData())) {
            foreach ($checkout->getStepData() as $step=>$data) {
                if (!($step==='login'
                    || Mage::getSingleton('customer/session')->isLoggedIn() && $step==='billing')) {
                    $checkout->setStepData($step, 'allow', false);
                }
            }
        }
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param string $method
     * @return array
     */
    public function saveCheckoutMethod($method)
    {
        if (empty($method)) {
            $res = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('Invalid data')
            );
            return $res;
        }

        $this->getQuote()->setCheckoutMethod($method)->save();
        $this->getCheckout()->setStepData('billing', 'allow', true);
        return array();
    }

    /**
     * Enter description here...
     *
     * @param int $addressId
     * @return Mage_Customer_Model_Address
     */
    public function getAddress($addressId)
    {
        $address = Mage::getModel('customer/address')->load((int)$addressId);
        $address->explodeStreetAddress();
        if ($address->getRegionId()) {
            $address->setRegion($address->getRegionId());
        }
        return $address;
    }

    /**
     * This method is called by One Page Checkout JS (AJAX) while saving the billing information.
     *
     * @param unknown_type $data
     * @param unknown_type $customerAddressId
     * @return unknown
     */
    public function saveBilling($data, $customerAddressId)
    {
        if (empty($data)) {
            $res = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('Invalid data')
            );
            return $res;
        }

        $address = $this->getQuote()->getBillingAddress();

        // DELETE
        //print_r($data);

        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                $address->importCustomerAddress($customerAddress);
            }
        } else {
            $address->addData($data);
        }
        if (!$this->getQuote()->getCustomerId() && 'register' == $this->getQuote()->getCheckoutMethod()) {
            $email = $address->getEmail();
            $customer = Mage::getModel('customer/customer')->loadByEmail($email);
            if ($customer->getId()) {
                $res = array(
                    'error' => 1,
                    'message' => Mage::helper('checkout')->__('There is already a customer registered using this email address')
                );
                return $res;
            }
        }

        $address->implodeStreetAddress();
//        if (empty($data['use_for_shipping'])) {
//            $data['use_for_shipping'] = 0;
//        }
//        else {
//            $data['use_for_shipping'] = 1;
//        }
//
//        if (!empty($data['use_for_shipping'])) {
//            $billing = clone $address;
//            $billing->unsEntityId()->unsAddressType();
//            $shipping = $this->getQuote()->getShippingAddress();
//            $shipping->addData($billing->getData())
//                ->setSameAsBilling(1)
//                ->setCollectShippingRates(true);
//            $this->getCheckout()->setStepData('shipping', 'complete', true);
//        } else {
//            $shipping = $this->getQuote()->getShippingAddress();
//            $shipping->setSameAsBilling(0);
//        }
//
//        if ($address->getCustomerPassword()) {
//            $customer = Mage::getModel('customer/customer');
//            $this->getQuote()->setPasswordHash($customer->encryptPassword($address->getCustomerPassword()));
//        }
//
//        $this->getQuote()->save();
//
//        $this->getCheckout()
//            ->setStepData('billing', 'allow', true)
//            ->setStepData('billing', 'complete', true)
//            ->setStepData('shipping', 'allow', true);

        switch((int) $data['pickup_or_use_for_shipping']) {
            case 1:
                $billing = clone $address;
                $billing->unsEntityId()->unsAddressType();
                $shipping = $this->getQuote()->getShippingAddress();
                $shipping->addData($billing->getData())
                    ->setSameAsBilling(1)
                    ->setCollectShippingRates(true);
                $this->getCheckout()->setStepData('shipping', 'complete', true);
                break;
            case 0:
                $shipping = $this->getQuote()->getShippingAddress();
                $shipping->setSameAsBilling(0);
                break;
            case 2:
                $shipping = $this->getQuote()->getShippingAddress();
                $shipping->setSameAsBilling(0);
                break;
        }

        if ($address->getCustomerPassword()) {
            $customer = Mage::getModel('customer/customer');
            $this->getQuote()->setPasswordHash($customer->encryptPassword($address->getCustomerPassword()));
        }

        $this->getQuote()->save();

        $this->getCheckout()
            ->setStepData('billing', 'allow', true)
            ->setStepData('billing', 'complete', true)
            ->setStepData('shipping', 'allow', true);

        return array();
    }

    public function saveShipping($data, $customerAddressId)
    {
        if (empty($data)) {
            $res = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('Invalid data')
            );
            return $res;
        }
        $address = $this->getQuote()->getShippingAddress();

        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                $address->importCustomerAddress($customerAddress);
            }
        } else {
            $address->addData($data);
        }
        $address->implodeStreetAddress();
        $address->setCollectShippingRates(true);
        $this->getQuote()->save();

        $this->getCheckout()
            ->setStepData('shipping', 'complete', true)
            ->setStepData('shipping_method', 'allow', true);

        return array();
    }

    public function saveShippingMethod($shippingMethod)
    {
        if (empty($shippingMethod)) {
            $res = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('Invalid data')
            );
            return $res;
        }
        $this->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
        $this->getQuote()->collectTotals()->save();

        $this->getCheckout()
            ->setStepData('shipping_method', 'complete', true)
            ->setStepData('payment', 'allow', true);

        return array();
    }

    public function savePayment($data)
    {
        if (empty($data)) {
            $res = array(
                'error' => -1,
                'message' => Mage::helper('checkout')->__('Invalid data')
            );
            return $res;
        }
        $payment = $this->getQuote()->getPayment();
        $payment->importData($data);
        $this->getQuote()->save();

        $this->getCheckout()
            ->setStepData('payment', 'complete', true)
            ->setStepData('review', 'allow', true);

        return array();
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function saveOrder()
    {
        $res = array('error'=>1);

        try {
            $billing = $this->getQuote()->getBillingAddress();
            $shipping = $this->getQuote()->getShippingAddress();

            switch ($this->getQuote()->getCheckoutMethod()) {
            case 'guest':
                $this->getQuote()->setCustomerEmail($billing->getEmail());
                $email  = $billing->getEmail();
                $name   = $billing->getFirstname().' '.$billing->getLastname();
                break;

            case 'register':
                $customer = $this->_createCustomer();
                $customer->sendNewAccountEmail();
                $email  = $customer->getEmail();
                $name   = $customer->getName();
                break;

            default:
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $email  = $customer->getEmail();
                $name   = $customer->getName();

                $billing = $this->getQuote()->getBillingAddress();
                $shipping = $this->getQuote()->getShippingAddress();
                if (!$billing->getCustomerAddressId()) {
                    $customerBilling = $billing->exportCustomerAddress();
                    $customer->addAddress($customerBilling);
                }
                if (!$shipping->getCustomerAddressId() && !$shipping->getSameAsBilling()) {
                    $customerShipping = $shipping->exportCustomerAddress();
                    $customer->addAddress($customerShipping);
                }
                $customer->save();

                $changed = false;
                if (isset($customerBilling) && !$customer->getDefaultBilling()) {
                    $customer->setDefaultBilling($customerBilling->getId());
                    $changed = true;
                }
                if (isset($customerBilling) && !$customer->getDefaultShipping() && $shipping->getSameAsBilling()) {
                    $customer->setDefaultShipping($customerBilling->getId());
                    $changed = true;
                }
                elseif (isset($customerShipping) && !$customer->getDefaultShipping()){
                    $customer->setDefaultShipping($customerShipping->getId());
                    $changed = true;
                }

                if ($changed) {
                    $customer->save();
                }
            }

            $convertQuote = Mage::getModel('sales/convert_quote');
            /* @var $convertQuote Mage_Sales_Model_Convert_Quote */
            $order = Mage::getModel('sales/order');
            /* @var $order Mage_Sales_Model_Order */

            $order = $convertQuote->addressToOrder($shipping);
            $order->setBillingAddress($convertQuote->addressToOrderAddress($billing));
            $order->setShippingAddress($convertQuote->addressToOrderAddress($shipping));
            $order->setPayment($convertQuote->paymentToOrderPayment($this->getQuote()->getPayment()));

            foreach ($this->getQuote()->getAllItems() as $item) {
               $order->addItem($convertQuote->itemToOrderItem($item));
            }

            /**
             * We can use configuration data for declare new order status
             */
            Mage::dispatchEvent('checkout_type_onepage_save_order', array('order'=>$order, 'quote'=>$this->getQuote()));
            #$order->save();
            $order->place();
            $order->save();

            $this->getQuote()->setIsActive(false);
            $this->getQuote()->save();

            $orderId = $order->getIncrementId();
            $this->getCheckout()->setLastQuoteId($this->getQuote()->getId());
            $this->getCheckout()->setLastOrderId($order->getId());
            $this->getCheckout()->setLastRealOrderId($order->getIncrementId());

            $order->sendNewOrderEmail();

            $res['success'] = true;
            $res['error']   = false;
            //$res['error']   = true;
        }
        catch (Mage_Core_Exception $e){
            $res['success'] = false;
            $res['error'] = true;
            $res['error_messages'] = $e->getMessage();
        }
        catch (Exception $e){
            echo $e;
            $res['success'] = false;
            $res['error'] = true;
            if (isset($order)) {
                $res['error_messages'] = $order->getErrors();
            }
        }

        return $res;
    }

    protected function _createCustomer()
    {
        $quote = $this->getQuote();

        $customer = Mage::getModel('customer/customer');

        $billingEntity = $quote->getBillingAddress();
        $billing = $billingEntity->exportCustomerAddress();
        $customer->addAddress($billing);

        $shippingEntity = $quote->getShippingAddress();
        if (!$shippingEntity->getSameAsBilling()) {
            $shipping = $shippingEntity->exportCustomerAddress();
            $customer->addAddress($shipping);
        } else {
            $shipping = $billing;
        }
        //TODO: check that right primary types are assigned

        $customer->setFirstname($billing->getFirstname());
        $customer->setLastname($billing->getLastname());
        $customer->setEmail($billing->getEmail());
        $customer->setPassword($customer->decryptPassword($quote->getPasswordHash()));
        $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));

        $customer->save();

        $customer->setDefaultBilling($billing->getId());
        $customer->setDefaultShipping($shipping->getId());
        $customer->save();

        $quote->setCustomer($customer);
        $billingEntity->setCustomerId($customer->getId())->setCustomerAddressId($billing->getId());
        $shippingEntity->setCustomerId($customer->getId())->setCustomerAddressId($shipping->getId());

        Mage::getSingleton('customer/session')->loginById($customer->getId());

        return $customer;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getLastOrderId()
    {
        /*
        $customerSession = Mage::getSingleton('customer/session');
        if (!$customerSession->isLoggedIn()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addAttributeSelect('self/real_order_id')
            ->addAttributeFilter('self/customer_id', $customerSession->getCustomerId())
            ->setOrder('self/created_at', 'DESC')
            ->setPageSize(1)
            ->loadData();
        foreach ($collection as $order) {
            $orderId = $order->getRealOrderId();
        }
        */
        $order = Mage::getModel('sales/order');
        $order->load($this->getCheckout()->getLastOrderId());
        $orderId = $order->getIncrementId();
        return $orderId;
    }
}
