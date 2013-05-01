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
 * Checkout default helper
 *
 */
class Mage_Checkout_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve checkout session model
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        if (empty($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }
    
    /**
     * Retrieve checkout quote model object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (empty($this->_quote)) {
            $this->_quote = $this->getCheckout()->getQuote();
        }
        return $this->_quote;
    }
    
    /**
     * Retrieve quote item product url
     *
     * @param   Mage_Sales_Model_Quote_Item $item
     * @return  string
     */
    public function getQuoteItemProductUrl($item)
    {
        if ($superProduct = $item->getSuperProduct()) {
            return $superProduct->getProductUrl();
        }
        
        if ($product = $item->getProduct()) {
            return $product->getProductUrl();
        }
        return '';
    }
    
    /**
     * Retrieve quote item product image url
     *
     * @param   Mage_Sales_Model_Quote_Item $item
     * @return  string
     */
    public function getQuoteItemProductImageUrl($item)
    {
        if ($superProduct = $item->getSuperProduct()) {
            return $superProduct->getThumbnailUrl();
        }
        
        if ($product = $item->getProduct()) {
            return $product->getThumbnailUrl();
        }
        return '';
    }
    
    /**
     * Retrieve quote item product name
     *
     * @param   Mage_Sales_Model_Quote_Item $item
     * @return  string
     */
    public function getQuoteItemProductName($item)
    {
        $superProduct = $item->getSuperProduct();
        if ($superProduct && $superProduct->isConfigurable()) {
            return $superProduct->getName();
        }
        
        if ($product = $item->getProduct()) {
            return $product->getName();
        }
        return $item->getName();
    }
    
    /**
     * Retrieve quote item product description
     *
     * @param   Mage_Sales_Model_Quote_Item $item
     * @return  string
     */
    public function getQuoteItemProductDescription($item)
    {
        if ($superProduct = $item->getSuperProduct()) {
            if ($superProduct->isConfigurable()) {
                return $this->_getConfigurableProductDescription($item->getProduct());
            }
        }
        return '';
    }
    
    /**
     * Retrieve quote item qty
     *
     * @param   Mage_Sales_Model_Quote_Item $item
     * @return  int || float
     */
    public function getQuoteItemQty($item)
    {
        $qty = $item->getQty();
        if ($product = $item->getProduct()) {
            if ($product->getQtyIsDecimal()) {
                return number_format($qty, 2, null, '');
            }
        }
        return number_format($qty, 0, null, '');
    }
    
    /**
     * Retrieve quote item product in stock flag
     *
     * @param   Mage_Sales_Model_Quote_Item $item
     * @return  bool
     */
    public function getQuoteItemProductIsInStock($item)
    {
        if ($item->getProduct()->isSaleable()) {
            if ($item->getProduct()->getQty()>=$item->getQty()) {
                return true;
            }
        }
        return false;
    }
    
    protected function _getConfigurableProductDescription($product)
    {
 		$html = '<ul class="super-product-attributes">';
 		foreach ($product->getSuperProduct()->getSuperAttributes(true) as $attribute) {
 			$html.= '<li><strong>' . $attribute->getFrontend()->getLabel() . ':</strong> ';
 			if($attribute->getSourceModel()) {
 				$html.= $this->htmlEscape(
 				   $attribute->getSource()->getOptionText($product->getData($attribute->getAttributeCode()))
                );
 			} else {
 				$html.= $this->htmlEscape($product->getData($attribute->getAttributeCode()));
 			}
 			$html.='</li>';
 		}
 		$html.='</ul>';
 		return $html;
    }
    
    public function formatPrice($price)
    {
        return $this->getQuote()->getStore()->formatPrice($price);
    }
    
    public function convertPrice($price, $format=true)
    {
        return $this->getQuote()->getStore()->convertPrice($price, $format);
    }
}
