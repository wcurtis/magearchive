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
 * @package    Mage_Admin
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * ACL user resource
 *
 * @category   Mage
 * @package    Mage_Admin
 */
class Mage_Admin_Model_Mysql4_User
{
    protected $_userTable;
    protected $_read;
    protected $_write;

    /**
     * Zend auth adapter
     *
     * @var Zend_Auth_Adapter_Interface
     */
    protected $_authAdapter = null;
    
    public function __construct() 
    {
        $this->_userTable = Mage::getSingleton('core/resource')->getTableName('admin/user');
        $this->_read = Mage::getSingleton('core/resource')->getConnection('admin_read');
        $this->_write = Mage::getSingleton('core/resource')->getConnection('admin_write');
    }
    
    public function getAuthAdapter()
    {
        return new Zend_Auth_Adapter_DbTable($this->_read, $this->_userTable, 'username', 'password', 'md5(?)');
    }

    /**
     * Authenticate user by $username and $password
     *
     * @param string $username
     * @param string $password
     * @return boolean|Object
     */
    public function recordLogin(Mage_Admin_Model_User $user)
    {
        $data = array(
            'logdate' => now(),
            'lognum'  => $user->getLognum()+1
        );
        $condition = $this->_write->quoteInto('user_id=?', $user->getUserId());
        $this->_write->update($this->_userTable, $data, $condition);
        return $this;
    }
    
    public function load($userId)
    {
        $select = $this->_read->select()->from($this->_userTable)
            ->where("user_id=?", $userId);
        return $this->_read->fetchRow($select);
    }
    
    public function loadByUsername($username)
    {
        $select = $this->_read->select()->from($this->_userTable)
            ->where("username=?", $username);
        return $this->_read->fetchRow($select);
    }

    public function save(Mage_Admin_Model_User $user)
    {
        $this->_write->beginTransaction();

        try {
            $data = array(
                'firstname' => $user->getFirstname(),
                'lastname'  => $user->getLastname(),
                'email'     => $user->getEmail(),
                'username'  => $user->getUsername(),
                'modified'  => now()
            );

            if ( !is_null($user->getReloadAclFlag()) ) {
                $data['reload_acl_flag'] = $user->getReloadAclFlag();
            }
            if ($user->getPassword()) {
                $data['password'] = $this->_encryptPassword($user->getPassword());
            }
         
            if ($user->getId()) {
                $condition = $this->_write->quoteInto('user_id=?', $user->getId());
                $this->_write->update($this->_userTable, $data, $condition);
            } else { 
                $data['created'] = now();
                $this->_write->insert($this->_userTable, $data);
                $user->setUserId($this->_write->lastInsertId());
            }

            $this->_write->commit();
        }
        catch (Exception $e)
        {
            $this->_write->rollback();
            throw $e;
        }
        
        return $user;
    }
    
    public function delete()
    {
            
    }

    public function hasAssigned2Role($userId)
    {
        if ( $userId > 0 ) {
    		$dbh          = $this->_read;
    		$roleTable    = Mage::getSingleton('core/resource')->getTableName('admin/role');
    		$select = $dbh->select();
        	$select->from($roleTable)
        		->where("parent_id > 0 AND user_id = {$userId}");
        	return $dbh->fetchAll($select);
    	} else {
    		return null;
    	}
    }

    private function _encryptPassword($pwStr)
    {
        return md5($pwStr);
    }

}
