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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product controller
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @module     Catalog
 */
class Mage_Catalog_ProductController extends Mage_Core_Controller_Front_Action
{
    protected function _initProduct()
    {
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $this->getRequest()->getParam('id');

        $product = Mage::getModel('catalog/product')
            ->load($productId);

        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            Mage::register('current_category', $category);
        }
        Mage::register('current_product', $product);
        Mage::register('product', $product); // this need remove after all replace
    }

    protected function _initSendToFriendModel(){
        $sendToFriendModel = Mage::getModel('catalog/sendfriend');
        Mage::register('send_to_friend_model', $sendToFriendModel);
    }

    public function viewAction()
    {
        $this->_initProduct();
        $this->_initSendToFriendModel();

        $product = Mage::registry('product');
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $this->_forward('noRoute');
            return;
        }

        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();

        $update->addHandle('PRODUCT_'.$product->getId());

        $this->loadLayoutUpdates();

        $update->addUpdate($product->getCustomLayoutUpdate());

        $this->generateLayoutXml()->generateLayoutBlocks();

        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('tag/session');
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }

    public function galleryAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->renderLayout();
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
        $this->_initLayoutMessages('tag/session');
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }

    public function sendmailAction()
    {
        $this->_initProduct();
        $this->_initSendToFriendModel();

        $product = Mage::registry('current_product');
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
                    ->addException($e, Mage::helper('catalog')
                    ->__('Some emails was not sent')
                );
            }

            $this->_redirectError(Mage::getURL('catalog/product/send',array('id'=>$product->getId())));
        } else {
            $this->_forward('noRoute');
        }
    }
}
