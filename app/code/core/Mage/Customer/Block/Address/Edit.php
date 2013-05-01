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
 * Customer address edit block
 *
 * @category   Mage
 * @package    Mage_Customer
 */
class Mage_Customer_Block_Address_Edit extends Mage_Directory_Block_Data
{
    protected $_address;
    protected $_countryCollection;
    protected $_regionCollection;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('customer/address/edit.phtml');
        $this->_address = Mage::getModel('customer/address');

        // Init address object
        if ($id = $this->getRequest()->getParam('id')) {
            $this->_address->load($id);
            if ($this->_address->getCustomerId() != Mage::getSingleton('customer/session')->getCustomerId()) {
                $this->_address->setData(array());
            }
        }

        Mage::registry('action')->getLayout()->getBlock('root')->setHeaderTitle(($this->getAddress()->getId()?'Edit':'New').' Address Entry');

        if ($postedData = Mage::getSingleton('customer/session')->getAddressFormData(true)) {
            $this->_address->setData($postedData);
        }
    }

    public function getTitle()
    {
        if ($title = $this->getData('title')) {
            return $title;
        }
        if ($this->getAddress()->getId()) {
            $title = Mage::helper('customer')->__('Edit Address');
        }
        else {
            $title = Mage::helper('customer')->__('Add New Address');
        }
        return $title;
    }

    public function getBackUrl()
    {
        $url = $this->getData('back_url');
        if (is_null($url)) {
            $url = Mage::getUrl('*/*/index', array('_secure'=>true));
            $this->setData('back_url', $url);
        }
        return $url;
    }

    public function getSaveUrl()
    {
        return Mage::getUrl('customer/address/formPost', array('_secure'=>true, 'id'=>$this->getAddress()->getId()));
    }

    public function getAddress()
    {
        return $this->_address;
    }

    public function getCountryId()
    {
        if ($countryId = $this->getAddress()->getCountryId()) {
            return $countryId;
        }
        return parent::getCountryId();
    }

    public function getRegionId()
    {
        return $this->getAddress()->getRegionId();
    }

    public function getCustomerAddressCount()
    {
        return Mage::getSingleton('customer/session')->getCustomer()
            ->getLoadedAddressCollection()
            ->getSize();
    }

    public function canSetAsDefaultBilling()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getCustomerAddressCount();
        }
        return !$this->isDefaultBilling();
    }

    public function canSetAsDefaultShipping()
    {
        if (!$this->getAddress()->getId()) {
            return $this->getCustomerAddressCount();
        }
        return !$this->isDefaultShipping();;
    }

    public function isDefaultBilling()
    {
        return $this->getAddress()->getId() && $this->getAddress()->getId()==Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
    }

    public function isDefaultShipping()
    {
        return $this->getAddress()->getId() && $this->getAddress()->getId()==Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
    }

    public function getCustomer()
    {
        return Mage::getSingleton('customer/session')->getCustomer();
    }
}
