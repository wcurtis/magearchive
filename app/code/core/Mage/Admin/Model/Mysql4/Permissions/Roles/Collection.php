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
 * @package    Mage_Permissions
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Admin_Model_Mysql4_Permissions_Roles_Collection extends Varien_Data_Collection_Db
{
	protected $_usersTable;
	protected $_roleTable;
	protected $_ruleTable;

    public function __construct()
    {
        $resources = Mage::getSingleton('core/resource');

        parent::__construct($resources->getConnection('tag_read'));

        $this->_usersTable        = $resources->getTableName('admin/user');
        $this->_roleTable         = $resources->getTableName('admin/role');
        $this->_ruleTable         = $resources->getTableName('admin/rule');

        $this->_sqlSelect->from($this->_roleTable, '*');
        $this->_sqlSelect->where("{$this->_roleTable}.role_type='G'");
        $this->_setIdFieldName('role_id');
    }

    public function toOptionArray()
    {
	   return $this->_toOptionArray('role_id', 'role_name');
    }
}
?>
