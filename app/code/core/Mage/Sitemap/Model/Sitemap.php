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
 * @package    Mage_Sitemap
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sitemap model
 *
 * @category   Mage
 * @package    Mage_Sitemap
 */
class Mage_Sitemap_Model_Sitemap extends Mage_Core_Model_Abstract
{

    /**
     * Init model
     */
    protected function _construct()
    {
        $this->_init('sitemap/sitemap');
    }

    /**
     * Prepare filename
     *
     * @return string
     */
    public function getPreparedFilename()
    {
        $filename = Mage::getBaseDir('base') . '/' . $this->getSitemapPath() . '/' . $this->getSitemapFilename();
        $filename = str_replace('//', '/', $filename);
        return $filename;
    }

    /**
     * Generate sitemap xml
     *
     * @return string
     */
    public function generateXml() {

        $storeId = $this->getStoreId();
        $date = date('Y-m-d');

        $simplexml = new Varien_Simplexml_Element('<?xml version="1.0" encoding="UTF-8"?><urlset></urlset>');
        $simplexml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');


        /**
         * Generate categories sitemap
         */

        $changefreq = (string)Mage::getStoreConfig('sitemap/category/changefreq');
        $priority = (string)Mage::getStoreConfig('sitemap/category/priority');

        $categories = Mage::getModel('catalog/category')
            ->setStoreId($storeId)
            ->getCollection()
            ->addAttributeToSelect('*')
            ->load();

        foreach ($categories as $category){
            $category = Mage::getModel('catalog/category')
                ->load($category->getId());
            if (!$category->getIsActive()) {
            	continue;
            }

            $url = $simplexml->addChild('url');
            $url->addChild('loc', $category->getUrl());

            $url->addChild('lastmod', $date);
            $url->addChild('changefreq', $changefreq);
            $url->addChild('priority', $priority);
        }

        /**
         * Generate products sitemap
         */

        $changefreq = (string)Mage::getStoreConfig('sitemap/product/changefreq');
        $priority = (string)Mage::getStoreConfig('sitemap/product/priority');

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

            $url = $simplexml->addChild('url');
            $url->addChild('loc', $product->getProductUrl());

            $url->addChild('lastmod', $date);
            $url->addChild('changefreq', $changefreq);
            $url->addChild('priority', $priority);
        }

        /**
         * Generate CMS pages sitemap
         */

        $changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq');
        $priority = (string)Mage::getStoreConfig('sitemap/page/priority');

        $pages = Mage::getModel('cms/page')
            ->setStoreId($storeId)
            ->getCollection();

        foreach ($pages as $page) {
             $page = Mage::getModel('cms/page')
        	     ->load($page->getId());

            $url = $simplexml->addChild('url');
            $url->addChild('loc', Mage::getBaseUrl() . $page->getIdentifier());

            $url->addChild('lastmod', $date);
            $url->addChild('changefreq', $changefreq);
            $url->addChild('priority', $priority);
        }

        // record last generation time
        $this->setSitemapTime(now());
        $this->save();

        return $simplexml->asXml();
    }

}
