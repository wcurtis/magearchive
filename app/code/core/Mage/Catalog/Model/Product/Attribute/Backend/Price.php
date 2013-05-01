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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Price attribute backend model
 *
 */

class Mage_Catalog_Model_Product_Attribute_Backend_Price extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    public function afterSave($object)
    {
        $oldValue = $object->getData($this->getAttribute()->getAttributeCode());

        if ($object->getStoreId() != 0 || !$oldValue) {
            return;
        }

        $scope = (int) Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);

        if ($scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE) {
            $baseCurrency = Mage::app()->getBaseCurrencyCode();

            $storeIds = $object->getStoreIds();
            if (is_array($storeIds)) {
                foreach ($storeIds as $storeId) {
                    $storeCurrency = Mage::app()->getStore($storeId)->getBaseCurrencyCode();
                    $rate = Mage::getModel('directory/currency')->load($baseCurrency)->getRate($storeCurrency);

                    $newValue = $oldValue * $rate;
                    $object->addAttributeUpdate($this->getAttribute()->getAttributeCode(), $newValue, $storeId);
                }
            }
        }
    }

    public function setAttribute($attribute)
    {
        parent::setAttribute($attribute);
        $this->setScope($attribute);
        return $this;
    }

    public function setScope($attribute)
    {
        $priceScope = (int) Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);

        if ($priceScope == Mage_Core_Model_Store::PRICE_SCOPE_GLOBAL) {
            $attribute->setIsGlobal(Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL);
        } else {
            $attribute->setIsGlobal(Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE);
        }
    }
}