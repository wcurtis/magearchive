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
 * Catalog product tier price backend attribute model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Product_Attribute_Backend_Tierprice extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    public function setAttribute($attribute)
    {
        parent::setAttribute($attribute);
        //$this->setScope($attribute);
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
    /**
     * Retrieve resource model
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Backend_Tierprice
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('catalog/product_attribute_backend_tierprice');
    }

    /**
     * Validate data
     *
     * @param   Mage_Catalog_Model_Product $object
     * @return  this
     */
    public function validate($object)
    {
        $tiers = $object->getData($this->getAttribute()->getName());
        if (empty($tiers)) {
            return $this;
        }
        $dup = array();
        foreach ($tiers as $tier) {
            if (!empty($tier['delete'])) {
                continue;
            }
            $key = $tier['cust_group'].'-'.$tier['price_qty'];
            if (!empty($dup[$key])) {
                Mage::throwException(
                    Mage::helper('catalog')->__('Duplicate tier price customer group and quantity.')
                );
            }
            $dup[$key] = 1;
        }
        return $this;
    }

    public function afterLoad($object)
    {
        $data = $this->_getResource()->loadProductPrices($object);

        foreach ($data as $i=>$row) {
            if (!empty($row['all_groups'])) {
                $data[$i]['cust_group'] = Mage_Customer_Model_Group::CUST_GROUP_ALL;
            }
        }
        $object->setData($this->getAttribute()->getName(), $data);
    }

    public function afterSave($object)
    {
        $this->_getResource()->deleteProductPrices($object);
        $tierPrices = $object->getData($this->getAttribute()->getName());

        if (!is_array($tierPrices)) {
            return $this;
        }
        //$minimalPrice = $object->getPrice();

        foreach ($tierPrices as $tierPrice) {
            if (empty($tierPrice['price_qty'])
                || !isset($tierPrice['price'])
                || !empty($tierPrice['delete'])) {
                continue;
            }

            $useForAllGroups = $tierPrice['cust_group'] == Mage_Customer_Model_Group::CUST_GROUP_ALL;

            $data = array();
            $data['all_groups']        = $useForAllGroups;
            $data['customer_group_id'] = !$useForAllGroups ? $tierPrice['cust_group'] : 0;
            $data['qty']               = $tierPrice['price_qty'];
            $data['value']             = $tierPrice['price'];

/*            if ($tierPrice['price']<$minimalPrice) {
                $minimalPrice = $tierPrice['price'];
            }*/

            $this->_getResource()->insertProductPrice($object, $data);
        }


//        $this->_spreadPrices($object);

        return $this;
        /*$object->setMinimalPrice($minimalPrice);
        $this->getAttribute()->getEntity()->saveAttribute($object, 'minimal_price');*/

    }

    protected function _spreadPrices($object)
    {
        if ($object->getStoreId() == 0) {
            $scope = (int) Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);
            $baseCurrency = Mage::app()->getBaseCurrencyCode();

            if ($scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE) {
                $oldValue = $object->getData($this->getAttribute()->getAttributeCode());
                $storeIds = $object->getStoreIds();

                if (is_array($storeIds)) {
                    foreach ($storeIds as $storeId) {
                        $storeCurrency = Mage::app()->getStore($storeId)->getBaseCurrencyCode();
                        $rate = Mage::getModel('directory/currency')->load($storeCurrency)->getRate($baseCurrency);

                        $newValue = array();
                        foreach ($oldValue as $tier) {
                            if (empty($tier['price_qty'])
                                || !isset($tier['price'])
                                || !empty($tier['delete'])) {
                                continue;
                            }

                            $tier['price'] = $tier['price'] * $rate;
                            $newValue[] = $tier;
                        }
                        $object->addAttributeUpdate($this->getAttribute()->getAttributeCode(), $newValue, $storeId);
                    }
                }
            }
        }
    }

    public function afterDelete($object)
    {
        $this->_getResource()->deleteProductPrices($object);
        return $this;
    }
}