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
 * @package    Mage_Review
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review controller
 *
 * @category   Mage
 * @package    Mage_Review
 */
class Mage_Review_ProductController extends Mage_Core_Controller_Front_Action
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

    public function postAction()
    {
        $productId = $this->getRequest()->getParam('id', false);
        if ($data = $this->getRequest()->getPost()) {
            $review = Mage::getModel('review/review')->setData($data);
            try {
                $review->setEntityId(1) // product
                    ->setEntityPkValue($productId)
                    ->setStatusId(2) // pending
                    ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->setStores(array(Mage::app()->getStore()->getId()))
                    ->save();

                $arrRatingId = $this->getRequest()->getParam('ratings', array());
                foreach ($arrRatingId as $ratingId=>$optionId) {
                	Mage::getModel('rating/rating')
                	   ->setRatingId($ratingId)
                	   ->setReviewId($review->getId())
                	   ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                	   ->addOptionVote($optionId, $productId);
                }

                $review->aggregate();

                Mage::getSingleton('review/session')
                    ->addSuccess(Mage::helper('review')->__('Your review has been accepted for moderation'));
            }
            catch (Exception $e){
                Mage::getSingleton('review/session')
                    ->addError(Mage::helper('review')->__('Unable to post review. Please, try again later.'));
            }
        }

        $this->_redirectReferer();
    }

    public function listAction()
    {
        $this->_initProduct();
        $productId = $this->getRequest()->getParam('id');
        if( !$productId ) {
            $this->_redirectUrl(Mage::getBaseUrl());
        }
        Mage::register('productId', $productId);

        $this->loadLayout();
        $this->_initLayoutMessages('review/session');
        $this->renderLayout();
    }

    public function viewAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('review/session');
        $this->renderLayout();
    }
}
