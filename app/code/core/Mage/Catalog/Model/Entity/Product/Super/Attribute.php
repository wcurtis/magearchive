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
 * Catalog super product attribute resource model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Entity_Product_Super_Attribute extends Mage_Core_Model_Mysql4_Abstract
{

    protected function _construct()
    {
        $this->_init('catalog/product_super_attribute', 'product_super_attribute_id');
    }

    protected  function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('product_super_attribute_label'))
            ->where($this->getIdFieldName() . ' = ? ', $object->getId())
            ->where('store_id = ?', (int) $this->getStoreId());

        $data = $read->fetchRow($select);
        if (!$data) {
            return $this;
        }

        $object->setStoreId($data['store_id'])
            ->setLabel($data['value']);

        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $select =  $this->_getWriteAdapter()->select()
            ->from($this->getTable('product_super_attribute_label'), 'value_id')
            ->where($this->getIdFieldName() . ' = ? ', $object->getId())
            ->where('store_id = ?', (int) $object->getStoreId());

        $valueId = $this->_getWriteAdapter()->fetchOne($select);

        $data = array();
        $data['store_id']              = $object->getStoreId();
        $data[$this->getIdFieldName()] = $object->getId();
        $data['value']                 = $object->getLabel();

        if($valueId) {
            $this->_getWriteAdapter()->update($this->getTable('product_super_attribute_label'),
                $data,
                'value_id = '.(int) $valueId);
        } else {
            $this->_getWriteAdapter()
                ->insert($this->getTable('product_super_attribute_label'), $data);
        }

        $valuePricing = $object->getValues();

        if(!is_array($valuePricing)) {
            $valuePricing = array();
        }

        $ignoreDeleteIds = array();

        foreach ($valuePricing as $value) {
            $pricing = Mage::getModel('catalog/product_super_attribute_pricing')
                ->setData($value)
                ->setId(isset($value['id']) ? $value['id'] : null)
                ->setData($this->getIdFieldName(), $object->getId())
                ->save();

            $ignoreDeleteIds[] = $pricing->getId();
        }

        $deleteCondition = $this->_getWriteAdapter()->quoteInto($this->getIdFieldName().' = ?',
                                                                    $object->getId());

        if(sizeof($ignoreDeleteIds)>0) {
            $deleteCondition.= ' AND '.$this->_getWriteAdapter()->quoteInto('value_id NOT IN(?)', $ignoreDeleteIds);
        }

        $this->_getWriteAdapter()
            ->delete($this->getTable('product_super_attribute_pricing'), $deleteCondition);

        return $this;
    }

    public function getPricingCollection($superAttribute)
    {
        $collection = Mage::getResourceModel('catalog/product_super_attribute_pricing_collection')
            ->addFieldToFilter($this->getIdFieldName(), $superAttribute->getId());

        return $collection;
    }

}
// Class Mage_Catalog_Model_Entity_Product_Super_Attribute`END