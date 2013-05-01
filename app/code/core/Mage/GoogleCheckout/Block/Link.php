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
 * @package    Mage_GoogleCheckout
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Checkout shortcut link
 *
 * @category   Mage
 * @package    Mage_GoogleCheckout
 */
class Mage_GoogleCheckout_Block_Link extends Mage_Core_Block_Text
{
    protected function _construct()
    {
#echo "<pre>".print_r(debug_backtrace(),1)."</pre>";
        #$this->setIsDisabled(true);
        $this->addText($this->_getButtonFormHtml());
    }

    protected function _getImageStyle()
    {
        $s = Mage::getStoreConfig('google/checkout/checkout_image');
        if (!$s) {
            $s = '180/46/trans';
        }
        return explode('/', $s);
    }

    protected function _getImageUrl()
    {
        $url = 'https://checkout.google.com/buttons/checkout.gif';
        $url .= '?merchant_id='.Mage::getStoreConfig('google/checkout/merchant_id');
        $v = $this->_getImageStyle();
        $url .= '&w='.$v[0].'&h='.$v[1].'&style='.$v[2];
        $url .= '&variant='.($this->getIsDisabled() ? 'disabled' : 'text');
        $url .= '&loc='.Mage::getStoreConfig('google/checkout/locale');
        return $url;
    }

    public function _getButtonFormHtml()
    {
        $html = '<form method="post" action="'.$this->getUrl('googlecheckout/redirect/checkout').'" style="margin:0;padding:0;"';
        $html .= (Mage::getStoreConfig('google/analytics/active') ? ' onsubmit="setUrchinInputCode(pageTracker)"' : '').'>';
        $html .= '<input type="hidden" name="analyticsdata" value="" />';
        $v = $this->_getImageStyle();
        $html .= '<input type="image" src="'.$this->_getImageUrl().'" width="'.$v[0].'" height="'.$v[1].'"';
        $html .= ' alt="'.Mage::helper('googlecheckout')->__('Fast checkout through Google').'"/>';
        $html .= '</form>';
        return $html;
    }

    public function _beforeToHtml()
    {
        return (bool)Mage::getStoreConfig('google/checkout/active');
    }
}