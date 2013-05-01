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

class Mage_Admin_Model_Permissions_User extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('admin/permissions_user');
    }

    public function save() {

        $data = array(
            'firstname' => $this->getFirstname(),
            'lastname'  => $this->getLastname(),
            'email'     => $this->getEmail(),
        );

        if ( $this->getId() > 0 ) {
            $data['user_id']    = $this->getId();
        }
        if( $this->getUsername() ) {
            $data['username']   = $this->getUsername();
        }
        if ($this->getPassword()) {
            $data['password']   = $this->_getEncodedPassword($this->getPassword());
        }

        if ($this->getNewPassword()) {
            $data['password']   = $this->_getEncodedPassword($this->getNewPassword());
        }

        if ( !is_null($this->getIsActive()) ) {
            $data['is_active']  = intval($this->getIsActive());
        }
        
        $this->setData($data);
        $this->_getResource()->save($this);
        return $this;
    }

    public function delete()
    {
        $this->_getResource()->delete($this);
        return $this;
    }

    public function saveRelations()
    {
        $this->_getResource()->_saveRelations($this);
        return $this;
    }

    public function getRoles()
    {
        return $this->_getResource()->_getRoles($this);
    }

    public function deleteFromRole()
    {
        $this->_getResource()->deleteFromRole($this);
        return $this;
    }

    public function roleUserExists()
    {
        $result = $this->_getResource()->roleUserExists($this);
        return ( is_array($result) && count($result) > 0 ) ? true : false;
    }

    public function add()
    {
        $this->_getResource()->add($this);
        return $this;
    }
    
    public function userExists()
    {
        $result = $this->_getResource()->userExists($this);
        return ( is_array($result) && count($result) > 0 ) ? true : false;
    }

    public function getCollection() {
        return Mage::getResourceModel('admin/permissions_user_collection');
    }
    
    # Protected methods
    protected function _getEncodedPassword($pwd)
    {
        return md5($pwd);
    }

}