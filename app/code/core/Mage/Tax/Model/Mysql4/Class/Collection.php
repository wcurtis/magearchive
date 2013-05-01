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
 * Tax class collection
 *
 * @category   Mage
 * @package    Mage_Tax
 */

class Mage_Tax_Model_Mysql4_Class_Collection extends Varien_Data_Collection_Db
{
    protected $_classTable;

    function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        parent::__construct($resource->getConnection('tax_read'));
        
        $this->_setIdFieldName('class_id');
        $this->_classTable = $resource->getTableName('tax/tax_class');

        $this->_sqlSelect->from($this->_classTable);
    }

    public function toOptionArray()
    {
        return parent::_toOptionArray('class_id', 'class_name');
    }

    public function setClassTypeFilter($classType)
    {
        $this->_sqlSelect->where("{$this->_classTable}.class_type = ?", $classType);
        return $this;
    }
}