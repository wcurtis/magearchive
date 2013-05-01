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
 * Customer address controller
 *
 * @category   Mage
 * @package    Mage_Customer
 */
class Mage_Customer_AddressController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     * Customer addresses list
     */
    public function indexAction()
    {
        if (count(Mage::getSingleton('customer/session')->getCustomer()->getAddresses()))
        {
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->renderLayout();
        }
        else {
            $this->getResponse()->setRedirect(Mage::getUrl('*/*/new'));
        }
    }

    public function editAction()
    {
        $this->_forward('form');
    }

    public function newAction()
    {
        $this->_forward('form');
    }

    /**
     * Address book form
     */
    public function formAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('customer/address');
        }
        $this->renderLayout();
    }

    public function formPostAction()
    {
        // Save data
        if ($this->getRequest()->isPost()) {
            $address = Mage::getModel('customer/address')
                ->setData($this->getRequest()->getPost())
                ->setId($this->getRequest()->getParam('id'))
                ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));

            try {
                $address->save();
                Mage::getSingleton('customer/session')
                    ->addSuccess(Mage::helper('customer')->__('The address was successfully saved'));

                $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('customer/session')
                    ->setAddressFormData($this->getRequest()->getPost())
                    ->addError($e->getMessage());
            }
        }
        $this->_redirectError(Mage::getUrl('*/*/edit', array('id'=>$address->getId())));
    }

    public function deleteAction()
    {
        $addressId = $this->getRequest()->getParam('id', false);

        if ($addressId) {
            $address = Mage::getModel('customer/address')->load($addressId);

            // Validate address_id <=> customer_id
            if ($address->getCustomerId() != Mage::getSingleton('customer/session')->getCustomerId()) {
                Mage::getSingleton('customer/session')
                    ->addError(Mage::helper('customer')->__('The address does not belong to this customer'));
                $this->getResponse()->setRedirect(Mage::getUrl('*/*/index'));
                return;
            }

            try {
                $address->delete();
                Mage::getSingleton('customer/session')
                    ->addSuccess(Mage::helper('customer')->__('The address was successfully deleted'));
            }
            catch (Exception $e){
                Mage::getSingleton('customer/session')
                    ->addError(Mage::helper('customer')->__('There was an error while deleting the address'));
            }
        }
        $this->getResponse()->setRedirect(Mage::getUrl('*/*/index'));
    }

}
