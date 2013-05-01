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
 * Wishlist sidebar block
 *
 * @category   Mage
 * @package    Mage_Checkout
 */

class Mage_Checkout_Block_Cart_Sidebar extends Mage_Core_Block_Template
{
    public function getItemCollection()
    {

        $collection = $this->getData('item_collection');
        if (is_null($collection)) {
            /**
             * Collect totals need for update quote products
             */
            #$this->getQuote()->collectTotals()
            #   ->save();
Varien_Profiler::start('TEST1: '.__METHOD__);
            $collection = Mage::getResourceModel('sales/quote_item_collection')
               ->addAttributeToSelect('*')
               ->setQuoteFilter($this->getQuote()->getId())
               ->addAttributeToSort('created_at', 'desc')
               ->setPageSize(3)
               ->load();
Varien_Profiler::stop('TEST1: '.__METHOD__);


            $this->setData('item_collection', $collection);
        }
        return $collection;
    }

    public function getSubtotal()
    {
        foreach ($this->getQuote()->getTotals() as $total) {
            if ($total->getCode()=='subtotal') {
                return Mage::helper('core')->currency($total->getValue());
            }
        }
        return false;
    }

    /**
     * Retrieve quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function getCanDisplayCart()
    {
        return true;
    }

    public function getRemoveItemUrl($item)
    {
        return $this->helper('checkout/cart')->getRemoveUrl($item);
    }

    public function getMoveToWishlistItemUrl($item)
    {
        return $this->getUrl('checkout/cart/moveToWishlist',array('id'=>$item->getId()));
    }
}// Class Mage_Wishlist_Block_Customer_Sidebar END
