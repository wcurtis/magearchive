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
 * Customer tags controller
 *
 * @category   Mage
 * @package    Mage_Tag
 */

class Mage_Tag_CustomerController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        if( !Mage::getSingleton('customer/session')->getCustomerId() ) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('tag/session');
        $this->renderLayout();
    }

    public function viewAction()
    {
        if( !Mage::getSingleton('customer/session')->getCustomerId() ) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }
        Mage::register('tagId', $this->getRequest()->getParam('tagId'));

        $this->loadLayout();
        $this->_initLayoutMessages('tag/session');
        $this->renderLayout();
    }

    public function editAction()
    {
        if( !Mage::getSingleton('customer/session')->getCustomerId() ) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('tag/session');

        $tagId = $this->getRequest()->getParam('tagId');
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();

        $model = Mage::getModel('tag/tag_relation');
        $model->loadByTagCustomer(null, $tagId, $customerId);

        Mage::register('tagModel', $model);

        if( intval($tagId) <= 0 ) {
            $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
            return;
        }

        if( $model->getCustomerId() != $customerId ) {
            $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
            return;
        }

        $this->_initLayoutMessages('customer/session');

        $this->renderLayout();
    }

    public function removeAction()
    {
        if( !Mage::getSingleton('customer/session')->getCustomerId() ) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }

        $tagId = $this->getRequest()->getParam('tagId');
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();

        if( intval($tagId) <= 0 ) {
            $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
            return;
        }

        $model = Mage::getModel('tag/tag_relation');
        $model->loadByTagCustomer(null, $tagId, $customerId);
        if( $model->getCustomerId() == $customerId ) {
            try {
                $model->deactivate();
                $tag = Mage::getModel('tag/tag')->load($tagId)->aggregate();
                Mage::getSingleton('tag/session')->addSuccess(Mage::helper('tag')->__('You tag was successfully deleted'));
                $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('tag/session')->addError(Mage::helper('tag')->__('Unable to remove tag. Please, try again later.'));
            }
        } else {
            $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
            return;
        }
    }

    public function saveAction()
    {
        if( !Mage::getSingleton('customer/session')->getCustomerId() ) {
            Mage::getSingleton('customer/session')->authenticate($this);
            return;
        }

        $this->_redirectReferer();

        if( $post = $this->getRequest()->getPost() ) {
            try {
                $tagId = $this->getRequest()->getParam('tagId');
                $customerId = Mage::getSingleton('customer/session')->getCustomerId();
                $tagName = $this->getRequest()->getParam('tagName');
                $productId = 0;
                $isNew = false;
                $message = false;

                $tagModel = Mage::getModel('tag/tag');
                $tagModel->load($tagId);
                $storeId = Mage::app()->getStore()->getId();
                if( $tagModel->getName() != $tagName ) {
                    $tagModel->loadByName($tagName);

                    if( !$tagModel->getName() ) {
                        $isNew = true;
                        $message = Mage::helper('tag')->__('Thank you. Your tag has been accepted for moderation.');
                    }

                    $tagModel->setName($tagName)
                            ->setStatus( ( $tagModel->getId() && $tagModel->getStatus() != $tagModel->getPendingStatus() ) ? $tagModel->getStatus() : $tagModel->getPendingStatus() )
                            ->setStoreId($storeId)
                            ->save();
                }

                $tagRalationModel = Mage::getModel('tag/tag_relation');
                $tagRalationModel->loadByTagCustomer(null, $tagId, $customerId, $storeId);

                if ($tagRalationModel->getCustomerId() == $customerId ) {
                    $productId = $tagRalationModel->getProductId();
                    if ($tagRalationModel->getTagId()!=$tagModel->getId()) {
                        $tagRalationModel->setActive(0)->save();
                    } else {
                        $tagRalationModel->delete();
                    }

                    $newTagRalationModel = Mage::getModel('tag/tag_relation')
                        ->setTagId($tagModel->getId())
                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->setStoreId($storeId)
                        ->setActive(1)
                        ->setProductId($productId)
                        ->save();
                }


                if( $tagModel->getId() ) {
                    $tagModel->aggregate();
                    $this->getResponse()->setRedirect(Mage::getUrl('*/*/view', array('tagId' => $tagModel->getId())));
                }

                Mage::getSingleton('tag/session')
                    ->addSuccess( ($message) ? $message : Mage::helper('tag')->__('You tag was successfully saved') );
                return;
            } catch (Exception $e) {
                Mage::getSingleton('tag/session')
                    ->addError(Mage::helper('tag')->__('Unable to save your tag. Please, try again later.') );
                return;
            }
        }
    }
}
