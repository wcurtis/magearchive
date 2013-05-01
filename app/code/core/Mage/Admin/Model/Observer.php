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
 * Admin observer model
 *
 * @category   Mage
 * @package    Mage_Admin
 */
class Mage_Admin_Model_Observer
{
    public function actionPreDispatchAdmin($event)
    {
        $session  = Mage::getSingleton('admin/session');
        $request = Mage::app()->getRequest();
        $user = $session->getUser();

        if (!$user) {
            if ($request->getPost('login')) {
                $postLogin  = $request->getPost('login');
                $username   = $postLogin['username'];
                $password   = $postLogin['password'];
                if (!empty($username) && !empty($password)) {
                    $user = Mage::getModel('admin/user')->login($username, $password);
                    if ( $user->getId() && $user->getIsActive() != '1' ) {
                        if (!$request->getParam('messageSent')) {
                                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Your Account has been deactivated.'));
                                $request->setParam('messageSent', true);
                        }
                    } elseif (!Mage::getModel('admin/user')->hasAssigned2Role($user->getId())) {
                        if (!$request->getParam('messageSent')) {
                                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Access Denied.'));
                                $request->setParam('messageSent', true);
                        }
                    } else {
                        if ($user->getId()) {
                            $session->setUser($user);
                            header('Location: '.$request->getRequestUri());
                            exit;
                        } else {
                            if (!$request->getParam('messageSent')) {
                                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Invalid Username or Password.'));
                                $request->setParam('messageSent', true);
                            }
                        }
                    }
                }
            }
            if (!$request->getParam('forwarded')) {
                if($request->getParam('isAjax')) {
                    $request->setParam('forwarded', true)
                        ->setControllerName('index')
                        ->setActionName('deniedJson')
                        ->setDispatched(false);
                } else {
                    $request->setParam('forwarded', true)
                        ->setControllerName('index')
                        ->setActionName('login')
                        ->setDispatched(false);
                }

                return false;
            }
        } else {
            $user->reload();
        }

        if ($user) {
            if (!$session->getAcl() || $user->getReloadAclFlag()) {
                $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
            }
            if ($user->getReloadAclFlag()) {
                $user->unsetData('password');
                $user->setReloadAclFlag('0')->save();
            }
        }

    }


}
