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
 * Customer account controller
 *
 * @category   Mage
 * @package    Mage_Customer
 */
class Mage_Customer_AccountController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $action = $this->getRequest()->getActionName();
        if (!preg_match('/^(create|login|logoutSuccess|forgotpassword|forgotpasswordpost)/i', $action)) {
            if (!Mage::getSingleton('customer/session')->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        }
    }

    /**
     * Default customer account page
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $this->getLayout()->getBlock('content')->append($this->getLayout()->createBlock('customer/account_dashboard'));
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Account'));

        $this->renderLayout();
    }

    /**
     * Customer login form page
     */
    public function loginAction()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $this->getResponse()->setHeader('Login-Required', 'true');
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Login post action
     */
    public function loginPostAction()
    {
        $session = Mage::getSingleton('customer/session');

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                if (!$session->login($login['username'], $login['password'])) {
                    $session->addError($this->__('Invalid login or password'));
                    $session->setUsername($login['username']);
                }
            }
        }
        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl() ) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());
        }
        $this->_redirectUrl($session->getBeforeAuthUrl());
    }

    /**
     * Customer logout action
     */
    public function logoutAction()
    {
        Mage::getSingleton('customer/session')
            ->logout()
            ->setBeforeAuthUrl(Mage::getUrl());

        $this->_redirect('*/*/logoutSuccess');
    }

    /**
     * Logout success page
     */
    public function logoutSuccessAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Customer register form page
     */
    public function createAction()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_redirect('*/*');
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');


        $this->renderLayout();
    }

    /**
     * Create customer account action
     */
    public function createPostAction()
    {
        if ($this->getRequest()->isPost()) {

            $customer = Mage::getModel('customer/customer')
                ->setData($this->getRequest()->getPost());
            /**
             * Initialize customer group id
             */
            $customer->getGroupId();

            if ($this->getRequest()->getPost('create_address')) {
                $address = Mage::getModel('customer/address')
                    ->setData($this->getRequest()->getPost())
                    ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                    ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
                $customer->addAddress($address);
            }

            try {
                $customer->save();
                Mage::getSingleton('customer/session')
                    ->setCustomerAsLoggedIn($customer)
                    ->addSuccess(
                        $this->__('Thank you for registering with %s',
                            Mage::app()->getStore()->load(Mage::app()->getStore()->getStoreId())->getName())
                    );

                $customer->sendNewAccountEmail();

                $successUrl = Mage::getUrl('*/*/index', array('_secure'=>true));
                if (Mage::getSingleton('customer/session')->getBeforeAuthUrl()) {
                    $successUrl = Mage::getSingleton('customer/session')->getBeforeAuthUrl();
                }
                $this->_redirectSuccess($successUrl);
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('customer/session')
                    ->addError($e->getMessage())
                    ->setCustomerFormData($this->getRequest()->getPost());
            }
        }

        $this->_redirectError(Mage::getUrl('*/*/create', array('_secure'=>true)));
    }

    /**
     * Forgot customer password page
     */
    public function forgotPasswordAction()
    {
        $this->loadLayout();

        $this->getLayout()->getBlock('forgotPassword')->setEmailValue(
            Mage::getSingleton('customer/session')->getForgottenEmail()
        );
        Mage::getSingleton('customer/session')->unsForgottenEmail();

        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Forgot customer password action
     */
    public function forgotPasswordPostAction()
    {
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            $customer = Mage::getModel('customer/customer')->loadByEmail($email);
            if ($customer->getId()) {
                try {
                    $newPassword = $customer->generatePassword();
                    $customer->changePassword($newPassword, false);
                    $customer->sendPasswordReminderEmail();

                    Mage::getSingleton('customer/session')
                        ->addSuccess($this->__('A new password was sent'));

                    $this->getResponse()->setRedirect(Mage::getUrl('*/*'));
                    return;
                }
                catch (Exception $e){
                    Mage::getSingleton('customer/session')
                        ->addError($e->getMessage());
                }
            }
            else {
                Mage::getSingleton('customer/session')
                    ->addError($this->__('This email address was not found in our records'));
                Mage::getSingleton('customer/session')->setForgottenEmail($email);
            }
        }
        $this->getResponse()->setRedirect(Mage::getUrl('*/*/forgotpassword'));
    }

    /**
     * Forgot customer account information page
     */
    public function editAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        $data = Mage::getSingleton('customer/session')->getCustomerFormData(true);
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if (!empty($data)) {
            $customer->addData($data);
        }
        if($this->getRequest()->getParam('changepass')==1){
            $customer->setChangePassword(1);
        }

        $this->renderLayout();
    }

    /**
     * Change customer password action
     */
    public function editPostAction()
    {
        if ($this->getRequest()->isPost()) {

            $customer = Mage::getModel('customer/customer');
            /* @var $customer Mage_Customer_Model_Customer */
            $customer->setId(Mage::getSingleton('customer/session')->getCustomerId())
                ->setData('firstname', $this->getRequest()->getParam('firstname'))
                ->setData('lastname', $this->getRequest()->getParam('lastname'))
                ->setData('email', $this->getRequest()->getParam('email'));

            /*
            we would like to preserver the existing group id
            */
            if (Mage::getSingleton('customer/session')->getCustomerGroupId()) {
                $customer->setData('group_id',Mage::getSingleton('customer/session')->getCustomerGroupId());
            }

            // try to change customer password if needed
            if ($this->getRequest()->getParam('change_password')) {

                // validations start

                // TODO move into model validations
                $error = false;

                $currPass = $this->getRequest()->getPost('current_password');
                if (empty($currPass)) {
                    Mage::getSingleton('customer/session')
                        ->addError($this->__('Current Password') . $this->__(' is a required field'));
                    $error = true;
                }
                $newPass  = $this->getRequest()->getPost('password');
                if (empty($newPass)) {
                    Mage::getSingleton('customer/session')
                        ->addError($this->__('New Password') . $this->__(' is a required field'));
                    $error = true;
                }
                $confPass  = $this->getRequest()->getPost('confirmation');
                if (empty($confPass)) {
                    Mage::getSingleton('customer/session')
                        ->addError($this->__('Confirm New Password') . $this->__(' is a required field'));
                    $error = true;
                }
                if ($error) {
                    Mage::getSingleton('customer/session')
                        ->setCustomerFormData($this->getRequest()->getPost());
                    $this->_redirect('*/*/edit');
                    return;
                }
                if ($newPass != $confPass) {
                    Mage::getSingleton('customer/session')
                        ->setCustomerFormData($this->getRequest()->getPost())
                        ->addError($this->__('Please make sure your passwords match.'));
                    $this->_redirect('*/*/edit');
                    return;
                }

                if ($customer->hashPassword($currPass) == Mage::getSingleton('customer/session')->getCustomer()->getPasswordHash()) {
                    $customer->setPassword($newPass);
                } else {
                    Mage::getSingleton('customer/session')
                        ->setCustomerFormData($this->getRequest()->getPost())
                        ->addError($this->__('Invalid current password'));
                    $this->_redirect('*/*/edit');
                    return;
                }

                // validations end
            }

            try {
                $customer->save();
                Mage::getSingleton('customer/session')
                    ->setCustomer($customer)
                    ->addSuccess($this->__('Account information was successfully saved'));

                $this->_redirect('customer/account');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('customer/session')
                    ->setCustomerFormData($this->getRequest()->getPost())
                    ->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/edit');
    }

}
