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
 * ACL user model
 *
 * @category   Mage
 * @package    Mage_Admin
 */
class Mage_Admin_Model_User extends Varien_Object
{
    const XML_PATH_FORGOT_EMAIL_TEMPLATE    = 'system/emails/forgot_email_template';
    const XML_PATH_FORGOT_EMAIL_IDENTITY    = 'system/emails/forgot_email_identity';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get user id
     *
     * @return int || null
     */
    public function getId()
    {
        return $this->getUserId();
    }

    /**
     * Get user ACL role
     *
     * @return string
     */
    public function getAclRole()
    {
        return 'U'.$this->getUserId();
    }

    /**
     * Get resource model
     *
     * @return mixed
     */
    public function getResource()
    {
        return Mage::getResourceSingleton('admin/user');
    }

    /**
     * Login user
     *
     * @param   string $login
     * @param   string $password
     * @return  Mage_Admin_Model_User
     */
    public function login($username, $password)
    {
        $authAdapter = $this->getResource()->getAuthAdapter();
        $authAdapter->setIdentity($username)->setCredential($password);
        $resultCode = $authAdapter->authenticate()->getCode();

        if (Zend_Auth_Result::SUCCESS!==$resultCode) {
            return $this;
        }

        $this->addData((array)$authAdapter->getResultRowObject());

        $this->getResource()->recordLogin($this);

        return $this;
    }

    public function reload()
    {
        $this->load($this->getId());
        return $this;
    }

    /**
     * Load user data by user id
     *
     * @param   int $userId
     * @return  Mage_Admin_Model_User
     */
    public function load($userId)
    {
        $this->setData($this->getResource()->load($userId));
        return $this;
    }

    public function loadByUsername($username)
    {
        $this->setData($this->getResource()->loadByUsername($username));
        return $this;
    }

    /**
     * Save user data
     *
     * @return Mage_Admin_Model_User
     */
    public function save()
    {
        $this->getResource()->save($this);
        return $this;
    }

    /**
     * Delete user
     *
     * @return Mage_Admin_Model_User
     */
    public function delete()
    {
        $this->getResource()->delete($this);
        return $this;
    }

    public function getName($separator=' ')
    {
        return $this->getFirstname().$separator.$this->getLastname();
    }

    public function hasAssigned2Role($userId)
    {
        return $this->getResource()->hasAssigned2Role($userId);
    }

    /**
     * Send email with new user password
     *
     * @return Mage_Admin_Model_User
     */
    public function sendNewPasswordEmail()
    {
        Mage::getModel('core/email_template')
            ->setDesignConfig(array('area'=>'adminhtml', 'store'=>$this->getStoreId()))
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_FORGOT_EMAIL_TEMPLATE),
                Mage::getStoreConfig(self::XML_PATH_FORGOT_EMAIL_IDENTITY),
                $this->getEmail(),
                $this->getName(),
                array('user'=>$this));
        return $this;
    }
    
}
