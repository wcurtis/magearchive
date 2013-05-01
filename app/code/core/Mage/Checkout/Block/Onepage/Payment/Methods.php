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

/**
 * One page checkout status
 *
 * @category   Mage
 * @category   Mage
 * @package    Mage_Checkout
 */
class Mage_Checkout_Block_Onepage_Payment_Methods extends Mage_Payment_Block_Form_Container
{
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Check and prepare payment method model
     *
     * @return bool
     */
    protected function _assignMethod($method)
    {
        if (!$method || !$method->canUseCheckout()) {
            return false;
        }

        $method->setInfoInstance($this->getQuote()->getPayment());

        // Checking for min/max order total for assigned payment method
        $gt = $this->getQuote()->getGrandTotal();
        $payment = $this->getQuote()->getStore()->getConfig('payment/'.$method->getCode());

        if( isset($payment['min_order_total']) && $payment['min_order_total'] == '' ) {
            $payment['min_order_total'] = 0.0001;
        }

        /*if( isset($payment['max_order_total']) && $payment['max_order_total'] == '' && $gt == 0 ) {
            $payment['max_order_total'] = 0.0001;
        }*/

        if( !$payment ) {
            return false;
        } elseif( isset($payment['min_order_total']) && !empty($payment['min_order_total']) && $gt < $payment['min_order_total'] ) {
            return false;
        } elseif( isset($payment['max_order_total']) && !empty($payment['max_order_total']) && $gt > $payment['max_order_total'] ) {
            return false;
        }

/*        if ($payment
            && isset($payment['min_order_total'])
            && (!empty($payment['max_order_total']) )
            && ($gt < $payment['min_order_total'] || $gt > $payment['max_order_total'])) {
            return false;
        }
*/
        return true;
    }

    /**
     * Retrieve code of current payment method
     *
     * @return mixed
     */
    public function getSelectedMethodCode()
    {
        if ($method = $this->getQuote()->getPayment()->getMethod()) {
            return $method;
        }
        return false;
    }
}
