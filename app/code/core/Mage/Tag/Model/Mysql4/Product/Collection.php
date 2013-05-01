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
 * @package    Mage_Tag
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tagged products Collection
 *
 * @category   Mage
 * @package    Mage_Tag
 */

class Mage_Tag_Model_Mysql4_Product_Collection extends Mage_Catalog_Model_Entity_Product_Collection
{
 	protected $_entitiesAlias = array();
 	protected $_customerFilterId;
 	protected $_tagIdFilter;


 	protected $_joinFlags = array();

	public function __construct()
	{
	    parent::__construct();
        $this->getSelect()->group('e.entity_id');
	}

	public function setJoinFlag($table)
         {
            $this->_joinFlags[$table] = true;
            return $this;
        }

        public function getJoinFlag($table)
        {
            return isset($this->_joinFlags[$table]);
        }

        public function unsetJoinFlag($table=null)
        {
            if (is_null($table)) {
                $this->_joinFlags = array();
            } elseif ($this->getJoinFlag($table)) {
                unset($this->_joinFlags[$table]);
            }

            return $this;
        }



    public function addStoresVisibility()
    {
        $this->setJoinFlag('add_stores_after');
        return $this;
    }

    protected function _addStoresVisibility()
    {
        $tagIds =array();

        foreach ($this as $item) {
            $tagIds[] = $item->getTagId();
        }

        $tagsStores = array();
        if (sizeof($tagIds)>0) {
            $select = $this->_read->select()
                ->from($this->getTable('summary'), array('store_id','tag_id'))
                ->where('tag_id IN(?)', $tagIds);
            $tagsRaw = $this->_read->fetchAll($select);
            foreach ($tagsRaw as $tag) {
                if (!isset($tagsStores[$tag['tag_id']])) {
                    $tagsStores[$tag['tag_id']] = array();
                }

                $tagsStores[$tag['tag_id']][] = $tag['store_id'];
            }
        }

        foreach ($this as $item) {
            if(isset($tagsStores[$item->getTagId()])) {
                $item->setStores($tagsStores[$item->getTagId()]);
            } else {
                $item->setStores(array());
            }
        }


        return $this;
    }

    public function addGroupByTag()
    {
        $this->getSelect()
            ->group('relation.tag_relation_id');
        return $this;
    }


    public function getTable($name)
    {
        if(strpos($name, '/')!==false) {
            return Mage::getSingleton('core/resource')->getTableName($name);
        } else {
            return Mage::getSingleton('core/resource')->getTableName('tag/'.$name);
        }
    }

    public function addStoreFilter($storeId)
    {
        $this->getSelect()->join(array('summary_store'=>$this->getTable('summary')), 't.tag_id = summary_store.tag_id AND summary_store.store_id = ' . (int) $storeId, array());
        $this->getSelect()->join(array('p_store'=>$this->getTable('catalog/product_store')), 'e.entity_id = p_store.product_id AND p_store.store_id = ' . (int) $storeId, array());
        if($this->getJoinFlag('relation')) {
            $this->getSelect()->where('relation.store_id = ?', $storeId);
        }

        return $this;
    }

	public function addCustomerFilter($customerId)
	{
        $this->getSelect()
            ->where('relation.customer_id = ?', $customerId);
        $this->_customerFilterId = $customerId;
		return $this;
	}

	public function addTagFilter($tagId)
	{
        $this->getSelect()
            ->where('relation.tag_id = ?', $tagId);
            $this->setJoinFlag('distinct');
		return $this;
	}

	public function addStatusFilter($status)
	{
        $this->getSelect()
            ->where('t.status = ?', $status);
		return $this;
	}

    public function setDescOrder($dir='DESC')
    {
        $this->getSelect()
            ->order('relation.tag_relation_id', $dir);
        return $this;
    }

    public function resetSelect()
    {
        parent::resetSelect();
        $this->_joinFields();
        return $this;
    }

    public function addPopularity($tagId, $storeId=null)
    {
        $tagRelationTable = Mage::getSingleton('core/resource')->getTableName('tag/relation');

        $condition = '';
        if(!is_null($storeId)) {
          $condition = 'AND ' . $this->_read->quoteInto('prelation.store_id = ?', $storeId);
        }



        $this->getSelect()
            ->joinLeft(array('prelation' => $tagRelationTable), 'prelation.product_id=e.entity_id '.$condition , array('COUNT(DISTINCT prelation.tag_relation_id) AS popularity'))
            ->where('prelation.tag_id = ?', $tagId);

        $this->_tagIdFilter = $tagId;

        $this->setJoinFlag('prelation');
        return $this;
    }

    public function addPopularityFilter($condition) {
        $tagRelationTable = Mage::getSingleton('core/resource')->getTableName('tag/relation');

        $select = $this->_read->select()
            ->from($tagRelationTable, array('product_id', 'COUNT(DISTINCT tag_relation_id) as popularity'))
            ->where('tag_id = ?', $this->_tagIdFilter)
            ->group('product_id')
            ->having($this->_getConditionSql('popularity', $condition));

        $prodIds = array();
        foreach($this->_read->fetchAll($select) as $item) {
            $prodIds[] = $item['product_id'];
        }

        if(sizeof($prodIds)>0) {
            $this->getSelect()->where('e.entity_id IN(?)', $prodIds);
        } else {
            $this->getSelect()->where('e.entity_id IN(0)');
        }

        return $this;
    }

    public function setActiveFilter()
    {
        $this->getSelect()->where('relation.active = 1');
        if($this->getJoinFlag('prelation')) {
            $this->getSelect()->where('prelation.active = 1');
        }
        return $this;
    }

    public function addProductTags($storeId=null)
    {
        foreach( $this->getItems() as $item ) {
            $tagsCollection = Mage::getModel('tag/tag')->getResourceCollection();

            if (!is_null($storeId)) {
                $tagsCollection->addStoreFilter($storeId);
            }

            $tagsCollection->addPopularity()
                ->addProductFilter($item->getEntityId())
                ->addCustomerFilter($this->_customerFilterId)
                ->setActiveFilter();



            $tagsCollection->load();
            $item->setProductTags( $tagsCollection );
        }

        return $this;
    }

    protected function _joinFields()
    {
        $tagTable = Mage::getSingleton('core/resource')->getTableName('tag/tag');
        $tagRelationTable = Mage::getSingleton('core/resource')->getTableName('tag/relation');

        $this->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('small_image');

        $this->getSelect()
            ->join(array('relation' => $tagRelationTable), "relation.product_id = e.entity_id")
            ->join(array('t' => $tagTable), "t.tag_id = relation.tag_id", array(
                'tag_id', 'name', 'status', 'tag_name' => 'name'
            ));


    }

    public function load($printQuery = false, $logQuery=false)
    {
        parent::load($printQuery, $logQuery);
        if($this->getJoinFlag('add_stores_after')) {
            $this->_addStoresVisibility();
        }
        return $this;
    }

    /**
     * Render SQL for retrieve product count
     *
     * @return string
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();

        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::GROUP);

        if($this->getJoinFlag('group_tag')) {
            $field = 'relation.tag_id';
        } else {
            $field = 'e.entity_id';
        }
        $sql = $countSelect->__toString();
        $sql = preg_replace('/^select\s+.+?\s+from\s+/is', 'select count(' . ( $this->getJoinFlag('distinct') ? 'DISTINCT ' : '' ) . $field . ') from ', $sql);
        return $sql;
    }

    /**
     * Load entities records into items
     *
     * @link Mage_Catalog_Model_Entity_Product_Bundle_Option_Link_Collection
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
                    throw Mage::exception('Mage_Eav', Mage::helper('tag')->__('Data integrity: No header row found for attribute'));
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

    public function setOrder($attribute, $dir='desc')
    {
        if ($attribute == 'popularity') {
            $this->getSelect()->order($attribute . ' ' . $dir);
        }
        else {
        	parent::setOrder($attribute, $dir);
        }
        return $this;
    }
}
