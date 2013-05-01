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


class Mage_Eav_Model_Mysql4_Config extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('eav/entity_type', 'entity_type_id');
    }

    public function fetchCacheData()
    {
        $data = array();
        $entityTypes = $this->_getReadAdapter()->fetchAll('select * from '.$this->getMainTable());
        foreach ($entityTypes as $row) {
            $data['entity_type'][$row['entity_type_id']] = $row;
            $data['entity_type'][$row['entity_type_code']] = $row['entity_type_id'];
        }

        $attributes = $this->_getReadAdapter()->fetchAll('select * from '.$this->getTable('eav/attribute'));
        foreach ($attributes as $row) {
            $data['attribute'][$row['attribute_id']] = $row;
            $data['attribute'][$row['entity_type_id'].'/'.$row['attribute_code']] = $row['attribute_id'];
        }

        return $data;
    }

    public function fetchEntityTypeData($entityType)
    {
        $read = $this->_getReadAdapter();
        if (!$read) {
            return false;
        }
        $select = $read->select()->from($this->getMainTable());
        if (is_numeric($entityType)) {
            $select->where('entity_type_id=?', $entityType);
        } else {
            $select->where('entity_type_code=?', $entityType);
        }
        $row = $read->fetchRow($select);
        if (!$row) {
            return false;
        }

        $data = array();
        $data['entity_type'] = $row;

        $select = $read->select()->from($this->getTable('eav/attribute'))
            ->where('entity_type_id=?', $data['entity_type']['entity_type_id']);
        $attributes = $read->fetchAll($select);
        foreach ($attributes as $row) {
            $data['attribute'][$row['attribute_id']] = $row;
            $data['attribute'][$row['attribute_code']] = $row['attribute_id'];
        }

        return $data;
    }
}
