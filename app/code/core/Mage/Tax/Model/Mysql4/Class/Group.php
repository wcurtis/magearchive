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
 * Tax class group resource
 *
 * @category   Mage
 * @package    Mage_Tax
 */

class Mage_Tax_Model_Mysql4_Class_Group
{

    /**
     * resource tables
     */
    protected $_classGroupTable;

    /**
     * resources
     */
    protected $_write;

    protected $_read;

    public function __construct()
    {
        $this->_classGroupTable = Mage::getSingleton('core/resource')->getTableName('tax/tax_class_group');

        $this->_read = Mage::getSingleton('core/resource')->getConnection('tax_read');
        $this->_write = Mage::getSingleton('core/resource')->getConnection('tax_write');
    }

    public function getIdFieldName()
    {
        return 'class_id';
    }

    public function save($groupObject)
    {
        $groupArray = array(
            'class_parent_id' => $groupObject->getClassParentId(),
            'class_group_id' => $groupObject->getClassGroup()
        );

        $this->_write->insert($this->_classGroupTable, $groupArray);
    }

    public function delete($groupId)
    {
        $condition = $this->_write->quoteInto("{$this->_classGroupTable}.group_id = ?", $groupId);
        $this->_write->delete($this->_classGroupTable, $condition);
    }
}