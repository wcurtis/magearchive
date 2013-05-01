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
 * @package    Mage_Eav
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Eav_Model_Mysql4_Entity_Attribute_Group extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('eav/attribute_group', 'attribute_group_id');
    }

    public function itemExists($object)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()->from($this->getMainTable())
            ->where("attribute_group_name='{$object->getAttributeGroupName()}'");
        $data = $read->fetchRow($select);
        if (!$data) {
            return false;
        }
        return true;
    }

    public function save(Mage_Core_Model_Abstract $object) {
        $write = $this->_getWriteAdapter();
        $groupId = $object->getId();

        $data = array(
            'attribute_set_id' => $object->getAttributeSetId(),
            'attribute_group_name' => $object->getAttributeGroupName(),
            'sort_order' => ( $object->getSortOrder() > 0 ) ? $object->getSortOrder() : ($this->_getMaxSortOrder($object) + 1)
        );

        try {
            if( $groupId > 0 ) {
                $condition = $write->quoteInto("{$this->getMainTable()}.{$this->getIdFieldName()} = ?", $groupId);
                $write->update($this->getMainTable(), $data, $condition);
            } else {
                $write->insert($this->getMainTable(), $data);
                $object->setId($write->lastInsertId());
            }
            if( $object->getAttributes() ) {
                $insertId = $write->lastInsertId();
                foreach( $object->getAttributes() as $attribute ) {
                    if( $insertId > 0 ) {
                        $attribute->setAttributeGroupId($insertId);
                    }
                    $attribute->save();
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $this;
    }

    public function delete(Mage_Core_Model_Abstract $object)
    {
        $groups = $object->getGroupsArray();
        $setId = $object->getSetId();
        $write = $this->_getWriteAdapter();

        $condition = $write->quoteInto("{$this->getTable('entity_attribute')}.attribute_group_id = ?", $object->getId());
        $write->delete($this->getTable('entity_attribute'),  $condition);

        $condition = $write->quoteInto('attribute_group_id = ?', $object->getId());
        $write->delete($this->getMainTable(), $condition);
    }

    private function _getMaxSortOrder($object)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getMainTable(), new Zend_Db_Expr("MAX(`sort_order`)"))
            ->where("{$this->getMainTable()}.attribute_set_id = ?", $object->getAttributeSetId());
        $maxOrder = $read->fetchOne($select);
        return $maxOrder;
    }
}