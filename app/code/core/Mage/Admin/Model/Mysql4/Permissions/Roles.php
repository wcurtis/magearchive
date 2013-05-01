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

class Mage_Admin_Model_Mysql4_Permissions_Roles {
    protected $_usersTable;
    protected $_roleTable;
    protected $_ruleTable;

    /**
     * Read connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;

    /**
     * Write connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_write;

    public function __construct() {
        $resources = Mage::getSingleton('core/resource');

        $this->_usersTable        = $resources->getTableName('admin/user');
        $this->_roleTable         = $resources->getTableName('admin/role');
        $this->_ruleTable         = $resources->getTableName('admin/rule');

        $this->_read    = $resources->getConnection('admin_read');
        $this->_write   = $resources->getConnection('admin_write');
    }

    public function load($roleId) {
        if ($roleId) {
            $row = $this->_read->fetchRow("SELECT * FROM {$this->_roleTable} WHERE role_id = {$roleId}");
            return $row;
        } else {
            return array();
        }
    }

    public function save(Mage_Admin_Model_Permissions_Roles $role) {
        if ($role->getPid() > 0) {
            $row = $this->load($role->getPid());
        } else {
            $row = array('tree_level' => 0);
        }

        if ($role->getId()) {
            $this->_write->update($this->_roleTable, array('parent_id' => $role->getPid(),
                                                           'tree_level' => $row['tree_level'] + 1,
                                                           'role_name' => $role->getName(),

                                                           ), "role_id = {$role->getId()}");
        } else {
            $this->_write->insert($this->_roleTable, array('parent_id' => $role->getPid(),
                                                           'tree_level' => $row['tree_level'] + 1,
                                                           'role_name' => $role->getName(),
                                                           'role_type' => $role->getRoleType(),
                                                           ));
            $role->setId($this->_write->lastInsertId());
        }
        $this->_updateRoleUsersAcl($role);
        return $role->getId();
    }

    public function delete(Mage_Admin_Model_Permissions_Roles $role) {
        $this->_write->beginTransaction();

        try {
            $this->_write->delete($this->_roleTable, "role_id={$role->getId()}");
            $this->_write->delete($this->_roleTable, "parent_id={$role->getId()}");
            $this->_write->delete($this->_ruleTable, "role_id={$role->getId()}");
            $this->_write->commit();
        } catch (Mage_Core_Exception $e) {
            throw $e;
        } catch (Exception $e){
            $this->_write->rollBack();
        }
    }

    public function getRoleUsers(Mage_Admin_Model_Permissions_Roles $role)
    {
        $read 	= $this->_read;
        $select = $read->select()->from($this->_roleTable, array('user_id'))->where("(parent_id = {$role->getId()} AND role_type = 'U') AND user_id > 0");
        return $read->fetchCol($select);
    }

    private function _updateRoleUsersAcl(Mage_Admin_Model_Permissions_Roles $role)
    {
        $write  = $this->_write;
        $users  = $this->getRoleUsers($role);
        $rowsCount = 0;
        if ( sizeof($users) > 0 ) {
            $inStatement = implode(", ", $users);
            $rowsCount = $write->update($this->_usersTable, array('reload_acl_flag' => 1), "user_id IN({$inStatement})");
        }
        if ($rowsCount > 0) {
            return true;
        } else {
            return false;
        }
    }
}
