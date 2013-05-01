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
 * Catalog indexer abstract class
 *
 */
class Mage_CatalogIndex_Model_Indexer_Abstract extends Mage_Core_Model_Abstract
{
    public function processAfterSave(Mage_Catalog_Model_Product $object, $forceId = null)
    {
        $associated = array();
        switch ($object->getTypeId()) {
            case 'grouped':
                $associated = $object->getTypeInstance()->getAssociatedProducts();
                break;

            case 'configurable':
                $associated = $object->getTypeInstance()->getUsedProducts();
                break;
        }

        if (!$this->_isObjectIndexable($object)) {
            return;
        }

        $data = array();
        $attributes = $object->getAttributes();
        foreach ($attributes as $attribute) {
            if ($this->_isAttributeIndexable($attribute) && $object->getData($attribute->getAttributeCode()) != null) {
                $row = $this->createIndexData($object, $attribute);
                if ($row && is_array($row)) {
                    if (isset($row[0]) && is_array($row[0])) {
                        $data = array_merge($data, $row);
                    } else {
                        $data[] = $row;
                    }
                }
            }
        }
        if ($data)
            $this->saveIndices($data, $object->getStoreId(), ($forceId != null ? $forceId : $object->getId()));

        if ($associated) {
            foreach ($associated as $child) {
                $child
                    ->setStoreId($object->getStoreId())
                    ->setWebsiteId($object->getWebsiteId());
                $this->processAfterSave($child, $object->getId());
            }
        }
    }

    public function saveIndex($data, $storeId, $productId)
    {
        $this->_getResource()->saveIndex($data, $storeId, $productId);
    }

    public function saveIndices(array $data, $storeId, $productId)
    {
        $this->_getResource()->saveIndices($data, $storeId, $productId);
    }

    protected function _isObjectIndexable(Mage_Catalog_Model_Product $object)
    {
        if ($object->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            return false;
        }

        if ($object->getVisibility() != Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG &&
            $object->getVisibility() != Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) {
            return false;
        }

        return true;
    }

    public function isAttributeIndexable(Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        return $this->_isAttributeIndexable($attribute);
    }

    protected function _isAttributeIndexable(Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        return true;
    }
/*
    protected function _spreadDataForStores(Mage_Catalog_Model_Product $object, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, array $data, $websiteId = null) {
        return $data;

        $stores = false;

        if (!$websiteId) {
            if ($attribute->isScopeWebsite() || $attribute->isScopeGlobal()) {
                $stores = $object->getStoreIds();
            }
        } else {
            $stores = Mage::app()->getWebsite($websiteId)->getStoreIds();
        }

        if (is_array($stores)) {
            $result = array();
            foreach ($stores as $store) {
                $data['store_id'] = $store;
                $result[] = $data;
            }
        } else {
            $result = $data;
        }

        return $result;
    }
*/
    public function getIndexableAttributeCodes()
    {
        return $this->_getResource()->loadAttributeCodesByCondition($this->_getIndexableAttributeConditions());
    }

    protected function _getIndexableAttributeConditions()
    {
        return array();
    }

    public function cleanup($productId, $storeId)
    {
        $this->_getResource()->cleanup($productId, $storeId);
    }
}