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
 * @package    Mage_Paypal
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Express Checkout Controller
 *
 */
class Mage_Paypal_ExpressController extends Mage_Core_Controller_Front_Action
{
    protected function _expireAjax()
    {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    /**
     * When there's an API error
     *
     */
    public function errorAction()
    {
        $this->_redirect('checkout/cart');
    }

    public function cancelAction()
    {
        $this->_redirect('checkout/cart');
    }

    /**
     * Get singleton with paypal express order transaction information
     *
     * @return Mage_Paypal_Model_Express
     */
    public function getExpress()
    {
        return Mage::getSingleton('paypal/express');
    }

    /**
     * When a customer clicks Paypal button on shopping cart
     *
     */
    public function shortcutAction()
    {
        $this->getExpress()->shortcutSetExpressCheckout();
        $this->getResponse()->setRedirect($this->getExpress()->getRedirectUrl());
    }

    /**
     * When a customer chooses Paypal on Checkout/Payment page
     *
     */
    public function markAction()
    {
        $this->getExpress()->markSetExpressCheckout();
        $this->getResponse()->setRedirect($this->getExpress()->getRedirectUrl());
    }

    public function editAction()
    {
        $this->getResponse()->setRedirect($this->getExpress()->getApi()->getPaypalUrl());
    }

    /**
     * Return here from Paypal before final payment (continue)
     *
     */
    public function returnAction()
    {
        $this->getExpress()->returnFromPaypal();
        $this->getResponse()->setRedirect($this->getExpress()->getRedirectUrl());
    }

    /**
     * Return here from Paypal after final payment (commit) or after on-site order review
     *
     */
    public function reviewAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('paypal/session');
        $this->renderLayout();
    }

    /**
     * Get PayPal Onepage checkout model
     *
     * @return Mage_Paypal_Model_Express_Onepage
     */
    public function getReview()
    {
        return Mage::getSingleton('paypal/express_review');
    }

    public function saveShippingMethodAction()
    {
        if ($this->getRequest()->getParam('ajax')) {
            $this->_expireAjax();
        }

        if (!$this->getRequest()->isPost()) {
            return;
        }

        $data = $this->getRequest()->getParam('shipping_method', '');
        $result = $this->getReview()->saveShippingMethod($data);

        if ($this->getRequest()->getParam('ajax')) {
            $this->loadLayout('paypal_express_review_details');
            $this->getResponse()->setBody($this->getLayout()->getBlock('root')->toHtml());
        } else {
            $this->_redirect('paypal/express/review');
        }
    }

    public function saveOrderAction()
    {
        try {
            $this->getExpress()->placeOrder($this->getReview()->getQuote()->getPayment());
        } catch (Exception $e) {
            Mage::getSingleton('paypal/session')->addError($e->getMessage());
            $this->_redirect('paypal/express/review');
            return;
        }

        $address = $this->getReview()->getQuote()->getShippingAddress();
        if (!$address->getShippingMethod()) {
            if ($shippingMethod = $this->getRequest()->getParam('shipping_method')) {
                $this->getReview()->saveShippingMethod($shippingMethod);
            } else {
                Mage::getSingleton('paypal/session')->addError(Mage::helper('paypal')->__('Please select a valid shipping method'));
                $this->_redirect('paypal/express/review');
                return;
            }
        }

        $result = $this->getReview()->saveOrder();
#echo "<pre>".print_r($result,1)."</pre>";
        if (!empty($result['success'])) {
            $this->_redirect('checkout/onepage/success');
        } else {
            if (empty($result['error_messages'])) {
                $result['error_messages'][] = Mage::helper('paypal')->__('Unknown error during order save.');
            }
            foreach ($result['error_messages'] as $error) {
                Mage::getSingleton('paypal/session')->addError($error);
            }
            $this->_redirect('paypal/express/review');
        }
    }
}