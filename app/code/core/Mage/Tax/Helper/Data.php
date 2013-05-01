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
 * @package    Mage_Tax
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog data helper
 */
class Mage_Tax_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_taxData;
    protected $_showInCatalog;

    public function getProductPrice($product, $format=null)
    {
        try {
            $value = $product->getPrice();
            $value = Mage::app()->getStore()->convertPrice($value, $format);
        }
        catch (Exception $e){
            $value = $e->getMessage();
        }
    	return $value;
    }

    public function showInCatalog($store=null)
    {
        $storeId = Mage::app()->getStore($store)->getId();
        if (!isset($this->_showInCatalog[$storeId])) {
            $this->_showInCatalog[$storeId] =
                (int)Mage::getStoreConfig('sales/tax/show_in_catalog', $store)
                && Mage::getStoreConfig('sales/tax/based_on', $store)==='origin';
        }
        return $this->_showInCatalog[$storeId];
    }

    public function getTaxData($store=null)
    {
        $storeId = Mage::app()->getStore($store)->getId();
        if (!isset($this->_taxData[$storeId])) {
            $this->_taxData[$storeId] = Mage::getModel('tax/rate_data')
                ->setCustomerClassId(Mage::getSingleton('customer/session')->getCustomer()->getTaxClassId())
                ->setCountryId(Mage::getStoreConfig('shipping/origin/country_id', $store))
                ->setRegionId(Mage::getStoreConfig('shipping/origin/region_id', $store))
                ->setPostcode(Mage::getStoreConfig('shipping/origin/postcode', $store));
        }
        return $this->_taxData[$storeId];
    }

    public function updateProductTax($product)
    {
        $store = Mage::app()->getStore($product->getStoreId());
        if (!$this->showInCatalog($store)) {
            return false;
        }
        $this->getTaxData()->setProductClassId($product->getTaxClassId());
        $taxRatio = $this->getTaxData($store)->getRate()/100;

        $product->setPriceAfterTax($store->roundPrice($product->getPrice()*(1+$taxRatio)));
        $product->setFinalPriceAfterTax($store->roundPrice($product->getFinalPrice()*(1+$taxRatio)));
        $product->setShowTaxInCatalog(Mage::getStoreConfig('sales/tax/show_in_catalog', $store));

        return $taxRatio;
    }
}
