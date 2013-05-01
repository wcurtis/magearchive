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
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Checkout_CartController extends Mage_Core_Controller_Front_Action
{

    protected function _goBack()
    {
        if (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
            && !$this->getRequest()->getParam('in_cart')
            && $backUrl = $this->_getRefererUrl()) {

            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                Mage::getSingleton('checkout/session')->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $this->_redirect('checkout/cart');
        }
        return $this;
    }

    public function getQuote()
    {
        if (empty($this->_quote)) {
            $this->_quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    public function indexAction()
    {
        $cart = $this->_getCart();
        $cart->init();
        $cart->save();

        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');

        $this->renderLayout();
    }

    public function addgroupAction()
    {
        $productIds = $this->getRequest()->getParam('products');
        $cart = $this->_getCart();
        $cart->addProductsByIds($productIds);
        $cart->save();

        $this->_goBack();
    }

    /**
     * Adding product to shopping cart action
     */
    public function addAction()
    {
        $productId       = (int) $this->getRequest()->getParam('product');
        $qty             = $this->getRequest()->getParam('qty', 1);
        $relatedProducts = $this->getRequest()->getParam('related_product');

        if (!$productId) {
            $this->_goBack();
            return;
        }

        $additionalIds = array();
        // Parse related products
        if ($relatedProducts) {
            $relatedProducts = explode(',', $relatedProducts);
            if (is_array($relatedProducts)) {
                foreach ($relatedProducts as $relatedId) {
                    $additionalIds[] = $relatedId;
                }
            }
        }

        try {
            $cart = $this->_getCart();
            $product = Mage::getModel('catalog/product')
                ->load($productId)
                ->setConfiguredAttributes($this->getRequest()->getParam('super_attribute'))
                ->setGroupedProducts($this->getRequest()->getParam('super_group', array()));
            $eventArgs = array(
                'product' => $product,
                'qty' => $qty,
                'additional_ids' => $additionalIds,
                'request' => $this->getRequest(),
            );

            Mage::dispatchEvent('checkout_cart_before_add', $eventArgs);

            $cart->addProduct($product, $qty)
                ->addProductsByIds($additionalIds);

            Mage::dispatchEvent('checkout_cart_after_add', $eventArgs);

            $cart->save();

            Mage::dispatchEvent('checkout_cart_add_product', array('product'=>$product));

            $message = Mage::helper('checkout')->__('%s was successfully added to your shopping cart.', $product->getName());
            if (!$this->getRequest()->getParam('in_cart')) {
                // $message .= ' ' . Mage::helper('checkout')->__('Click <a href="%s">here</a> to continue shopping', $this->_getRefererUrl());
            }



            $messages = Mage::getSingleton('checkout/session')->getWishlistPendingMessages();
            $urls = Mage::getSingleton('checkout/session')->getWishlistPendingUrls();
            $wishlistIds = Mage::getSingleton('checkout/session')->getWishlistIds();

            if (count($wishlistIds) && $this->getRequest()->getParam('wishlist_next')){
                $wishlistId = array_shift($wishlistIds);

                $wishlist = Mage::getModel('wishlist/wishlist')
                        ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
                $wishlist->getItemCollection()->load();

                foreach($wishlist->getItemCollection() as $wishlistItem){
                    if ($wishlistItem->getId() == $wishlistId)
                        $wishlistItem->delete();
                }
                Mage::getSingleton('checkout/session')->setWishlistIds($wishlistIds);
            }

            if ($this->getRequest()->getParam('wishlist_next') && count($urls)) {
                $url = array_shift($urls);
                $message = array_shift($messages);

                Mage::getSingleton('checkout/session')->setWishlistPendingUrls($urls);
                Mage::getSingleton('checkout/session')->setWishlistPendingMessages($messages);

                Mage::getSingleton('checkout/session')->addError($message);
                $this->getResponse()->setRedirect($url);
            } else {
                Mage::getSingleton('checkout/session')->addSuccess($message);
                $this->_goBack();
            }
        }
        catch (Mage_Core_Exception $e){
            if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
            }
            else {
                Mage::getSingleton('checkout/session')->addError($e->getMessage());
            }

            $url = Mage::getSingleton('checkout/session')->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            }
            else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        }
        catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addException($e, Mage::helper('checkout')->__('Can not add item to shopping cart'));
            $this->_goBack();
        }
    }

    /**
     * Update shoping cart data action
     */
    public function updatePostAction()
    {
        try {
            $cartData = $this->getRequest()->getParam('cart');
            $cart = $this->_getCart();
            $cart->updateItems($cartData)
                ->save();
        }
        catch (Mage_Core_Exception $e){
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
        }
        catch (Exception $e){
            Mage::getSingleton('checkout/session')->addException($e, Mage::helper('checkout')->__('Cannot update shopping cart'));
        }

        $this->_goBack();
    }

    /**
     * Move shopping cart item to wishlist action
     */
    public function moveToWishlistAction()
    {
        $id = $this->getRequest()->getParam('id');
        try {
            $this->_getCart()->moveItemToWishlist($id)
                ->save();
        }
        catch (Exception $e){
            Mage::getSingleton('checkout/session')->addError(Mage::helper('checkout')->__('Cannot move item to wishlist'));
        }
        $this->_goBack();
    }

    /**
     * Delete shoping cart item action
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $cart = Mage::getSingleton('checkout/cart');
        try {
            $cart->removeItem($id)
              ->save();
        } catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError(Mage::helper('checkout')->__('Cannot remove item'));
        }

        $this->_redirectReferer(Mage::getUrl('*/*'));
    }

    public function estimatePostAction()
    {
        $country = $this->getRequest()->getParam('country_id');
        $postcode = $this->getRequest()->getParam('estimate_postcode');
        $city = $this->getRequest()->getParam('estimate_city');

        $this->getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($this->getRequest()->getParam('region_id'))
            ->setRegion($this->getRequest()->getParam('region'))
            ->setCollectShippingRates(true);
        $this->getQuote()/*->collectTotals()*/->save();

        $this->_goBack();
    }

    public function estimateUpdatePostAction()
    {
        $code = $this->getRequest()->getParam('estimate_method');

        $this->getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();

        $this->_goBack();
    }

    public function couponPostAction()
    {
        $couponCode = $this->getRequest()->getParam('coupon_code');
        try {
            $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->getQuote()->setCouponCode($couponCode)
                ->collectTotals()
                ->save();
            if ($couponCode) {
                if ($couponCode == $this->getQuote()->getCouponCode()) {
                    Mage::getSingleton('checkout/session')->addSuccess(
                        $this->__('Coupon code was applied successfully.')
                    );
                }
                else {
                    Mage::getSingleton('checkout/session')->addError(
                        $this->__('"%s" coupon code is not valid.', $couponCode)
                    );
                }
            }

        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError(
                $this->__('Can not apply coupon code.')
            );
        }

        $this->_goBack();
    }

    public function giftCertPostAction()
    {
        if ($this->getRequest()->getParam('do')==Mage::helper('checkout')->__('Clear')) {
            $giftCode = '';
        } else {
            $giftCode = $this->getRequest()->getParam('giftcert_code');
        }

        $this->getQuote()->setGiftcertCode($giftCode)/*->collectTotals()*/->save();

        $this->_goBack();
    }
}