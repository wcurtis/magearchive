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
 * Catalog category
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Category extends Varien_Object
{
    /**
     * Category display modes
     */
    const DM_PRODUCT        = 'PRODUCTS';
    const DM_PAGE           = 'PAGE';
    const DM_MIXED          = 'PRODUCTS_AND_PAGE';

    protected static $_url;
    protected static $_urlRewrite;

    private $_designAttributes;

    public function __construct()
    {
        $this->_designAttributes = array(
            'custom_design',
            'custom_design_apply',
            'custom_design_from',
            'custom_design_to',
            'page_layout',
            'custom_layout_update');

        parent::__construct();
        $this->setIdFieldName($this->getResource()->getEntityIdField());
    }

    public function getUrlInstance()
    {
        if (!self::$_url) {
            self::$_url = Mage::getModel('core/url');
        }
        return self::$_url;
    }

    /**
    * @return Mage_Core_Model_Url_Rewrite
    */
    public function getUrlRewrite()
    {
        if (!self::$_urlRewrite) {
            self::$_urlRewrite = Mage::getModel('core/url_rewrite');
        }
        return self::$_urlRewrite;
    }

    /**
     * Retrieve category resource model
     *
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function getResource()
    {
        return Mage::getResourceSingleton('catalog/category');
    }

    /**
     * Retrieve category tree model
     *
     * @return unknown
     */
    public function getTreeModel()
    {
        return Mage::getResourceModel('catalog/category_tree');
    }

    /**
     * Set category and resource model store id
     *
     * @param unknown_type $storeId
     * @return unknown
     */
    public function setStoreId($storeId)
    {
        $this->getResource()->setStore($storeId);
        $this->setData('store_id', $storeId);
        return $this;
    }

    /**
     * Retrieve category store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getResource()->getStoreId();
    }

    /**
     * Load category data
     *
     * @param   int $categoryId
     * @return  Mage_Catalog_Model_Category
     */
    public function load($categoryId)
    {
        $this->getResource()->load($this, $categoryId);
        return $this;
    }

    /**
     * Save category
     *
     * @return Mage_Catalog_Model_Category
     */
    public function save()
    {
        $this->getResource()->save($this);
        return $this;
    }

    /**
     * Delete category
     *
     * @return Mage_Catalog_Model_Category
     */
    public function delete()
    {
        $this->getResource()->delete($this);
        return $this;
    }

    /**
     * Move category
     *
     * @return Mage_Catalog_Model_Category
     */
    public function move($parentId)
    {
        $this->getResource()->move($this, $parentId);
        return $this;
    }

    public function getCollection()
    {
        return Mage::getResourceModel('catalog/category_collection');
    }

    /**
     * Retrieve default attribute set id
     *
     * @return int
     */
    public function getDefaultAttributeSetId()
    {
        return $this->getResource()->getConfig()->getDefaultAttributeSetId();
    }

    /**
     * Get category products collection
     *
     * @return Varien_Data_Collection_Db
     */
    public function getProductCollection()
    {
        $collection = Mage::getResourceModel('catalog/product_collection');
            //->addCategoryFilter($this->getId());
        return $collection;
    }

    /**
     * Retrieve all customer attributes
     *
     * @return array
     */
    public function getAttributes($noDesignAttributes = false)
    {
        $result = $this->getResource()
            ->loadAllAttributes($this)
            ->getAttributesByCode();

        if ($noDesignAttributes){
            foreach ($result as $k=>$a){
                if (in_array($k, $this->_designAttributes)) {
                    unset($result[$k]);
                }
            }
        }

        return $result;
    }

    /**
     * Retrieve array of product id's for category
     *
     * array($productId => $position)
     *
     * @return array
     */
    public function getProductsPosition()
    {
        if (!$this->getId()) {
            return array();
        }

        $arr = $this->getData('products_position');
        if (is_null($arr)) {
            $arr = $this->getResource()->getProductsPosition($this);
            $this->setData('products_position', $arr);
        }
        return $arr;
    }

    /**
     * Retrieve array of store ids for category
     *
     * @return array
     */
    public function getStoreIds()
    {
        if ($this->getInitialSetupFlag()) {
            return array();
        }

        if ($storeIds = $this->getData('store_ids')) {
            return $storeIds;
        }
        $storeIds = $this->getResource()->getStoreIds($this);
        $this->setData('store_ids', $storeIds);
        return $storeIds;
    }


    public function getLayoutUpdateHandle()
    {
        $layout = 'catalog_category_';
        if ($this->getIsAnchor()) {
            $layout.= 'layered';
        }
        else {
            $layout.= 'default';
        }
        return $layout;
    }

    /**
     * Get category url
     *
     * @return string
     */
    public function getCategoryUrl()
    {
        Varien_Profiler::start('REWRITE: '.__METHOD__);
        $rewrite = $this->getUrlRewrite();
        if ($this->getStoreId()) {
            $rewrite->setStoreId($this->getStoreId());
        }
        $idPath = 'category/'.$this->getId();

        $rewrite->loadByIdPath($idPath);

        if ($rewrite->getId()) {
            $url = $this->getUrlInstance()->getBaseUrl().$rewrite->getRequestPath();
        Varien_Profiler::stop('REWRITE: '.__METHOD__);
            return $url;
        }
        Varien_Profiler::stop('REWRITE: '.__METHOD__);

        $url = $this->getCategoryIdUrl();

        return $url;
    }

    public function getCategoryIdUrl()
    {
        Varien_Profiler::start('REGULAR: '.__METHOD__);
        $urlKey = $this->getUrlKey() ? $this->getUrlKey() : $this->formatUrlKey($this->getName());
        $url = $this->getUrlInstance()->getUrl('catalog/category/view', array(
            's'=>$urlKey,
            'id'=>$this->getId(),
        ));
        Varien_Profiler::stop('REGULAR: '.__METHOD__);
        return $url;
    }

    public function formatUrlKey($str)
    {
        $str = Mage::helper('core')->removeAccents($str);
    	$urlKey = preg_replace('#[^0-9a-z]+#i', '-', $str);
    	$urlKey = strtolower($urlKey);
    	$urlKey = trim($urlKey, '-');

    	return $urlKey;
    }

    public function getImageUrl()
    {
        $url = false;
        if ($image = $this->getImage()) {
            $url = Mage::getBaseUrl('media').'catalog/category/'.$image;
        }
        return $url;
    }

    public function getUrlPath()
    {
        if ($path = $this->getData('url_path')) {
            return $path;
        }

        $path = $this->getUrlKey();

        if ($this->getParentId()) {
            $parentPath = Mage::getModel('catalog/category')->load($this->getParentId())->getCategoryPath();
            $path = $parentPath.'/'.$path;
        }

        $this->setUrlPath($path);

        return $path;
    }

    public function getParentCategory()
    {
        return Mage::getModel('catalog/category')->load($this->getParentId());
    }

    public function getCustomDesignDate()
    {
        $result = array();
        $result['from'] = $this->getData('custom_design_from');
        $result['to'] = $this->getData('custom_design_to');

        return $result;
    }

    public function getDesignAttributes()
    {
        $result = array();
        foreach ($this->_designAttributes as $attrName) {
            $result[] = $this->_getAttribute($attrName);
        }
        return $result;
    }

    private function _getAttribute($attributeCode)
    {
        return $this->getResource()
            ->getAttribute($attributeCode);
    }
}
