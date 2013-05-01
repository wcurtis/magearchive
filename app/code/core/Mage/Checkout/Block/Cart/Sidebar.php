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
class Mage_Checkout_Block_Cart_Sidebar extends Mage_Checkout_Block_Cart_Abstract
{
    protected $_items;

    public function getRecentItems()
    {
        $items = array();
        if ($this->getQuote()->getItemsCount()==0) {
            return $items;
        }
        $quoteItems = $this->getQuote()->getAllItems();
        usort($quoteItems, array($this, 'sortByCreatedAt'));
        $i = 0;
        foreach ($quoteItems as $quoteItem) {
            $item = clone $quoteItem;
            $item->setItemProduct($this->helper('checkout')->getQuoteItemProduct($item));
            $item->setProductUrl($this->helper('checkout')->getQuoteItemProductUrl($item));
            $item->setProductName($this->helper('checkout')->getQuoteItemProductName($item));
            $item->setProductDescription($this->helper('catalog/product')->getProductDescription($item));
            if (Mage::helper('tax')->updateProductTax($item)) {
                $item->setPrice($item->getPriceAfterTax());
            }
            $items[] = $item;
            if (++$i==3) break;
        }
        return $items;
    }

    public function sortByCreatedAt($a, $b)
    {
        $a1 = $a->getCreatedAt();
        $b1 = $b->getCreatedAt();
        return $a1<$b1 ? 1 : $a1>$b1 ? -1 : 0;
    }

    public function getSubtotal()
    {
        return $this->getQuote()->getShippingAddress()->getSubtotal();
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
}