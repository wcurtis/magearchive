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
 * Category collection
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Entity_Category_Collection extends Mage_Catalog_Model_Entity_Collection_Abstract
{
    protected $_productTable;
    protected $_productStoreId;
    protected $_storeTable;

    protected $_loadWithProductCount = false;

    public function __construct()
    {
        $this->setEntity(Mage::getResourceSingleton('catalog/category'));
        $this->setObject('catalog/category');

        $this->_storeTable   = Mage::getSingleton('core/resource')->getTableName('catalog/product_store');
        $this->_productTable = Mage::getSingleton('core/resource')->getTableName('catalog/category_product');
    }

    public function addIdFilter($categoryIds)
    {
        if (is_array($categoryIds)) {
            $condition = array('in' => $categoryIds);
        }
        elseif (is_numeric($categoryIds)) {
            $condition = $categoryIds;
        }
        elseif (is_string($categoryIds)) {
        	$ids = explode(',', $categoryIds);
        	if (empty($ids)) {
        	    $condition = $categoryIds;
        	}
        	else {
        	    $condition = array('in' => $ids);
        	}
        }

        $this->addFieldToFilter('entity_id', $condition);
        return $this;
    }

    public function setLoadProductCount($flag)
    {
        $this->_loadWithProductCount = $flag;
        return $this;
    }

    public function setProductStoreId($storeId)
    {
        $this->_productStoreId = $storeId;
        return $this;
    }

    public function getProductStoreId()
    {
        if (is_null($this->_productStoreId)) {
            $this->_productStoreId = $this->getEntity()->getStoreId();
        }
        return $this->_productStoreId;
    }

    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->_loadWithProductCount) {
            $this->addAttributeToSelect('all_children');
            $this->addAttributeToSelect('is_anchor');
        }

        parent::load($printQuery, $logQuery);

        if ($this->_loadWithProductCount) {
            $this->_loadProductCount();
        }

        return $this;
    }

    /**
     * Load categories product count
     *
     * @return this
     */
    protected function _loadProductCount()
    {
        $anchor     = array();
        $regular    = array();
        foreach ($this as $item) {
        	if ($item->getIsAnchor()) {
        	    $anchor[$item->getId()] = $item;
        	}
        	else {
        	    $regular[$item->getId()] = $item;
        	}
        }

        // Retrieve regular categories product counts
        $regularIds = array_keys($regular);
        if (!empty($regularIds)) {
            $select = $this->_read->select();
            $select->from($this->_productTable,
                    array('category_id', new Zend_Db_Expr('COUNT('.$this->_productTable.'.product_id)'))
                )
                ->join($this->_storeTable, $this->_storeTable.'.product_id='.$this->_productTable.'.product_id')
                ->where($this->_read->quoteInto($this->_productTable.'.category_id IN(?)', $regularIds))
                ->where($this->_read->quoteInto($this->_storeTable.'.store_id=?', $this->getProductStoreId()))
                ->group($this->_productTable.'.category_id');
            $counts = $this->_read->fetchPairs($select);
            foreach ($regular as $item) {
            	if (isset($counts[$item->getId()])) {
            	    $item->setProductCount($counts[$item->getId()]);
            	}
            	else {
            	    $item->setProductCount(0);
            	}
            }
        }
        // Retrieve Anchor categories product counts
        foreach ($anchor as $item) {
            if ($allChildren = $item->getAllChildren()) {
                $select = $this->_read->select();
                $select->from($this->_productTable, new Zend_Db_Expr('COUNT( DISTINCT '.$this->_productTable.'.product_id)'))
                    ->join($this->_storeTable, $this->_storeTable.'.product_id='.$this->_productTable.'.product_id')
                    ->where($this->_read->quoteInto($this->_productTable.'.category_id IN(?)', explode(',', $item->getAllChildren())))
                    ->where($this->_read->quoteInto($this->_storeTable.'.store_id=?', $this->getProductStoreId()))
                    ->group($this->_storeTable.'.store_id');

                $item->setProductCount((int) $this->_read->fetchOne($select));
            }
            else {
                $item->setProductCount(0);
            }
        }
        return $this;
    }
}
