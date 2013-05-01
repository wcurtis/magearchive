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
 * Catalog category model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Entity_Category extends Mage_Catalog_Model_Entity_Abstract
{
    /**
     * Category tree object
     *
     * @var Varien_Data_Tree_Db
     */
    protected $_tree;

    /**
     * Catalog products table name
     *
     * @var string
     */
    protected $_categoryProductTable;

    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->setType('catalog_category')
            ->setConnection(
                $resource->getConnection('catalog_read'),
                $resource->getConnection('catalog_write')
            );
        $this->_categoryProductTable = Mage::getSingleton('core/resource')->getTableName('catalog/category_product');
    }

    /**
     * Retrieve category tree object
     *
     * @return Varien_Data_Tree_Db
     */
    protected function _getTree()
    {
        if (!$this->_tree) {
            $this->_tree = Mage::getResourceModel('catalog/category_tree')
                ->load();
        }
        return $this->_tree;
    }

    protected function _afterDelete(Varien_Object $object){
        parent::_afterDelete($object);
        $node = $this->_getTree()->getNodeById($object->getId());
        $path = $this->_getTree()->getPath($object->getId());

        $this->_getTree()->removeNode($node);
        $this->_updateCategoryPath($object, $path);
        return $this;
    }

    protected function _beforeSave(Varien_Object $object)
    {
        parent::_beforeSave($object);
        if ($object->getParentId()) {
            $parentNode = $this->_getTree()->getNodeById($object->getParentId());

            if (!$object->getId()) {
                $node = $this->_getTree()->appendChild(array(), $parentNode);
                $object->setId($node->getId());
            }
        }
        return $this;
    }

    protected function _afterSave(Varien_Object $object)
    {
//        if (!$object->getNotUpdateDepends()) {
            parent::_afterSave($object);
//        }
        //$this->_saveInStores($object);

        $this->_saveCategoryProducts($object)
            ->_updateCategoryPath($object, $this->_getTree()->getPath($object->getId()));

        return $this;
    }

    protected function _saveInStores(Varien_Object $object)
    {
        if (!$object->getMultistoreSaveFlag()) {
            $stores = $object->getStoreIds();
            foreach ($stores as $storeId) {
                if ($object->getStoreId() != $storeId) {
                	$newObject = clone $object;
                	$newObject->setStoreId($storeId)
                	   ->setMultistoreSaveFlag(true)
                	   ->save();
                }
            }
        }
        return $this;
    }

    /**
     * save category products
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Model_Entity_Category
     */
    protected function _saveCategoryProducts($category)
    {
        $products = $category->getPostedProducts();
        if (!is_null($products)) {
            $oldProducts = $category->getProductsPosition();
            if (!empty($oldProducts)) {
                $this->getWriteConnection()->delete($this->_categoryProductTable,
                    $this->getWriteConnection()->quoteInto('product_id in(?)', array_keys($oldProducts)) . ' AND ' .
                    $this->getWriteConnection()->quoteInto('category_id=?', $category->getId())
                );
            }

            foreach ($products as $productId => $productPosition) {
                if (!intval($productId)) {
                    continue;
                }
            	$data = array(
            	   'category_id'   => $category->getId(),
            	   'product_id'    => $productId,
            	   'position'      => $productPosition
            	);
            	$this->getWriteConnection()->insert($this->_categoryProductTable, $data);
            }
        }
        return $this;
    }

    protected function _updateCategoryPath($category, $path)
    {
        if ($category->getNotUpdateDepends()) {
            return $this;
        }
        foreach ($path as $pathItem) {
            if ($pathItem->getId()>1 && $category->getId() != $pathItem->getId()) {
                $category = Mage::getModel('catalog/category')
                    ->load($pathItem->getId())
                    ->save();
            }
        }
        return $this;
    }

    protected function _insertAttribute($object, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $value, $storeIds = array())
    {
        return parent::_insertAttribute($object, $attribute, $value, $object->getStoreIds());
    }

    public function getStoreIds($category)
    {
        if (!$category->getId()) {
            return array();
        }

        $nodePath = $this->_getTree()
            ->getNodeById($category->getId())
                ->getPath();
        $nodes = array();
        foreach ($nodePath as $node) {
        	$nodes[] = $node->getId();
        }

        $stores = array_keys(Mage::getConfig()->getStoresConfigByPath('catalog/category/root_id', $nodes));
        $entityStoreId = $this->getStoreId();
        if (!in_array($entityStoreId, $stores)) {
            array_unshift($stores, $entityStoreId);
        }
        if (!in_array(0, $stores)) {
            array_unshift($stores, 0);
        }
        return $stores;
    }

    /**
     * Retrieve category product id's
     *
     * @param   Mage_Catalog_Model_Category $category
     * @return  array
     */
    public function getProductsPosition($category)
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->joinField('store_id',
                'catalog/product_store',
                'store_id',
                'product_id=entity_id',
                '{{table}}.store_id='.(int) $category->getStoreId())
            ->joinField('category_id',
                'catalog/category_product',
                'category_id',
                'product_id=entity_id',
                null)
            ->joinField('position',
                'catalog/category_product',
                'position',
                'product_id=entity_id',
                '{{table}}.category_id='.(int) $category->getId(),
                'left')
            ->addFieldToFilter('category_id', $category->getId())
            ->load();

        $products = array();
        foreach ($collection as $product) {
        	$products[$product->getId()] = $product->getPosition();
        }
        return $products;
    }

    public function move(Mage_Catalog_Model_Category $category, $newParentId)
    {
        $oldStoreId = $category->getStoreId();
        $parent = Mage::getModel('catalog/category')
            ->setStoreId($category->getStoreId())
            ->load($category->getParentId());

        $newParent = Mage::getModel('catalog/category')
            ->setStoreId($category->getStoreId())
            ->load($newParentId);

        $oldParentStores = $parent->getStoreIds();
        $newParentStores = $newParent->getStoreIds();

        $category->setParentId($newParentId)
            ->save();
        $parent->save();
        $newParent->save();

        // Add to new stores
        $addToStores = array_diff($newParentStores, $oldParentStores);
        foreach ($addToStores as $storeId) {
        	$newCategory = clone $category;
        	$newCategory->setStoreId($storeId)
        	   ->save();
            $children = $category->getAllChildren();

            if ($children && $arrChildren = explode(',', $children)) {
                foreach ($arrChildren as $childId) {
                    if ($childId == $category->getId()) {
                        continue;
                    }

                	$child = Mage::getModel('catalog/category')
                	   ->setStoreId($oldStoreId)
                	   ->load($childId)
                	   ->setStoreId($storeId)
                	   ->save();
                }
            }
        }
        return $this;
    }
}
