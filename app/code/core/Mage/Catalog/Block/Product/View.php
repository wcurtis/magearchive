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
 * Product View block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @module     Catalog
 */
class Mage_Catalog_Block_Product_View extends Mage_Catalog_Block_Product_Abstract
{

    protected function _construct()
    {
        parent::_construct();
    }

    protected function _prepareLayout()
    {

    	if ($headBlock = $this->getLayout()->getBlock('head')) {
            if ($title = $this->getProduct()->getMetaTitle()) {
                $headBlock->setTitle($title.' '.Mage::getStoreConfig('catalog/seo/title_separator').' '.Mage::getStoreConfig('system/store/name'));
            }/*
            else {
                $headBlock->setTitle($this->getProduct()->getName());
            }*/

            if ($keyword = $this->getProduct()->getMetaKeyword()) {
                $headBlock->setKeywords($keyword);
            }
            if ($description = $this->getProduct()->getMetaDescription()) {
                $headBlock->setDescription($description);
            }
        }
        $this->getLayout()->createBlock('catalog/breadcrumbs');
        return parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    protected function _prepareData()
    {
        $product = $this->getProduct();

        $groupCollection = $product->getSuperGroupProducts()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('sku')
            ->addAttributeToSort('position', 'asc')
            ->useProductItem();

        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($groupCollection);
        return $this;
    }

    /**
     * Retrieve current product model
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }

    public function getAdditionalData()
    {
        $data = array();
        $product = $this->getProduct();
        //print_r($product->getData());
        //die;
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if ($product->getSuperAttributesIds() && in_array($attribute->getAttributeId(), $product->getSuperAttributesIds())) {
                continue;
            }
            if ($attribute->getIsVisibleOnFront() && $attribute->getIsUserDefined()) {

                $value = $attribute->getFrontend()->getValue($product);
                if (strlen($value)) {
                    $data[$attribute->getAttributeCode()] = array(
                       'label' => $this->__($attribute->getFrontend()->getLabel()),
                       'value' => $attribute->getFrontend()->getValue($product)//$product->getData($attribute->getAttributeCode())
                    );
                }
            }
        }
        return $data;
    }


    public function getGalleryImages()
    {
        $collection = $this->getProduct()->getGallery();
        return $collection;
    }

    public function getTierPrices($product)
    {
        $prices = $product->getFormatedTierPrice();
        $res = array();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                if ($product->getPrice() != $product->getFinalPrice()) {
                    if ($price['price']<$product->getFinalPrice()) {
                        $price['savePercent'] = ceil(100 - (( 100 / $product->getFinalPrice() ) * $price['price'] ));
                        $res[] = $price;
                    }
                }
                else {
                    $price['savePercent'] = ceil(100 - (( 100 / $product->getPrice() ) * $price['price'] ));
                    $res[] = $price;
                }
            }
        }
        return $res;
    }

    public function getGalleryUrl($image=null)
    {
        $params = array('id'=>$this->getProduct()->getId());
        if ($image) {
            $params['image'] = $image->getValueId();
            return $this->getUrl('*/*/gallery', $params);
        }
        return $this->getUrl('*/*/gallery', $params);
    }

    public function getAlertHtml($type)
    {
        return $this->getLayout()->createBlock('customeralert/alerts')
            ->setAlertType($type)
            ->toHtml();
    }

    public function getMinimalQty($product)
    {
        if ($stockItem = $product->getStockItem()) {
            return $stockItem->getMinSaleQty()>1 ? $stockItem->getMinSaleQty()*1 : null;
        }
        return null;
    }


	public function canEmailToFriend()
	{
	    $sendToFriendModel = Mage::registry('send_to_friend_model');
	    return $sendToFriendModel->canEmailToFriend();
	}
}