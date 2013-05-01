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
 * @package    Mage_Tag
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tag Frontend controller
 *
 * @category   Mage
 * @package    Mage_Tag
 */

class Mage_Tag_IndexController extends Mage_Core_Controller_Front_Action
{
    public function saveAction()
    {
        if( $tagName = $this->getRequest()->getQuery('tagName') ) {

            $product = Mage::getModel('catalog/product')
                ->load($this->getRequest()->getParam('productId'));
            $productUrl = $product->getProductUrl();
            $this->getResponse()->setRedirect($productUrl);

            try {
                if( !Mage::getSingleton('customer/session')->authenticate($this) ) {
                    return;
                }

                $customerId = Mage::getSingleton('customer/session')->getCustomerId();

                $tagName = urldecode($tagName);
                $tagNamesArr = explode("\n", preg_replace("/(\'(.*?)\')|(\s+)/i", "$1\n", $tagName));

                foreach( $tagNamesArr as $key => $tagName ) {
                    $tagNamesArr[$key] = trim($tagNamesArr[$key], '\'');
                    $tagNamesArr[$key] = trim($tagNamesArr[$key]);
                    if( $tagNamesArr[$key] == '' ) {
                        unset($tagNamesArr[$key]);
                    }
                }

                foreach( $tagNamesArr as $tagName ) {
                    if( $tagName ) {
                        $tagModel = Mage::getModel('tag/tag');
                        $tagModel->loadByName($tagName);

                        $tagModel->setName($tagName)
                                ->setStoreId(Mage::app()->getStore()->getId())
                                ->setStatus( ( $tagModel->getId() && $tagModel->getStatus() != $tagModel->getPendingStatus() ) ? $tagModel->getStatus() : $tagModel->getPendingStatus() )
                                ->save();

                        $tagRelationModel = Mage::getModel('tag/tag_relation');

                        $tagRelationModel->loadByTagCustomer($this->getRequest()->getParam('productId'), $tagModel->getId(), Mage::getSingleton('customer/session')->getCustomerId(), Mage::app()->getStore()->getId());

                        if( $tagRelationModel->getCustomerId() == $customerId && $tagRelationModel->getActive()) {
                            return;
                        }

                        $tagRelationModel->setTagId($tagModel->getId())
                            ->setCustomerId($customerId)
                            ->setProductId($this->getRequest()->getParam('productId'))
                            ->setStoreId(Mage::app()->getStore()->getId())
                            ->setCreatedAt( now() )
                            ->setActive(1)
                            ->save();
                        $tagModel->aggregate();
                    } else {
                        continue;
                    }
                }

                Mage::getSingleton('catalog/session')
                        ->addSuccess(Mage::helper('tag')->__('Your tag(s) have been submitted'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('catalog/session')
                    ->addError(Mage::helper('tag')->__('Unable to save tag(s)'));
                return;
            }
        }
        return;
    }
    /*
    public function saveAllAction()
    {
        $tagName = $this->getRequest()->getParam('tagName');
        $tagNamesArr = explode("\n", preg_replace("/'*(\d+)(\s+)/i", "$1\n", $tagName));

        for($i=0;$i<=500;$i++) {
            $tagName = rand(1100, 1150);
            if( $tagName ) {
                $tagModel = Mage::getModel('tag/tag');
                $tagModel->loadByName($tagName);

                $tagModel->setName($tagName)
                        ->setStoreId(1)
                        ->setStatus( $tagModel->getApprovedStatus() )
                        ->save();

                $tagRelationModel = Mage::getModel('tag/tag_relation');

                $tagRelationModel->setTagId($tagModel->getId())
                    ->setCustomerId(44)
                    ->setProductId(2)
                    ->setStoreId(1)
                    ->save();
            }
        }
    }
    */
}
