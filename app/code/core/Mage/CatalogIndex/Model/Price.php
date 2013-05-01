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
 * @package    Mage_CatalogIndex
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Price index model
 *
 */
class Mage_CatalogIndex_Model_Price extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('catalogindex/price');
        $this->attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'price');
        $this->_getResource()->setStoreId(Mage::app()->getStore()->getId());
        $this->_getResource()->setRate(Mage::app()->getStore()->getCurrentCurrencyRate());
    }

    public function getMaxValue($entityIdsFilter)
    {
        return $this->_getResource()->getMaxValue($this->attribute, new Zend_Db_Expr($entityIdsFilter));
    }

    public function getCount($range, $entityIdsFilter)
    {
        return $this->_getResource()->getCount($range, $this->attribute, new Zend_Db_Expr($entityIdsFilter));
    }

    public function getFilteredEntities($range, $index, $entityIdsFilter)
    {
        return $this->_getResource()->getFilteredEntities($range, $index, $this->attribute, new Zend_Db_Expr($entityIdsFilter));
    }
}