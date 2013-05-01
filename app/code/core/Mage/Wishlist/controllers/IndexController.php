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
 * Wishlist front controller
 *
 * @category   Mage
 * @package    Mage_Wishlist
 */
class Mage_Wishlist_IndexController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
            if(!Mage::getSingleton('customer/session')->getBeforeWishlistUrl()) {
                Mage::getSingleton('customer/session')->setBeforeWishlistUrl($this->_getRefererUrl());
            }
        }
    }

    public function indexAction()
    {
        try {
            $wishlist = Mage::getModel('wishlist/wishlist')
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
        }
        catch (Exception $e) {
            Mage::getSingleton('wishlist/session')->addError(Mage::helper('wishlist')->__('Cannot create wishlist'));
        }

        Mage::register('wishlist', $wishlist);


        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }

    public function addAction()
    {
        try {
            $wishlist = Mage::getModel('wishlist/wishlist')
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
        }
        catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError(Mage::helper('wishlist')->__('Cannot create wishlist'));
            $this->_redirect('*');
            return;
        }

        $productId = (int) $this->getRequest()->getParam('product');
        $product = Mage::getModel('catalog/product')->load($productId);
        if (!$product->getId()) {
            Mage::getSingleton('customer/session')->addError(Mage::helper('wishlist')->__('Cannot specify product'));
            $this->_redirect('*');
            return;
        }

        try {
            $wishlist->addNewItem($product->getId());

            Mage::dispatchEvent('wishlist_add_product', array('wishlist'=>$wishlist, 'product'=>$product));

            if ($referer = Mage::getSingleton('customer/session')->getBeforeWishlistUrl()) {
                Mage::getSingleton('customer/session')->setBeforeWishlistUrl(null);
            }
            else {
                $referer = $this->_getRefererUrl();
            }
            $message = Mage::helper('wishlist')->__('%1$s was successfully added to your wishlist. Click <a href="%2$s">here</a> to continue shopping', $product->getName(), $referer);
            Mage::getSingleton('customer/session')->addSuccess($message);
        }
        catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError(Mage::helper('wishlist')->__('There was an error while adding item to wishlist: %s', $e->getMessage()));
        }

        $this->_redirect('*');
    }

    public function updateAction()
    {
        $wishlist = Mage::getModel('wishlist/wishlist')
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);

        if($post = $this->getRequest()->getPost()) {
            foreach ($post['description'] as $itemId => $description) {
                $item = Mage::getModel('wishlist/item')->load($itemId);
                if($item->getWishlistId()!=$wishlist->getId()) {
                    continue;
                }

                try {
                       $item->setDescription($description)
                           ->save();
                }
                catch (Exception $e) { }
            }
        }


        $this->_redirect('*');
    }

    public function removeAction() {
        $wishlist = Mage::getModel('wishlist/wishlist')
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
        $id = (int) $this->getRequest()->getParam('item');
        $item = Mage::getModel('wishlist/item')->load($id);

        if($item->getWishlistId()==$wishlist->getId()) {
            try {
                $item->delete();
            }
            catch(Exception $e) {
                Mage::getSingleton('customer/session')->addError(Mage::helper('wishlist')->__('There was an error while deleting item from wishlist: %s', $e->getMessage()));
            }
        }
        $this->_redirect('*');
    }

    public function cartAction() {
        $wishlist = Mage::getModel('wishlist/wishlist')
            ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);

        $id = (int) $this->getRequest()->getParam('item');
        $item = Mage::getModel('wishlist/item')->load($id);

        if($item->getWishlistId()==$wishlist->getId()) {
             try {
                $product = Mage::getModel('catalog/product')->load($item->getProductId())->setQty(1);
                $quote = Mage::getSingleton('checkout/cart')
                   ->addProduct($product)
                   ->save();
                $item->delete();
            }
            catch(Exception $e) {
                Mage::getSingleton('checkout/session')->addError($e->getMessage());
                $url = Mage::getSingleton('checkout/session')->getRedirectUrl(true);
                if ($url) {
                    $this->getResponse()->setRedirect($url);
                }
                else {
                    $this->_redirect('*/*/');
                }
                return;
            }
        }
        $this->_redirect('checkout/cart');
    }

    public function allcartAction() {
        $messages = array();
        $urls = array();
        $wishlistIds = array();

        $wishlist = Mage::getModel('wishlist/wishlist')
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);

        $wishlist->getItemCollection()->load();
        foreach ($wishlist->getItemCollection() as $item) {
             try {
                $product = Mage::getModel('catalog/product')->load($item->getProductId())->setQty(1);
                if ($product->isSalable()){
                    Mage::getSingleton('checkout/cart')->addProduct($product);
                    $item->delete();
                }
            } catch(Exception $e) {
                $url = Mage::getSingleton('checkout/session')->getRedirectUrl(true);
                if ($url){
                    $url = Mage::getModel('core/url')->getUrl('catalog/product/view', array(
                        'id'=>$item->getProductId(),
                        'wishlist_next'=>1
                    ));

                    $urls[] = $url;
                    $messages[] = $e->getMessage();
                    $wishlistIds[] = $item->getId();
                } else {
                    $item->delete();
                }
            }
            Mage::getSingleton('checkout/cart')->save();
        }

        if ($urls) {
            Mage::getSingleton('checkout/session')->addError(array_shift($messages));
            $this->getResponse()->setRedirect(array_shift($urls));

            Mage::getSingleton('checkout/session')->setWishlistPendingUrls($urls);
            Mage::getSingleton('checkout/session')->setWishlistPendingMessages($messages);
            Mage::getSingleton('checkout/session')->setWishlistIds($wishlistIds);
        } else {
            $this->_redirect('checkout/cart');
        }
    }

    public function shareAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('content')
            ->append($this->getLayout()->createBlock('wishlist/customer_sharing','wishlist.sharing'));
        $this->renderLayout();
    }

    public function sendAction()
    {
        try{

            if (!$this->getRequest()->getParam('emails')) {
                Mage::throwException(Mage::helper('wishlist')->__('E-mail Addresses required'), 'wishlist/session');
            }

            $emails = explode(',', $this->getRequest()->getParam('emails'));
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            $wishlist = Mage::getModel('wishlist/wishlist')
                ->loadByCustomer($customer, true);
            Mage::register('wishlist', $wishlist);

            $message = nl2br(htmlspecialchars($this->getRequest()->getParam('message')));

            /*if share rss added rss feed to email template*/
            if ($this->getRequest()->getParam('rss_url')) {
                $rss_url = $this->getLayout()->createBlock('wishlist/share_email_rss')->toHtml();
                $message .=$rss_url;
            }

            $wishlistBlock = $this->getLayout()->createBlock('wishlist/share_email_items')->toHtml();

            foreach($emails as $key => $email) {
                $email = trim($email);
                $emails[$key] = $email;
            }

            $emails = array_unique($emails);

            $emailModel = Mage::getModel('core/email_template');
            foreach($emails as $email) {
                $emailModel->sendTransactional(
                    Mage::getStoreConfig('wishlist/email/email_template'),
                    Mage::getStoreConfig('wishlist/email/email_identity'),
                    $email,
                    null,
                    array(
                        'customer'          => $customer,
                        'items'                 => &$wishlistBlock,
                        'addAllLink'         => Mage::getUrl('*/shared/allcart',array('code'=>$wishlist->getSharingCode())),
                        'viewOnSiteLink'    => Mage::getUrl('*/shared/index',array('code'=>$wishlist->getSharingCode())),
                        'message'            => $message
                    ));
            }

            $wishlist->setShared(1);
            $wishlist->save();

            Mage::dispatchEvent('wishlist_share', array('wishlist'=>$wishlist));

            Mage::getSingleton('customer/session')->addSuccess(Mage::helper('wishlist')->__('Your Wishlist was successfully shared'));
            $this->_redirect('*/*');
        }
        catch (Exception $e) {
            Mage::getSingleton('wishlist/session')->addError($e->getMessage());
            Mage::getSingleton('wishlist/session')->setData('sharing_form', $this->getRequest()->getParams());
            $this->_redirect('*/*/share');
        }
    }

}
