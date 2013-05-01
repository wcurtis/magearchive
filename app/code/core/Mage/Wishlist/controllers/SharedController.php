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
 * @package    Mage_Wishlist
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Wishlist shared items controllers
 *
 * @category   Mage
 * @package    Mage_Wishlist
 */
class Mage_Wishlist_SharedController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {

        $wishlist = Mage::getModel('wishlist/wishlist')->loadByCode($this->getRequest()->getParam('code'));
        if ($wishlist->getCustomerId() && $wishlist->getCustomerId() == Mage::getSingleton('customer/session')->getCustomerId()) {
            $this->_redirectUrl(Mage::helper('wishlist')->getListUrl());
            return;
        }

        if(!$wishlist->getId()) {
            $this->norouteAction();
        } else {
            Mage::register('shared_wishlist', $wishlist);
            $this->loadLayout();
            $this->_initLayoutMessages('wishlist/session');
            $this->getLayout()->getBlock('content')
                ->append(
                    $this->getLayout()->createBlock('wishlist/share_wishlist','customer.wishlist')
            );
            $this->renderLayout();
        }

    }

    public function allcartAction()
    {
        $wishlist = Mage::getModel('wishlist/wishlist')
            ->loadByCode($this->getRequest()->getParam('code'));

        //exit($wishlist->getId());

        if(!$wishlist->getId()) {
            $this->norouteAction();
        } else {
            $wishlist->getProductCollection()->load();

            foreach ($wishlist->getProductCollection() as $item) {
                 try {
                    $product = Mage::getModel('catalog/product')->load($item->getId())->setQty(1);
                    Mage::getSingleton('checkout/cart')->addProduct($product);
                }
                catch(Exception $e) {
                    Mage::getSingleton('catalog/session')->addError($e->getMessage());
                    if($product) {
                        // Redirect to the last product with exception
                        $this->getResponse()->setRedirect(Mage::helper('catalog/product')->getProductUrl($product));
                    }
                    else {
                        $this->_redirect('catalog');
                    }
                    return;
                }
                Mage::getSingleton('checkout/cart')->save();
            }
            $this->_redirect('checkout/cart');
        }
    }

}
