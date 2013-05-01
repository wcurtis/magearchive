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
 * Paypal shortcut link
 *
 * @category   Mage
 * @package    Mage_Paypal
 */
class Mage_PaypalUk_Block_Link_Shortcut extends Mage_Core_Block_Text_List_Link
{
    public function getAParams()
    {
        return array(
            'href'=>$this->getUrl('paypaluk/express/shortcut', array('_secure'=>true))
        );
    }

    public function getInnerText()
    {
        $locale = Mage::app()->getLocale()->getLocaleCode();
        if (strpos('en_GB', $locale)===false) {
            $locale = 'en_US';
        }
        return '<img src="https://www.paypal.com/'.$locale.'/i/btn/btn_xpressCheckout.gif" alt="'.Mage::helper('paypalUk')->__('Paypal UK Checkout').'"/>';
    }

    public function _beforeToHtml()
    {
        return (bool)Mage::getStoreConfig('payment/paypaluk_express/active');
    }
}