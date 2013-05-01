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
 * @package    Mage_CatalogSearch
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_CatalogSearch_Model_Mysql4_Search_Collection extends Mage_Catalog_Model_Entity_Product_Collection 
{
    protected $_attributesCollection;
    
    /**
     * Add search query filter
     *
     * @param   string $query
     * @return  Mage_CatalogSearch_Model_Mysql4_Search_Collection
     */
    public function addSearchFilter($query)
    {
        $query = '%'.$query.'%';
        $this->addFieldToFilter('entity_id', array('in'=>new Zend_Db_Expr($this->_getSearchEntityIdsSql($query))));
    	return $this;
    }
    
    /**
     * Retrieve collection of all attributes
     *
     * @return Varien_Data_Collection_Db
     */
    protected function _getAttributesCollection()
    {
        if (!$this->_attributesCollection) {
            $this->_attributesCollection = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($this->getEntity()->getConfig()->getId())
                ->load();
        
            foreach ($this->_attributesCollection as $attribute) {
                $attribute->setEntity($this->getEntity());
            }
        }
        return $this->_attributesCollection;
    }
    
    protected function _isAttributeTextAndSearchable($attribute)
    {
        if ($attribute->getIsSearchable() && in_array($attribute->getBackendType(), array('varchar', 'text'))) {
            return true;
        }
        return false;
    }
    
    protected function _hasAttributeOptionsAndSearchable($attribute)
    {
        if ($attribute->getIsSearchable() && $attribute->getFrontendInput() == 'select' && $attribute->getBackendType()=='int') {
            return true;
        }
        return false;
    }
    
    protected function _getSearchEntityIdsSql($query)
    {
        $tables = array();
        
        /**
         * Collect tables and attribute ids of attributes with string values
         */
        foreach ($this->_getAttributesCollection() as $attribute) {
        	if ($this->_isAttributeTextAndSearchable($attribute)) {
        	    $table = $attribute->getBackend()->getTable();
        	    if (!isset($tables[$table])) {
        	        $tables[$table] = array();
        	    }
        	    $tables[$table][] = $attribute->getId();
        	}
        }
        
        $selects = array();
        foreach ($tables as $table => $attributeIds) {
        	$selects[] = $this->_read->select()
        	   ->from($table, 'entity_id')
        	   ->where('store_id=?', $this->getEntity()->getStoreId())
        	   ->where('attribute_id IN (?)', $attributeIds)
        	   ->where('value LIKE ?', $query);
        }
        
        if ($sql = $this->_getSearchInOptionSql($query)) {
            $selects[] = $sql;
        }
        
        $sql = implode(' UNION ', $selects);
        return $sql;
    }
    
    /**
     * Retrieve SQL for search entities by option
     *
     * @param unknown_type $query
     * @return unknown
     */
    protected function _getSearchInOptionSql($query)
    {
        $attributeIds = array();
        $table = '';
        
        /**
         * Collect attributes with options
         */
        foreach ($this->_getAttributesCollection() as $attribute) {
        	if ($this->_hasAttributeOptionsAndSearchable($attribute)) {
        	    $table = $attribute->getBackend()->getTable();
        	    $attributeIds[] = $attribute->getId();
        	}
        }
        
        if (empty($attributeIds)) {
            return false;
        }
        
        $optionTable = Mage::getSingleton('core/resource')->getTableName('eav/attribute_option');
        $optionValueTable = Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value');
        
        /**
         * Select option Ids
         */
        $select = $this->_read->select()
            ->from(array('default'=>$optionValueTable), 'option_id')
            ->joinLeft(array('store'=>$optionValueTable), 
                $this->_read->quoteInto('store.option_id=default.option_id AND store.store_id=?', $this->getEntity()->getStoreId()),
                array())
            ->join(array('option'=>$optionTable),
                'option.option_id=default.option_id',
                array())
            ->where('default.store_id=0')
            ->where('option.attribute_id IN (?)', $attributeIds);
            
        $searchCondition = $this->_read->quoteInto('(store.value IS NULL AND default.value LIKE ?)', $query) .
            $this->_read->quoteInto(' OR (store.value LIKE ?)', $query);
        $select->where($searchCondition);
        
        $optionsIds = $this->_read->fetchCol($select);
        
        if (empty($optionsIds)) {
            return false;
        }
        
        return $this->_read->select()
            ->from($table, 'entity_id')
            ->where('store_id=?', $this->getEntity()->getStoreId())
            ->where('attribute_id IN (?)', $attributeIds)
            ->where('value IN (?)', $optionsIds);
    }
}