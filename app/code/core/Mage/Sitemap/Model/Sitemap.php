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
 * Catalog product
 *
 * @category   Mage
 * @package    Mage_Sitemap
 */
class Mage_Sitemap_Model_Sitemap extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('sitemap/sitemap');
    }

    public function generateSitemap() {

        $storeId = $this->getStoreId();

        $simplexml = new Varien_Simplexml_Element('<?xml version="1.0" encoding="UTF-8"?><urlset></urlset>');

        $simplexml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $categories = Mage::getModel('catalog/category')
            ->setStoreId($storeId)
            ->getCollection()
            ->addAttributeToSelect('*')

            ->load();

        foreach ($categories as $category){
            $category = Mage::getModel('catalog/category')
                ->load($category->getId());
//var_dump($category);
            if (!$category->getIsActive()) {
            	continue;
            }

            //$category->setStoreId($storeId);

            $url = $simplexml->addChild('url');

            $url->addChild('loc', $category->getCategoryUrl());
            $url->addChild('lastmod', date('Y-m-d'));
            $url->addChild('changefreq', Mage::getStoreConfig('sitemap/category/changefreq'));
            $url->addChild('priority', Mage::getStoreConfig('sitemap/category/priority'));
        }

        $products = Mage::getModel('catalog/product')
            ->setStoreId($storeId)
            ->getCollection()
            ->addAttributeToSelect('*');

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        $products->load();

        foreach ($products as $product){
            $product = Mage::getModel('catalog/product')
                ->load($product->getId());


            //$product->setStoreId($storeId);

            $url = $simplexml->addChild('url');

            $url->addChild('loc', $product->getProductUrl());
            $url->addChild('lastmod', date('Y-m-d'));
            $url->addChild('changefreq', Mage::getStoreConfig('sitemap/product/changefreq'));
            $url->addChild('priority', Mage::getStoreConfig('sitemap/product/priority'));
        }

        $pages = Mage::getModel('cms/page')
            ->setStoreId($storeId)
            ->getCollection();

        foreach ($pages as $page) {
             $page = Mage::getModel('cms/page')
        	     ->load($page->getId());

           // $page->setStoreId($storeId);

            $url = $simplexml->addChild('url');

            $url->addChild('loc', Mage::getBaseUrl() . $page->getIdentifier());
            $url->addChild('lastmod', date('Y-m-d'));
            $url->addChild('changefreq', Mage::getStoreConfig('sitemap/page/changefreq'));
            $url->addChild('priority', Mage::getStoreConfig('sitemap/page/priority'));
        }

        $this->setSitemapTime(now());
        $this->save();

        return $simplexml->asXml();
    }

     /**
     * Save sitemap
     *
     * @return Mage_Catalog_Model_Product
     */
    public function save()
    {
        $this->getResource()->save($this);
        return $this;
    }

    public function getResource()
    {
        return Mage::getResourceSingleton('sitemap/sitemap');
    }

}