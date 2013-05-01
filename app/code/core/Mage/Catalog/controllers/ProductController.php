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
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);

        if (!in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
            return false;
        }

        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            Mage::register('current_category', $category);
        }

        Mage::register('current_product', $product);
        Mage::getSingleton('catalog/session')->setLastViewedProductId($product->getId());

        Mage::register('product', $product); // this need remove after all replace

        Mage::getModel('catalog/design')->applyDesign($product, 1);
        return true;
    }

    protected function _initSendToFriendModel(){
        $sendToFriendModel = Mage::getModel('sendfriend/sendfriend');
        Mage::register('send_to_friend_model', $sendToFriendModel);
    }

    public function viewAction()
    {
        if ($this->_initProduct()) {
            $this->_initSendToFriendModel();

            $product = Mage::registry('product');
            if (!Mage::helper('catalog/product')->canShow($product)) {
                /**
                 * @todo Change Group Store switcher
                 */
                if (isset($_GET['store'])) {
                    $this->_redirect(null);
                    return;
                }
                $this->_forward('noRoute');
                return;
            }

            Mage::dispatchEvent('catalog_product_view', array('product'=>$product));

            $update = $this->getLayout()->getUpdate();
            $update->addHandle('default');
            $this->addActionLayoutHandles();

            $update->addHandle('PRODUCT_'.$product->getId());

            $this->loadLayoutUpdates();

            $update->addUpdate($product->getCustomLayoutUpdate());

            $this->generateLayoutXml()->generateLayoutBlocks();

            $currentCategory = Mage::registry('current_category');
            if ($currentCategory instanceof Mage_Catalog_Model_Category){
                $this->getLayout()->getBlock('root')
                    ->addBodyClass('categorypath-'.$currentCategory->getUrlPath())
                    ->addBodyClass('category-'.$currentCategory->getUrlKey());
            }

            if ($root = $this->getLayout()->getBlock('root')) {
                $root->addBodyClass('product-'.$product->getUrlKey());
            }

            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('tag/session');
            $this->_initLayoutMessages('checkout/session');
            $this->renderLayout();
        }
        else {
            $this->_forward('noRoute');
        }
    }

    public function galleryAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function imageAction()
    {
        $size = $this->getRequest()->getParam('size');
        try {
            if( $size ) {
                $imageFile = preg_replace("#/catalog/product/image/size/[0-9]*x[0-9]*#", '', $this->getRequest()->getRequestUri());
            } else {
                $imageFile = preg_replace("#/catalog/product/image#", '', $this->getRequest()->getRequestUri());
            }

            $imageModel = Mage::getModel('catalog/product_image');
            $imageModel->setSize($size)
                ->setBaseFile($imageFile)
                ->resize()
                ->setWatermark( Mage::getStoreConfig('catalog/watermark/image') )
                ->saveFile()
                ->push();
        } catch( Exception $e ) {
            $this->norouteAction();
        }
    }
}