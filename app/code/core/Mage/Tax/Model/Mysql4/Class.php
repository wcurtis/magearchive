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
 * Tax class resource
 *
 * @category   Mage
 * @package    Mage_Tax
 */

class Mage_Tax_Model_Mysql4_Class
{

    /**
     * resource tables
     */
    protected $_classTable;

    protected $_classGroupTable;

    /**
     * resources
     */
    protected $_write;

    protected $_read;


    public function __construct()
    {
        $this->_classTable = Mage::getSingleton('core/resource')->getTableName('tax/tax_class');
        $this->_classGroupTable = Mage::getSingleton('core/resource')->getTableName('tax/tax_class_group');

        $this->_read = Mage::getSingleton('core/resource')->getConnection('tax_read');
        $this->_write = Mage::getSingleton('core/resource')->getConnection('tax_write');
    }

    public function getIdFieldName()
    {
        return 'class_id';
    }

    public function load($model, $classId)
    {
        $select = $this->_read->select();
        $select->from($this->_classTable);
        $select->where("{$this->_classTable}.class_id = ?", $classId);
        $data = $this->_read->fetchRow($select);

        $model->setData($data);
    }

    public function save($classObject)
    {
        if( !is_null($classObject->getClassId()) ) {
			$classArray = array(
				'class_name' => $classObject->getClassName(),
				'class_type' => $classObject->getClassType()
			);
			$condition = $this->_write->quoteInto("{$this->_classTable}.class_id = ?", $classObject->getClassId());
            $this->_write->update($this->_classTable, $classArray, $condition);
            return $classObject->getClassId();
        } else {
			$classArray = array(
				'class_name' => $classObject->getClassName(),
				'class_type' => $classObject->getClassType()
			);

			$this->_write->insert($this->_classTable, $classArray);
			$classId = $this->_write->lastInsertId();

			$classObject->setClassId($classId);
        }
    }

    public function delete($classObject)
    {
        $condition = $this->_write->quoteInto("{$this->_classTable}.class_id = ?", $classObject->getClassId());
        $this->_write->delete($this->_classTable, $condition);
    }

    public function saveGroup($groupObject)
    {
        $groupArray = array(
            'class_parent_id' => $groupObject->getClassParentId(),
            'class_group_id' => $groupObject->getClassGroupId()
        );

        $this->_write->insert($this->_classGroupTable, $groupArray);
    }

    public function deleteGroup($groupId)
    {
        $condition = $this->_write->quoteInto("{$this->_classGroupTable}.group_id = ?", $groupId);
        $this->_write->delete($this->_classGroupTable, $condition);
    }

    public function itemExists($classObject)
    {
        $select = $this->_read->select();
        $select->from($this->_classTable);
        $select->where("{$this->_classTable}.class_name = '{$classObject->getClassName()}' AND {$this->_classTable}.class_type = '{$classObject->getClassType()}' AND {$this->_classTable}.class_id != '{$classObject->getClassId()}'");
        $data = $this->_read->fetchRow($select);
        return ( intval($data['class_id']) > 0 ) ? true : false;
    }
}