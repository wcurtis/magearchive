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
 * Reindexer resource model
 *
 */
class Mage_CatalogIndex_Model_Mysql4_Indexer extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_attributeCache = array();

    protected function _construct()
    {
        $this->_init('catalog/product', 'entity_id');
    }

    protected function _filterEntityIds($attributeConditions, $store)
    {
        $select = $this->_getReadAdapter()->select();
        $select
            ->from(array('v'=>"{$this->getTable('catalog/product')}_int"), array('entity_id'))
            ->joinLeft(
                    array("a"=>$this->getTable('eav/attribute')),
                    "a.attribute_id = v.attribute_id",
                    array()
                    )
            ->joinLeft(
                    array('d'=>"{$this->getTable('catalog/product')}_int"),
                    "d.attribute_id = v.attribute_id AND d.store_id = 0",
                    array()
                    );

        foreach ($attributeConditions as $attributeCode=>$attributeValue) {
            if (is_array($attributeValue)) {
                $conditionPart = "IFNULL(v.value, d.value) in (?)";
            } else {
                $conditionPart = "IFNULL(v.value, d.value) = ?";
            }

            $conditionSql = "(a.attribute_code = '{$attributeCode}' AND {$conditionPart})";
            $select->orWhere($conditionSql, $attributeValue);
        }
        $select->where('v.store_id = ?', $store->getId());

        return $select;
    }

    public function getProducts($storeId)
    {
        return $this->_filterEntityIds($conditions, $storeId);
    }

    public function getProductData($productConditions, $attributeCodes, $store){
        $filterSql = $this->_filterEntityIds($productConditions, $store);
        $select = $this->_getReadAdapter()->select();
        $select
            ->from(array('product'=>$this->getTable('catalog/product')), array('entity_id'))
            ->where("product.entity_id in (?)", new Zend_Db_Expr($filterSql))
            ->group('entity_id');

        foreach ($attributeCodes as $code) {
            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $code);
            $this->_attributeCache[$code] = $attribute;

            if ($attribute->getId()) {
                $table = $attribute->getBackend()->getTable();

                $joinCondition = "_{$code}.entity_id = product.entity_id AND _{$code}.attribute_id = '{$attribute->getId()}' AND _{$code}.store_id = '{$store->getId()}'";
                $defaultJoinCondition = "_{$code}_default.entity_id = product.entity_id AND _{$code}_default.attribute_id = '{$attribute->getId()}' AND _{$code}_default.store_id = 0";

                $field = new Zend_Db_Expr("IFNULL(_{$code}.value, _{$code}_default.value)");

                $select->joinLeft(array("_{$code}"=>$table),
                    $joinCondition,
                    array($code=>$field));

                $select->joinLeft(array("_{$code}_default"=>$table),
                    $defaultJoinCondition,
                    array());

            }
        }

        $result = $this->_getReadAdapter()->fetchAll($select);

        foreach ($result as $entity) {
            $entityId = $entity['entity_id'];
            unset($entity['entity_id']);
            foreach ($entity as $column=>$cell) {
                if (!is_null($cell))
                    $this->_getWriteAdapter()->insert($this->getTable('catalogindex/eav'), array('store_id'=>$store->getId(), 'entity_id'=>$entityId, 'attribute_id'=>$this->_attributeCache[$column]->getId(), 'value'=>$cell));
            }
        }

        return $result;
    }

    public function getProductIds($select)
    {
        $select->distinct(true);
        return $this->_getReadAdapter()->fetchCol($select);
    }

    public function clear()
    {
        $this->_getWriteAdapter()->delete($this->getTable('catalogindex/eav'));
        $this->_getWriteAdapter()->delete($this->getTable('catalogindex/price'));
    }
}