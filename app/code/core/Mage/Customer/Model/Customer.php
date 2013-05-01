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
 * @package    Mage_Customer
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer model
 *
 */
class Mage_Customer_Model_Customer extends Mage_Core_Model_Abstract implements Mage_Core_Model_Shared_Interface
{
    const XML_PATH_REGISTER_EMAIL_TEMPLATE  = 'customer/create_account/email_template';
    const XML_PATH_REGISTER_EMAIL_IDENTITY  = 'customer/create_account/email_identity';
    const XML_PATH_FORGOT_EMAIL_TEMPLATE    = 'customer/password/forgot_email_template';
    const XML_PATH_FORGOT_EMAIL_IDENTITY    = 'customer/password/forgot_email_identity';
    const XML_PATH_DEFAULT_EMAIL_DOMAIN     = 'customer/create_account/email_domain';

    protected $_eventPrefix = 'customer';
    protected $_eventObject = 'customer';

    protected $_addressCollection;
    protected $_store;

    function _construct()
    {
        $this->_init('customer/customer');
    }

    /**
     * @todo remove public access to resource
     */
    public function getResource()
    {
        return $this->_getResource();
    }

    /**
     * Authenticate customer
     *
     * @param   string $login
     * @param   string $password
     * @return  Mage_Customer_Model_Customer || false
     */
    public function authenticate($login, $password)
    {
        if ($this->_getResource()->authenticate($this, $login, $password)) {
            return $this;
        }
        return false;
    }

    /**
     * Load customer by email
     *
     * @param   string $customerEmail
     * @return  Mage_Customer_Model_Customer
     */
    public function loadByEmail($customerEmail)
    {
        $this->_getResource()->loadByEmail($this, $customerEmail);
        return $this;
    }

    /**
     * Save customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function save()
    {
        $this->getGroupId();
        return parent::save();
    }

    /**
     * Change customer password
     * $data = array(
     *      ['password']
     *      ['confirmation']
     *      ['current_password']
     * )
     *
     * @param   array $data
     * @param   bool $checkCurrent
     * @return  this
     */
    public function changePassword($newPassword, $checkCurrent=true)
    {
        $this->_getResource()->changePassword($this, $newPassword, $checkCurrent);
        return $this;
    }

    /**
     * Get full customer name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    /**
     * Add address to address collection
     *
     * @param   Mage_Customer_Model_Address $address
     * @return  Mage_Customer_Model_Customer
     */
    public function addAddress(Mage_Customer_Model_Address $address)
    {
        $this->getAddressCollection()->addItem($address);
        return $this;
    }

    /**
     * Retrieve customer address by address id
     *
     * @param   int $addressId
     * @return  Mage_Customer_Model_Address
     */
    public function getAddressById($addressId)
    {
        return Mage::getModel('customer/address')
            ->load($addressId);
    }

    /**
     * Retrieve not loaded address collection
     *
     * @return Mage_Customer_Model_Address_Collection
     */
    public function getAddressCollection()
    {
        if (empty($this->_addressCollection)) {
            $this->_addressCollection = Mage::getResourceModel('customer/address_collection');
        }
        return $this->_addressCollection;
    }

    /**
     * Retrieve loaded customer address collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getLoadedAddressCollection()
    {
        $collection = $this->getData('loaded_address_collection');
        if (is_null($collection)) {
            $collection = Mage::getResourceModel('customer/address_collection')
                ->setCustomerFilter($this)
                ->addAttributeToSelect('*')
                ->load();
            $this->setData('loaded_address_collection', $collection);
        }

        return $collection;
    }

    /**
     * Retrieve all customer attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->_getResource()
            ->loadAllAttributes($this)
            ->getAttributesByCode();
    }

    public function setPassword($password)
    {
        $this->setData('password', $password);
        $this->setPasswordHash($this->hashPassword($password));
        return $this;
    }

    /**
     * Hach customer password
     *
     * @param   string $password
     * @return  string
     */
    public function hashPassword($password)
    {
        return $this->_getResource()->getHashPassword($password);
    }

    /**
     * Encrypt password
     *
     * @param   string $password
     * @return  string
     */
    public function encryptPassword($password)
    {
        return Mage::helper('core')->encrypt($password);
    }

    /**
     * Decrypt password
     *
     * @param   string $password
     * @return  string
     */
    public function decryptPassword($password)
    {
        return Mage::helper('core')->decrypt($password);
    }

    /**
     * Retrieve primary address by type(attribute)
     *
     * @param   string $attributeCode
     * @return  Mage_Customer_Mode_Address
     */
    public function getPrimaryAddress($attributeCode)
    {
        $addressId = $this->getData($attributeCode);
        $primaryAddress = false;
        if ($addressId) {
            foreach ($this->getLoadedAddressCollection() as $address) {
                if ($addressId == $address->getId()) {
                    return $address;
                }
            }
        }
        return $primaryAddress;
    }

    /**
     * Retrieve customer primary billing address
     *
     * @return Mage_Customer_Mode_Address
     */
    public function getPrimaryBillingAddress()
    {
        return $this->getPrimaryAddress('default_billing');
    }

    public function getDefaultBillingAddress()
    {
        return $this->getPrimaryBillingAddress();
    }

    /**
     * Retrieve primary customer shipping address
     *
     * @return Mage_Customer_Mode_Address
     */
    public function getPrimaryShippingAddress()
    {
        return $this->getPrimaryAddress('default_shipping');
    }

    public function getDefaultShippingAddress()
    {
        return $this->getPrimaryShippingAddress();
    }

    /**
     * Retrieve ids of primary addresses
     *
     * @return unknown
     */
    public function getPrimaryAddressIds()
    {
        $ids = array();
        if ($this->getDefaultBilling()) {
            $ids[] = $this->getDefaultBilling();
        }
        if ($this->getDefaultShipping()) {
            $ids[] = $this->getDefaultShipping();
        }
        return $ids;
    }

    /**
     * Retrieve all customer primary addresses
     *
     * @return array
     */
    public function getPrimaryAddresses()
    {
        $addresses = array();
        $primaryBilling = $this->getPrimaryBillingAddress();
        if ($primaryBilling) {
            $addresses[] = $primaryBilling;
            $primaryBilling->setIsPrimaryBilling(true);
        }

        $primaryShipping = $this->getPrimaryShippingAddress();
        if ($primaryShipping) {
            if ($primaryBilling->getId() == $primaryShipping->getId()) {
                $primaryBilling->setIsPrimaryShipping(true);
            }
            else {
                $primaryShipping->setIsPrimaryShipping(true);
                $addresses[] = $primaryShipping;
            }
        }
        return $addresses;
    }

    /**
     * Retrieve not primary addresses
     *
     * @return array
     */
    public function getAdditionalAddresses()
    {
        $addresses = array();
        $primatyIds = $this->getPrimaryAddressIds();
        foreach ($this->getLoadedAddressCollection() as $address) {
            if (!in_array($address->getId(), $primatyIds)) {
                $addresses[] = $address;
            }
        }
        return $addresses;
    }

    public function isAddressPrimary(Mage_Customer_Model_Address $address)
    {
        if (!$address->getId()) {
            return false;
        }
        return ($address->getId() == $this->getDefaultBilling()) || ($address->getId() == $this->getDefaultShipping());
    }

    /**
     * Retrieve random password
     *
     * @param   int $length
     * @return  string
     */
    public function generatePassword($length=6)
    {
        return substr(md5(uniqid(rand(), true)), 0, $length);
    }

    /**
     * Send email with account information
     *
     * @return Mage_Customer_Model_Customer
     */
    public function sendNewAccountEmail()
    {
        Mage::getModel('core/email_template')
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_REGISTER_EMAIL_TEMPLATE),
                Mage::getStoreConfig(self::XML_PATH_REGISTER_EMAIL_IDENTITY),
                $this->getEmail(),
                $this->getName(),
                array('customer'=>$this));
        return $this;
    }

    /**
     * Send email with new customer password
     *
     * @return Mage_Customer_Model_Customer
     */
    public function sendPasswordReminderEmail()
    {
        Mage::getModel('core/email_template')
            ->sendTransactional(
              Mage::getStoreConfig(self::XML_PATH_FORGOT_EMAIL_TEMPLATE),
              Mage::getStoreConfig(self::XML_PATH_FORGOT_EMAIL_IDENTITY),
              $this->getEmail(),
              $this->getName(),
              array('customer'=>$this));
        return $this;
    }

    /**
     * Retrieve customer group identifier
     *
     * @return int
     */
    public function getGroupId()
    {
        if (!$this->getData('group_id')) {
            $storeId = $this->getStoreId() ? $this->getStoreId() : Mage::app()->getStore()->getId();
            $this->setData('group_id', Mage::getStoreConfig(
                Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, $storeId));
        }
        return $this->getData('group_id');
    }

    /**
     * Retrieve customer tax class identifier
     *
     * @return int
     */
    public function getTaxClassId()
    {
        if (!$this->getData('tax_class_id')) {
            $this->setTaxClassId(Mage::getModel('customer/group')->load($this->getGroupId())->getTaxClassId());
        }
        return $this->getData('tax_class_id');
    }

    /**
     * Check store availability for customer
     *
     * @param   mixed $store
     * @return  bool
     */
    public function isInStore($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $storeId = $store->getId();
        }
        else {
            $storeId = $store;
        }
        $availableStores = $this->getStore()->getWebsite()->getStoresIds();

        return in_array($storeId, $availableStores);
    }

    /**
     * Retrieve store where customer was created
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            if ($this->getStoreId() == Mage::app()->getStore()->getId()) {
                $this->_store = Mage::app()->getStore();
            } else {
                $this->_store = Mage::getModel('core/store')->load($this->getStoreId());
            }
        }
        return $this->_store;
    }

    /**
     * Retrieve shared store ids
     *
     * @return array|false
     */
    public function getSharedStoreIds()
    {
        return $this->_getResource()->getSharedStoreIds();
    }

    /**
     * Enter description here...
     *
     * @param Mage_Core_Model_Store $store
     * @return Mage_Customer_Model_Customer
     */
    public function setStore(Mage_Core_Model_Store $store)
    {
        $this->_store = $store;
        $storeId = $store->getId();
        $this->setStoreId($storeId);
        foreach ($this->getLoadedAddressCollection() as $address) {
            /* @var $address Mage_Customer_Model_Address */
            $address->setStoreId($storeId);
        }
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param int $storeId
     * @return Mage_Customer_Model_Customer
     */
    public function setStoreId($storeId)
    {
        $this->_getResource()->setStore($storeId);
        $this->setData('store_id', $storeId);
        if (! is_null($this->_store) && ($this->_store->getId() != $storeId)) {
            $this->_store = null;
        }
        return $this;
    }

    /**
     * Customer delete
     *
     * @return Mage_Customer_Model_Customer
     */
    public function delete()
    {
        $customerId = $this->getId();
        parent::delete();
        Mage::dispatchEvent('customer_model_delete', array('customer_id'=>$customerId, 'customer'=>$this));
        return $this;
    }
}