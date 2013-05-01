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
 * Catalog url model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Url
{
    /**
     * Stores configuration
     *
     * @var array
     */
    protected $_stores;

    /**
     * Category root ids for each store
     *
     * @var array
     */
    protected $_rootIds;

    /**
     * URL Rewrites by store_id and id_path
     *
     * @var array
     */
    protected $_rewrites;

    /**
     * Categories cache by store_id
     *
     * @var array
     */
    protected $_categories;

    /**
     * Products cache by store_id
     *
     * @var array
     */
    protected $_products;

    /**
     * URL Rewrites by store_id and request_path
     *
     * @var array
     */
    protected $_paths;

    /**
     * Is loaded url rewrites by store id
     *
     * @var array
     */
    protected $_rewritesIsLoaded = array();

    /**
     * Is loaded categories cache by store id
     *
     * @var array
     */
    protected $_categoriesIsLoaded = array();

    /**
     * Is loaded products cache by store id
     *
     * @var array
     */
    protected $_productsIsLoaded = array();

    /**
     * Load url rewrites from core_url_rewrite table
     *
     * @param int $storeId
     * @return Mage_Catalog_Model_Url
     */
    public function loadRewrites($storeId)
    {
        if (!empty($this->_rewritesIsLoaded[$storeId])) {
            return $this;
        }

        $rewriteCollection = Mage::getResourceModel('core/url_rewrite_collection');
        $rewriteCollection->getSelect()
            ->where("id_path like 'category/%' or id_path like 'product/%'")
            ->where("store_id=?", $storeId);
        $rewriteCollection->load();

        $this->_rewrites[$storeId] = array();
        foreach ($rewriteCollection as $rewrite) {
            // store rewrites by idPath
            $this->_rewrites[$rewrite->getStoreId()][$rewrite->getIdPath()] = $rewrite;
            // store rewrites by requestPath
            $this->_paths[$rewrite->getStoreId()][$rewrite->getRequestPath()] = $rewrite->getIdPath();
        }

        $this->_rewritesIsLoaded[$storeId] = true;

        return $this;
    }

    /**
     * Get requestPath that was not used yet.
     *
     * Will try to get unique path by adding -1 -2 etc. between url_key and optional url_suffix
     *
     * @param int $storeId
     * @param string $requestPath
     * @param string $idPath
     * @return string
     */
    public function getUnusedPath($storeId, $requestPath, $idPath=null)
    {
        // repeat while supplied request_path already been used
        while (isset($this->_paths[$storeId][$requestPath])) {
            // if id_path was supplied and it matches cached request_path, continue with this request_path
            if (!is_null($idPath) && $this->_paths[$storeId][$requestPath]===$idPath) {
                break;
            }
            // retrieve url_suffix for product urls
            $productUrlSuffix = (string)$this->getStoreConfig($storeId)->catalog->seo->product_url_suffix;
            // match request_url abcdef1234(-12)(.html) pattern
            if (!preg_match('#^([0-9a-z/-]+?)(-([0-9]+))?('.preg_quote($productUrlSuffix).')?$#i', $requestPath, $m)) {
                // if doesn't match can't do much about it
                break;
            }
            // change request_path to make it unique
            $requestPath = $m[1].(isset($m[3])?'-'.($m[3]+1):'-1').(isset($m[4])?$m[4]:'');
            // continue until unique request_path found
        }
        // store request_path in cache
        $this->_paths[$storeId][$requestPath] = $idPath;
        return $requestPath;
    }

    /**
     * Load Categories cache
     *
     * @param integer $storeId
     * @return Mage_Catalog_Model_Url
     */
    public function loadCategories($storeId)
    {
        if (!empty($this->_categoryIsLoaded[$storeId])) {
            return $this;
        }

        $categoryCollection = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('children')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url_path');
        $categoryCollection->getEntity()
            ->setStore($storeId);
        $categoryCollection->load();

        $this->_categories = array();
        foreach ($categoryCollection as $category) {
            $this->_categories[$storeId][$category->getId()] = $category;
        }

        $this->_categoryIsLoaded[$storeId] = true;

        return $this;
    }

    /**
     * Load Products cache
     *
     * @param integer $storeId
     * @return Mage_Catalog_Model_Url
     */
    public function loadProducts($storeId)
    {
        if (!empty($this->_productsIsLoaded[$storeId])) {
            return $this;
        }
        $productCollection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name');
        $productCollection->getEntity()
            ->setStore($storeId);
        $productCollection->load();

        $this->_products[$storeId] = $productCollection->getItems();

        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('catalog_read');
        $productStoreTable = $resource->getTableName('catalog/product_store');
        $categoryProductTable = $resource->getTableName('catalog/category_product');

        $select = $read->select()
            ->from(array('cp'=>$categoryProductTable))
            ->join(array('ps'=>$productStoreTable), 'ps.product_id=cp.product_id', array())
            ->where('ps.store_id=?', $storeId);

        $categoryProducts = $read->fetchAll($select);
        foreach ($categoryProducts as $row) {
            $category = $this->getCategory($storeId, $row['category_id']);
            $product = $this->getProduct($storeId, $row['product_id']);
            if (!$category || !$product) {
                continue;
            }
            $products = $category->getProducts();
            $products[$product->getId()] = $product;
            $category->setProducts($products);

            $categories = $product->getCategories();
            $categories[$category->getId()] = $category;
            $product->setCategories($categories);
        }

        $this->_productsIsLoaded[$storeId] = true;

        return $this;
    }

    /**
     * Get store config simplexml node
     *
     * @param integer $storeId
     * @return Mage_Core_Model_Config_Element
     */
    public function getStoreConfig($storeId=null)
    {
        if (!$this->_stores) {
            foreach (Mage::getConfig()->getNode('stores')->children() as $storeNode) {
                $sId = (int)$storeNode->system->store->id;
                $rId = $storeNode;
                if ($sId==0) {
                    continue;
                }
                $this->_stores[$sId] = $rId;
            }
        }
        if (is_null($storeId)) {
            return $this->_stores;
        }

        return isset($this->_stores[$storeId]) ? $this->_stores[$storeId] : null;
    }

    /**
     * Get root category id for the store
     *
     * @param integer $storeId
     * @return integer|array
     */
    public function getRootId($storeId=null)
    {
        if (!$this->_rootIds) {
            $this->_rootIds = array();
            $collection = Mage::getModel('core/store')
                ->getCollection()
                ->addRootCategoryIdAttribute();
            foreach ($collection as $store) {
                $this->_rootIds[$store->getId()] = $store->getRootCategoryId();
            }
        }
        if (is_null($storeId)) {
            return $this->_rootIds;
        } else {
            return isset($this->_rootIds[$storeId]) ? $this->_rootIds[$storeId] : null;
        }
    }

    /**
     * Get rewrite object by id_path
     *
     * @param integer $storeId
     * @param string $idPath
     * @return Mage_Core_Model_Url_Rewrite
     */
    public function getRewrite($storeId, $idPath=null)
    {
        if (is_null($idPath)) {
            return isset($this->_rewrites[$storeId]) ? $this->_rewrites[$storeId] : null;
        }
        if (!isset($this->_rewrites[$storeId][$idPath])) {
            $rewrite = Mage::getModel('core/url_rewrite')->setStoreId($storeId)->loadByIdPath($idPath);
            $this->_rewrites[$storeId][$idPath] = $rewrite->getId() ? $rewrite : false;
        }
        return $this->_rewrites[$storeId][$idPath];
    }

    public function saveRewrite(Mage_Core_Model_Url_Rewrite $rewrite)
    {
        if (!$rewrite->getId()) {
            $old = Mage::getModel('core/url_rewrite')->setStoreId($rewrite->getStoreId())
                ->loadByIdPath($rewrite->getIdPath());
            if (!$old) {
                $old->loadByRequestPath($rewrite->getRequestPath());
            }
            if ($old) {
                $rewrite->setId($old->getId());
            }
        }
        $rewrite->save();

        $this->_rewrites[$rewrite->getStoreId()][$rewrite->getIdPath()] = $rewrite;
        $this->_paths[$rewrite->getStoreId()][$rewrite->getRequestPath()] = $rewrite->getIdPath();

        return $this;
    }

    /**
     * Get category object
     *
     * @param integer $storeId
     * @param integer|null $categoryId
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory($storeId, $categoryId=null)
    {
        if (is_null($categoryId)) {
            return isset($this->_categories[$storeId]) ? $this->_categories[$storeId] : null;
        }
        if (!isset($this->_categories[$storeId][$categoryId])) {
            $category = Mage::getModel('catalog/category')->setStoreId($storeId)->load($categoryId);
            $this->_categories[$storeId][$categoryId] = $category->getId() ? $category : false;
        }
        return $this->_categories[$storeId][$categoryId];
    }

    /**
     * Get product object
     *
     * @param integer $storeId
     * @param integer|null $productId
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct($storeId, $productId=null)
    {
        if (is_null($productId)) {
            return $this->_products[$storeId];
        }
        if (!isset($this->_products[$storeId][$productId])) {
            $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
            $this->_products[$storeId][$productId] = $product->getId() ? $product : false;
        }
        return $this->_products[$storeId][$productId];
    }

    /**
     * Refresh URL rewrites
     *
     * If $storeId is null will go over all the stores
     * If $parentId is null will start from root category id for the store
     *
     * @param integer|null $storeId
     * @param integer|null $parentId
     * @return Mage_Catalog_Model_Url
     */
    public function refreshRewrites($storeId=null, $categoryId=null, $parentPath=null)
    {
        if (is_null($storeId)) {
            foreach ($this->getRootId() as $storeId=>$rootId) {
                if ($storeId==0 || $rootId==0) {
                    continue;
                }
                $this->loadRewrites($storeId);
                if (empty($this->_rewrites[$storeId]) && !is_null($categoryId)) {
                    $categoryId = null;
                }
                $this->loadCategories($storeId);
                $this->loadProducts($storeId);
                $this->refreshRewrites($storeId, $categoryId, $parentPath);
            }
            return $this;
        }

        $categoryPath = '';
        if (is_null($categoryId)) {
            $products = $this->getProduct($storeId);
            if ($products) {
                foreach ($products as $productId=>$product) {
                    $this->refreshProductRewrites($storeId, $product);
                }
            }
            $category = $this->getCategory($storeId, $this->getRootId($storeId));
        } else {
            $category = $this->getCategory($storeId, $categoryId);
            if (!$category) {
                return $this;
            }
            $this->refreshCategoryRewrites($storeId, $category, $parentPath);
            if ($categoryId!=$this->getRootId($storeId)) {
                $categoryPath = $category->getUrlPath().'/';
            }
        }

        if (($category instanceof Mage_Catalog_Model_Category) && $category->getChildren()) {
            foreach (explode(',', $category->getChildren()) as $childId) {
                $category = $this->getCategory($storeId, $childId);
                $this->refreshRewrites($storeId, $childId, $categoryPath);
            }
        }

        return $this;
    }

    /**
     * Refresh URL rewrites for a category
     *
     * @param integer $storeId
     * @param Mage_Catalog_Model_category $category
     * @param string $parentPath
     * @return Mage_Catalog_Model_Url
     */
    public function refreshCategoryRewrites($storeId, $category, $parentPath=null)
    {
        if (''==$category->getUrlKey()) {
            if ($category->getName()) {
                $category->setUrlKey($category->formatUrlKey($category->getName()));
            } else {
                return $this;
            }
        } else {
            $category->setUrlKey($category->formatUrlKey($category->getUrlKey()));
        }
        if (is_null($parentPath)) {
            $parent = $this->getCategory($storeId, $category->getParentId());
            if (!$parent || $parent->getId()==$this->getRootId($storeId)) {
                $parentPath = '';
            } else {
                $parentPath = rtrim($parent->getUrlPath(),'/').'/';
            }
        }

        $idPath = 'category/'.$category->getId();
        $targetPath = 'catalog/category/view/id/'.$category->getId();
        $categoryPath = $parentPath.$category->getUrlKey();
        $categoryPath = $this->getUnusedPath($storeId, $categoryPath, $idPath);
        $update = false;
        $rewrite = $this->getRewrite($storeId, $idPath);

        if ($rewrite) {
            $update = $rewrite->getRequestPath() !== $categoryPath;
        } else {
            $rewrite = Mage::getModel('core/url_rewrite')
                ->setStoreId($storeId)
                ->setIdPath($idPath)
                ->setTargetPath($targetPath);
            $update = true;
        }
        if ($rewrite) {
            $rewrite->setType(Mage_Core_Model_Url_Rewrite::TYPE_CATEGORY);
        }
        if ($update) {
            $category->setUrlPath($categoryPath);

            $category->getResource()->saveAttribute($category, 'url_key');
            $category->getResource()->saveAttribute($category, 'url_path');

            $this->saveRewrite($rewrite->setRequestPath($categoryPath));
        }

        $products = $category->getProducts();
        if ($products) {
            foreach ($products as $productId=>$product) {
                $this->refreshProductRewrites($storeId, $product, $category);
            }
        }
        return $this;
    }

    /**
     * Refresh URL rewrites for a product
     *
     * @param integer $storeId
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Category|boolean $category
     * @return Mage_Catalog_Model_Url
     */
    public function refreshProductRewrites($storeId, $product, $category=null)
    {
        if (is_null($storeId)) {
            foreach ($this->getStoreConfig() as $storeId=>$storeNode) {
                $this->loadRewrites($storeId);
/*
                if (empty($this->_rewrites[$storeId])) {
                    $this->refreshRewrites();
                    return $this;
                }
*/
                /*$this->loadCategories($storeId);
                $this->loadProducts($storeId);*/
                $this->refreshProductRewrites($storeId, $product, $category);
            }
            return $this;
        }
        if (is_numeric($product)) {
            $product = $this->getProduct($storeId, $product);
        }
        if (!$product) {
            return $this;
        }
        if (''==$product->getUrlKey()) {
            if ($product->getName()) {
                $product->setUrlKey($product->formatUrlKey($product->getName()));
            } else {
                return $this;
            }
        } else {
            $product->setUrlKey($product->formatUrlKey($product->getUrlKey()));
        }

        $idPath = 'product/'.$product->getId();
        $targetPath = 'catalog/product/view/id/'.$product->getId();
        $productPath = '';

        if ($category instanceof Mage_Catalog_Model_Category) {
            $idPath .= '/'.$category->getId();
            $targetPath .= '/category/'.$category->getId();
            $productPath = $category->getUrlPath().'/';
        }

        $productUrlSuffix = (string)$this->getStoreConfig($storeId)->catalog->seo->product_url_suffix;
        $productPath .= $product->getUrlKey().$productUrlSuffix;
        $productPath = $this->getUnusedPath($storeId, $productPath, $idPath);

        $update = false;
        $rewrite = $this->getRewrite($storeId, $idPath);
        if ($rewrite) {
            $update = $rewrite->getRequestPath() !== $productPath;
        } else {
            $rewrite = Mage::getModel('core/url_rewrite')
                ->setStoreId($storeId)
                ->setIdPath($idPath)
                ->setTargetPath($targetPath);
            $update = true;
        }

        if ($rewrite) {
            $rewrite->setType(Mage_Core_Model_Url_Rewrite::TYPE_PRODUCT); // for product
        }

        if ($update) {
            $this->saveRewrite($rewrite->setRequestPath($productPath));
        }
        if (true===$category && $product->getCategories()) {
            foreach ($product->getCategories() as $category) {
                $this->refreshProductRewrites($storeId, $product, $category);
            }
        }
        return $this;
    }
}