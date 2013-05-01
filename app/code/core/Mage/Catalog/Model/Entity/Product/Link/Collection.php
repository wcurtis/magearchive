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
 * Catalog product links collection
 *
 * @category   Mage
 * @package    Mage_Catalog
 */

class Mage_Catalog_Model_Entity_Product_Link_Collection extends Mage_Catalog_Model_Entity_Product_Collection
{
    protected $_linkAttributeCollection = null;
    protected $_linkAttributeCollectionLoaded = false;
    protected $_linkTypeId = 0;
    protected $_productId = 0;
    protected $_storeId = 0;
    protected $_isLoaded = false;

    public function __construct()
    {
        $this->setEntity(Mage::getResourceSingleton('catalog/product'))
            ->setObject('catalog/product_link');
    }

    public function getConditionForProduct()
    {
        $condition = array('link_type_id'=>$this->getLinkTypeId());

        if ($this->getProductId()) {
            $condition['product_id'] = $this->getProductId();
        }

        return $condition;
    }

    public function setProductId($productId)
    {
        $this->_productId = $productId;

        $this->_joinLinkTable();
        return $this;
    }


    public function getProductId()
    {
        if(empty($this->_productId)){
            return 0;
        }
        return $this->_productId;
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;

        return $this;
    }


    public function getStoreId()
    {
        return $this->_storeId;
    }

    public function getIsLoaded()
    {
        return $this->_isLoaded;
    }

    protected function _joinLinkTable()
    {
        $this->joinField('link_id', 'catalog/product_link', 'link_id', 'linked_product_id=entity_id', $this->getConditionForProduct(), 'left')
            ->joinField('product_id', 'catalog/product_link', 'product_id', 'link_id=link_id', null,'left')
            ->joinField('linked_product_id', 'catalog/product_link', 'linked_product_id', 'link_id=link_id', null,'left')
            ->joinField('link_type_id', 'catalog/product_link', 'link_type_id', 'link_id=link_id', null,'left')
            ->joinField('link_type', 'catalog/product_link_type', 'code', 'link_type_id=link_type_id', null,'left');
        if ($this->getProductId()) {
            $this->getSelect()->where('e.entity_id != ?', $this->getProductId());
        }
        return $this;
    }

    public function joinLinkTable()
    {
        $this->_joinLinkTable();
        return $this;
    }

    public function addLinkAttributeToSelect($code, $linkType=null)
    {
         $attribute = $this->_getLinkAttribute($code, $linkType);
         $this->joinField($code,  $this->_getLinkAttributeTable($attribute),
                          'value', 'link_id=link_id',
                          array('product_link_attribute_id'=>$attribute->getId()), 'left');
         return $this;
    }

    public function getLinkAttributeCollection()
    {
        if(is_null($this->_linkAttributeCollection)) {
            $this->_linkAttributeCollection = Mage::getResourceModel('catalog/product_link_attribute_collection');
        }

        return $this->_linkAttributeCollection;
    }

    protected function _getLinkAttribute($code, $linkType=null)
    {
        $this->_loadLinkAttributes();

        if(!($attribute = $this->getLinkAttributeCollection()->getItemByCodeAndLinkType($code, $linkType))) {
            Mage::throwException(Mage::helper('catalog')->__('Invalid Attribute Requested'));
        }

        return $attribute;
    }

    protected function _getLinkAttributeTable(Varien_Object $attribute)
    {
        return 'catalog/product_link_attribute_' . $attribute->getDataType();
    }

    public function addLinkTypeFilter()
    {
        $this->addFieldToFilter('link_type_id', $this->getLinkTypeId());
        return $this;
    }

    public function addProductFilter()
    {
        $this->addFieldToFilter('product_id', $this->getProductId());
        return $this;
    }


    public function addStoreFilter()
    {
        $this->joinField('store_id',
                'catalog/product_store',
                'store_id',
                'product_id=entity_id',
                array('store_id'=>$this->getStoreId()));
        return $this;
    }

    public function setLinkType($type)
    {

        $this->_loadLinkAttributes($type);
        $this->_loadLinkTypeId($type);

        return $this;
    }

    protected function _loadLinkAttributes($type=null)
    {
        if(!$this->_linkAttributeCollectionLoaded) {
            $this->getLinkAttributeCollection()->addLinkTypeData();

            if(!is_null($type)) {
                $this->getLinkAttributeCollection()->addFieldToFilter('link_type', $type);
            }

            $this->getLinkAttributeCollection()->load();
            $this->_linkAttributeCollectionLoaded = true;
        }

        return $this;
    }

    protected function _loadLinkTypeId($type)
    {
        $items = $this->getLinkAttributeCollection()->getItems();
        if($this->_linkAttributeCollectionLoaded && sizeof($items) > 0) {
            reset($items);
            $firstItem = current($items);
            if ($firstItem) {
                $this->_linkTypeId = $firstItem->getLinkTypeId();
                return $this;
            }
        }

        $select = $this->_read->select()
            ->from($this->getLinkAttributeCollection()->getResource()->getTable('product_link_type'),'link_type_id')
            ->where('code = ?', $type);

        $this->_linkTypeId = $this->_read->fetchOne($select);
        return $this;
    }



    public function getLinkTypeId()
    {
        return $this->_linkTypeId;
    }

    public function useProductItem()
    {
        $this->setObject('catalog/product');
        return $this;
    }

    public function getSize()
    {

        return parent::getSize();
    }

    public function load($printQuery=false, $logQuery=false)
    {
        $result = parent::load($printQuery, $logQuery);

        if($this->getObject() instanceof Mage_Catalog_Model_Product_Link) {
            $this->walk('setAttributeCollection', array($this->getLinkAttributeCollection()));
        }

        $this->_isLoaded = true;

        return $result;
    }

    public function getColumnValues($colName)
    {
        $col = array();
        foreach ($this->getItems() as $item) {
            $col[] = $item->getData($colName);
        }
        return $col;
    }

    public function getItemById($idValue)
    {
        foreach ($this as $item) {
            if ($item->getId()==$idValue) {
                return $item;
            }
        }
        return false;
    }

    public function getItemsByColumnValue($column, $value)
    {
        $res = array();
        foreach ($this as $item) {
            if ($item->getData($column)==$value) {
                $res[] = $item;
            }
        }
        return $res;
    }

    public function getItemByColumnValue($column, $value)
    {
        foreach ($this as $item) {
            if ($item->getData($column)==$value) {
                return $item;
            }
        }
        return null;
    }

    public function addExcludeProductFilter($ids)
    {
        if (is_array($ids)) {
            if (!empty($ids)) {
                $this->addFieldToFilter('linked_product_id', array('nin'=>$ids));
            }
        }
        elseif(is_int($ids)) {
            $this->addFieldToFilter('linked_product_id', array('nin'=>array($ids)));
        }
        return $this;
    }

}// Class Mage_Catalog_Model_Entity_Product_Link_Collection END
