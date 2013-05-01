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
 * Catalog product bundle option link collection
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
 class Mage_Catalog_Model_Entity_Product_Bundle_Option_Link_Collection extends Mage_Catalog_Model_Entity_Product_Collection
 {
     protected $_optionIds = array();
     protected $_storeId = 0;
     protected $_entitiesAlias = array();


     public function __construct()
    {
        $this->setEntity(Mage::getResourceSingleton('catalog/product'))
               ->setObject('catalog/product_bundle_option_link');

    }

    public function getJoinCondition()
    {
        if(sizeof($this->getOptionIds())==0) {
            return null;
        }

        return $this->_read->quoteInto('{{table}}.option_id in (?)', $this->getOptionIds());
    }


    public function setOptionId($id)
    {
        $this->_optionIds = array($id);
        return $this;
    }

    public function setOptionIds(array $ids)
    {
        $this->_optionIds = $ids;
        return $this;
    }

    public function getOptionIds()
    {
        return $this->_optionIds;
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        $this->_joinLinkTable();
        $this->addFieldToFilter('store_id', (int)$this->getStoreId());
        return $this;
    }

    public function getStoreId()
    {
        return (int)$this->_storeId;
    }

    protected function _joinLinkTable()
    {
        $table = 'catalog/product_bundle_option_link';
        $this->joinField('link_id', $table, 'link_id', 'product_id=entity_id', $this->getJoinCondition(), 'left')
            ->joinField('product_id', $table, 'product_id', 'link_id=link_id', null, 'left')
            ->joinField('option_id', $table, 'option_id', 'link_id=link_id', null, 'left')
            ->joinField('discount', $table, 'discount', 'link_id=link_id', null, 'left')
            ->joinField('store_id',
                    'catalog/product_store',
                    'store_id',
                    'product_id=entity_id',
                    '{{table}}.store_id='.(int)$this->getStoreId(), 'left');
        return $this;
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
        return false;
    }


    public function getFirstItem()
    {

        foreach ($this as $item) {
            return $item;
        }
        return false;
    }


    // Overrided for one time loading of links for all records, becouse same entity can be used for diferent options.
    /**
     * Load entities records into items
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function _loadEntities($printQuery = false, $logQuery = false)
    {
        $entity = $this->getEntity();
        $entityIdField = $entity->getEntityIdField();

        if ($this->_pageSize) {
            $this->getSelect()->limitPage($this->_getPageStart(), $this->_pageSize);
        }

        $this->printLogQuery($printQuery, $logQuery);

        $rows = $this->_read->fetchAll($this->getSelect());
        if (!$rows) {
            return $this;
        }

        foreach ($rows as $v) {
            $object = clone $this->getObject();
            if(!isset($this->_entitiesAlias[$v[$entityIdField]])) {
                $this->_entitiesAlias[$v[$entityIdField]] = array();
            }
            $this->_items[] = $object->setData($v);
            $this->_entitiesAlias[$v[$entityIdField]][] = sizeof($this->_items)-1;
        }
        return $this;
    }

    protected function _getEntityAlias($entityId)
    {
        if(isset($this->_entitiesAlias[$entityId])) {
            return $this->_entitiesAlias[$entityId];
        }

        return false;
    }

    /**
     * Load attributes into loaded entities
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function _loadAttributes($printQuery = false, $logQuery = false)
    {
        if (empty($this->_items) || empty($this->_selectAttributes)) {
            return $this;
        }

        $entity = $this->getEntity();
        $entityIdField = $entity->getEntityIdField();

        $condition = "entity_type_id=".$entity->getTypeId();
        $condition .= " and ".$this->_read->quoteInto("$entityIdField in (?)", array_keys($this->_entitiesAlias));
        $condition .= " and ".$this->_read->quoteInto("store_id in (?)", $entity->getSharedStoreIds());
        $condition .= " and ".$this->_read->quoteInto("attribute_id in (?)", $this->_selectAttributes);

        $attrById = array();
        foreach ($entity->getAttributesByTable() as $table=>$attributes) {
            $sql = "select $entityIdField, attribute_id, value from $table where $condition";
            $this->printLogQuery($printQuery, $logQuery, $sql);
            $values = $this->_read->fetchAll($sql);
            if (empty($values)) {
                continue;
            }

            foreach ($values as $v) {
                if (!$this->_getEntityAlias($v[$entityIdField])) {
                    throw Mage::exception('Mage_Eav', Mage::helper('catalog')->__('Data integrity: No header row found for attribute'));
                }
                if (!isset($attrById[$v['attribute_id']])) {
                    $attrById[$v['attribute_id']] = $entity->getAttribute($v['attribute_id'])->getAttributeCode();
                }
                foreach ($this->_getEntityAlias($v[$entityIdField]) as $_entityIndex) {
                    $this->_items[$_entityIndex]->setData($attrById[$v['attribute_id']], $v['value']);
                }
            }
        }

        return $this;
    }

    public function useProductItem()
    {
        $this->setObject('catalog/product');
        return $this;
    }

    public function toArray(array $arrAttributes = array())
    {
        $array = array();

         foreach ($this->getItems() as $item) {
             $array[$item->getProductId()] = $item->toArray(array('discount'));
         }

         return $array;
     }
 }

