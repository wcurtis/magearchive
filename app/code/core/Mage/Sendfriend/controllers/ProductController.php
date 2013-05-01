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
 * @package    Mage_Sendfriend
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Sendfriend_ProductController extends Mage_Core_Controller_Front_Action
{
    protected function _initProduct()
    {
        $productId  = (int) $this->getRequest()->getParam('id');

        $product = Mage::getModel('catalog/product')
            ->load($productId);

        Mage::register('product', $product);
    }

    protected function _initSendToFriendModel(){
        $sendToFriendModel = Mage::getModel('sendfriend/sendfriend');
        Mage::register('send_to_friend_model', $sendToFriendModel);
    }

    public function sendAction(){
        $this->_initProduct();
        $this->_initSendToFriendModel();

        $product = Mage::registry('product');
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $this->_forward('noRoute');
            return;
        }

        $productHelper = Mage::helper('catalog/product');
        $sendToFriendModel = Mage::registry('send_to_friend_model');
        // check if user is allowed to send product to a friend
        if (! $sendToFriendModel->canEmailToFriend()) {
            Mage::getSingleton('catalog/session')->addError($this->__('You cannot email this product to a friend'));
            $this->_redirectReferer($productHelper->getProductUrl($product->getId()));
            return;
        }

        $maxSendsToFriend = $sendToFriendModel->getMaxSendsToFriend();
        if ($maxSendsToFriend){
            Mage::getSingleton('catalog/session')->addNotice($this->__('You cannot send more than %d times in an hour', $maxSendsToFriend));
        }

        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();

        $update->addHandle('PRODUCT_'.$product->getId());

        $this->loadLayoutUpdates();

        $update->addUpdate($product->getCustomLayoutUpdate());

        $this->generateLayoutXml()->generateLayoutBlocks();

        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }

    public function sendmailAction()
    {
        $this->_initProduct();
        $this->_initSendToFriendModel();

        $product = Mage::registry('product');
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $this->_forward('noRoute');
            return;
        }

        $sendToFriendModel = Mage::registry('send_to_friend_model');

        if($this->getRequest()->getPost()) {
            $sendToFriendModel->setSender($this->getRequest()->getParam('sender'));
            $sendToFriendModel->setRecipients($this->getRequest()->getParam('recipients'));
            $sendToFriendModel->setIp(Mage::getSingleton('log/visitor')->getRemoteAddr());
            $sendToFriendModel->setProduct($product);

            try {
                if ($sendToFriendModel->canSend()) {
                    $sendToFriendModel->send();

                    Mage::getSingleton('catalog/session')->addSuccess(
                        $this->__('Link to a friend was sent.')
                    );

                    $this->_redirectSuccess($product->getProductUrl());
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('catalog/session')->addError($e->getMessage());
            } catch (Exception $e) {
            echo $e;
                Mage::getSingleton('catalog/session')
                    ->addException($e, Mage::helper('sendfriend')
                    ->__('Some emails was not sent')
                );
            }

            $this->_redirectError(Mage::getURL('*/*/send',array('id'=>$product->getId())));
        } else {
            $this->_forward('noRoute');
        }
    }

}