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
 * @package    Mage_ProductAlert
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ProductAlert unsubscribe controller
 *
 * @category   Mage
 * @package    Mage_ProductAlert
 */
class Mage_ProductAlert_UnsubscribeController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
            if(!Mage::getSingleton('customer/session')->getBeforeUrl()) {
                Mage::getSingleton('customer/session')->setBeforeUrl($this->_getRefererUrl());
            }
        }
    }

    public function priceAction()
    {
        $session = Mage::getSingleton('catalog/session');
        /* @var $session Mage_Catalog_Model_Session */

        if (!$product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product'))) {
            /* @var $product Mage_Catalog_Model_Product */
            $session = Mage::getSingleton('customer/session');
            $session->addError(Mage::helper('productalert')->__('Product not found'));
            $this->_redirect('customer/account/');
            return ;
        }

        try {
            $model  = Mage::getModel('productalert/price')
                ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                ->setProductId($product->getId())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByParam();
            if ($model->getId()) {
                $model->delete();
            }

            $session->addSuccess(Mage::helper('productalert')->__('Alert subscription was deleted successfully'));
        }
        catch (Exception $e) {
            $session->addException($e, Mage::helper('productalert')->__('Please try again later'));
        }
        $this->_redirectUrl($product->getProductUrl());
    }

    public function priceAllAction()
    {
        $session = Mage::getSingleton('customer/session');
        /* @var $session Mage_Customer_Model_Session */

        try {
            Mage::getModel('productalert/price')
                ->deleteCustomer($session->getCustomerId(), Mage::app()->getStore()->getWebsiteId());
            $session->addSuccess(Mage::helper('productalert')->__('You will no longer receive price alerts for this product'));
        }
        catch (Exception $e) {
            $session->addException($e, Mage::helper('productalert')->__('Please try again later'));
        }
        $this->_redirect('customer/account/');
    }

    public function stockAction()
    {
        $session = Mage::getSingleton('catalog/session');
        /* @var $session Mage_Catalog_Model_Session */

        if (!$product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product'))) {
            /* @var $product Mage_Catalog_Model_Product */
            $session = Mage::getSingleton('customer/session');
            $session->addError(Mage::helper('productalert')->__('Product not found'));
            $this->_redirect('customer/account/');
            return ;
        }

        try {
            $model  = Mage::getModel('productalert/stock')
                ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                ->setProductId($product->getId())
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByParam();
            if ($model->getId()) {
                $model->delete();
            }

            $session->addSuccess(Mage::helper('productalert')->__('You will no longer receive stock alerts for this product'));
        }
        catch (Exception $e) {
            $session->addException($e, Mage::helper('productalert')->__('Please try again later'));
        }
        $this->_redirectUrl($product->getProductUrl());
    }

    public function stockAllAction()
    {
        $session = Mage::getSingleton('customer/session');
        /* @var $session Mage_Customer_Model_Session */

        try {
            Mage::getModel('productalert/stock')
                ->deleteCustomer($session->getCustomerId(), Mage::app()->getStore()->getWebsiteId());
            $session->addSuccess(Mage::helper('productalert')->__('You will no longer receive stock alerts'));
        }
        catch (Exception $e) {
            $session->addException($e, Mage::helper('productalert')->__('Please try again later'));
        }
        $this->_redirect('customer/account/');
    }
}