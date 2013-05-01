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
 * @package    Mage_Eav
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Entity/Attribute/Model - collection abstract
 *
 * @category   Mage
 * @package    Mage_Eav
 */
class Mage_Eav_Model_Entity_Collection_Abstract implements IteratorAggregate, Countable
{
    /**
     * Read connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;

    /**
     * Write connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_write;

    /**
     * Entity object to define collection's attributes
     *
     * @var Mage_Eav_Model_Entity_Abstract
     */
    protected $_entity;

    protected $_selectEntityTypes=array();

    /**
     * Attributes to be fetched for objects in collection
     *
     * @var array
     */
    protected $_selectAttributes=array();

    /**
     * Attributes to be filtered order sorted by
     *
     * @var array
     */
    protected $_filterAttributes=array();

    /**
     * Object template to be used for collection items
     *
     * @var Varien_Object
     */
    protected $_object;

    /**
     * Collection's Zend_Db_Select object
     *
     * @var Zend_Db_Select
     */
    protected $_select;

    /**
     * Array of objects in the collection
     *
     * @var array
     */
    protected $_items = array();

    protected $_itemsById = array();

    /**
     * Record number where the page starts
     *
     * @var integer
     */
    protected $_pageStart = 1;

    /**
     * Number of records on the page
     *
     * @var integer
     */
    protected $_pageSize;

    protected $_rowCount;

    protected $_joinEntities = array();

    protected $_joinAttributes = array();

    protected $_joinFields = array();

    protected $_rowIdFieldName;

    /**
     * Set connections for entity operations
     *
     * @param Zend_Db_Adapter_Abstract $read
     * @param Zend_Db_Adapter_Abstract $write
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function setConnection(Zend_Db_Adapter_Abstract $read, Zend_Db_Adapter_Abstract $write=null)
    {
        $this->_read = $read;
        $this->_write = $write ? $write : $read;
        return $this;
    }

    /**
     * Set entity to use for attributes
     *
     * @param Mage_Eav_Model_Entity_Abstract $entity
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function setEntity($entity)
    {
        if ($entity instanceof Mage_Eav_Model_Entity_Abstract) {
            $this->_entity = $entity;
        } elseif (is_string($entity) || $entity instanceof Mage_Core_Model_Config_Element) {
            $this->_entity = Mage::getModel('eav/entity')->setType($entity);
        } else {
            Mage::throwException(Mage::helper('eav')->__('Invalid entity supplied: %s', print_r($entity,1)));
        }
        $this->_read = $this->_entity->getReadConnection();
        $this->_write = $this->_entity->getWriteConnection();

        if ($this->_entity->getTypeId()) {
            $this->addAttributeToFilter('entity_type_id', $this->_entity->getTypeId());
        }
        return $this;
    }

    /**
     * Get collection's entity object
     *
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function getEntity()
    {
        if (empty($this->_entity)) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Entity is not initialized'));
        }
        return $this->_entity;
    }

    /**
     * Set template object for the collection
     *
     * @param Varien_Object $object
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function setObject($object=null)
    {
        if (empty($object)) {
            $object = new Varien_Object();
        } elseif (is_string($object)) {
            $object = Mage::getModel($object);
        }
        if (!$object instanceof Varien_Object) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid object supplied'));
        }

        $this->_object = $object;

        return $this;
    }

    /**
     * Get template object
     * It is a factory method by default
     *
     * @param bool $createNewInstance you can set this false to get an original instance
     * @return Varien_Object
     */
    public function getObject($createNewInstance = true)
    {
        Varien_Profiler::start(__METHOD__);
        /*
        if (!$this->_object && $this->_entity && $this->_entity->getObject()) {
            $this->setObject($this->_entity->getObject());
        }
        */
        if (!$this->_object) {
            $this->setObject();
        }
        if ($createNewInstance) {
            $className = get_class($this->_object);
            $object = new $className();
            // $object = clone $this->_object;
        } else {
            $object = $this->_object;
        }
        Varien_Profiler::stop(__METHOD__);
        return $object;
    }


    /**
     * Retrieve array of object collection items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    public function getItemById($id)
    {
        if (isset($this->_items[$id])) {
            return $this->_items[$id];
        }
        return false;
    }

    /**
     * Add an object to the collection
     *
     * @param Varien_Object $object
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addItem(Varien_Object $object)
    {
        if (get_class($object)!==get_class($this->getObject(false))) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Attempt to add an invalid object'));
        }

        //$entityId = $row[$this->getEntity()->getEntityIdField()];
        if ($entityId = $object->getId()) {
            $this->_items[$entityId] = $object;
        }
        else {
            $this->_items[] = $object;
        }

        return $this;
    }

    /**
     * Reset zend db select instance
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function resetSelect()
    {
        $this->_select = $this->_read->select();
        $this->_select->from(array('e'=>$this->getEntity()->getEntityTable()));
        return $this;
    }

    /**
     * Get zend db select instance
     *
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
        if (empty($this->_select)) {
            $this->resetSelect();
        }
        return $this->_select;
    }

    public function getAttribute($attributeCode)
    {
        if (isset($this->_joinAttributes[$attributeCode])) {
            return $this->_joinAttributes[$attributeCode]['attribute'];
        } else {
            return $this->getEntity()->getAttribute($attributeCode);
        }
        return false;
    }

    /**
     * Add attribute filter to collection
     *
     * If $attribute is an array will add OR condition with following format:
     * array(
     *     array('attribute'=>'firstname', 'like'=>'test%'),
     *     array('attribute'=>'lastname', 'like'=>'test%'),
     * )
     *
     * @see self::_getConditionSql for $condition
     * @param Mage_Eav_Model_Entity_Attribute_Interface|integer|string|array $attribute
     * @param null|string|array $condition
     * @param string $operator
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addAttributeToFilter($attribute, $condition=null)
    {
        if($attribute===null) {
        	$this->getSelect();
        	return $this;
        }

        if (is_numeric($attribute)) {
            $attribute = $this->getEntity()->getAttribute($attribute)->getAttributeCode();
        }
        elseif ($attribute instanceof Mage_Eav_Model_Entity_Attribute_Interface) {
            $attribute = $attribute->getAttributeCode();
        }

    	if (is_array($attribute)) {
    		$sqlArr = array();
            foreach ($attribute as $condition) {
                $sqlArr[] = $this->_getAttributeConditionSql($condition['attribute'], $condition);
            }
            $conditionSql = '('.join(') OR (', $sqlArr).')';
        }
        elseif (is_string($attribute)) {
            if (is_null($condition)) {
                throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid condition'));
            }
            $conditionSql = $this->_getAttributeConditionSql($attribute, $condition);
        }

        if (!empty($conditionSql)) {
            $this->getSelect()->where($conditionSql);
        } else {
            Mage::throwException('Invalid attribute identifier for filter ('.get_class($attribute).')');
        }

        return $this;
    }

    /**
     * Wrapper for compatibility with Varien_Data_Collection_Db
     *
     * @param mixed $attribute
     * @param mixed $condition
     */
    public function addFieldToFilter($attribute, $condition=null){
        return $this->addAttributeToFilter($attribute, $condition);
    }

    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addAttributeToSort($attribute, $dir='asc')
    {
        if (isset($this->_joinFields[$attribute])) {
            $this->getSelect()->order($this->_getAttributeFieldName($attribute).' '.$dir);
            return $this;
        }
        if (isset($this->_joinAttributes[$attribute])) {
            $attrInstance = $this->_joinAttributes[$attribute]['attribute'];
            $entityField = $this->_getAttributeTableAlias($attribute).'.'.$attrInstance->getAttributeCode();
        } else {
            $attrInstance = $this->getEntity()->getAttribute($attribute);
            $entityField = 'e.'.$attribute;
        }
        if ($attrInstance) {
            if ($attrInstance->getBackend()->isStatic()) {
                $this->getSelect()->order($entityField.' '.$dir);
            } else {
                $this->_addAttributeJoin($attribute);
                $this->getSelect()->order($this->_getAttributeTableAlias($attribute).'.value '.$dir);
            }
        }
        return $this;
    }

    /**
     * Add attribute to entities in collection
     *
     * If $attribute=='*' select all attributes
     *
     * @param array|string|integer|Mage_Core_Model_Config_Element $attribute
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addAttributeToSelect($attribute)
    {
        if (is_array($attribute)) {
            foreach ($attribute as $a) {
                $this->addAttribute($a);
            }
        } elseif ('*'===$attribute) {
            $attributes = $this->getEntity()
                ->loadAllAttributes()
                ->getAttributesByCode();
            foreach ($attributes as $attrCode=>$attr) {
                $this->_selectAttributes[$attrCode] = $attr->getId();
            }
        } else {
            if (isset($this->_joinAttributes[$attribute])) {
                $attrInstance = $this->_joinAttributes[$attribute]['attribute'];
            } else {
                $attrInstance = $this->getEntity()->getAttribute($attribute);
            }
            if (empty($attrInstance)) {
                throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid attribute requested: %s', (string)$attribute));
            }
            $this->_selectAttributes[$attrInstance->getAttributeCode()] = $attrInstance->getId();
        }
        return $this;
    }

    public function addEntityTypeToSelect($entityType, $prefix)
    {
        $this->_selectEntityTypes[$entityType] = array(
            'prefix'=>$prefix,
        );
        return $this;
    }

    /**
     * Add attribute expression (SUM, COUNT, etc)
     *
     * Example: ('sub_total', 'SUM({{attribute}})', 'revenue')
     * Example: ('sub_total', 'SUM({{revenue}})', 'revenue')
     *
     * For some functions like SUM use groupByAttribute.
     *
     * @param string $alias
     * @param string $expression
     * @param string $attribute
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addExpressionAttributeToSelect($alias, $expression, $attribute)
    {
        // validate alias
        if (isset($this->_joinFields[$alias])) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Joined field or attribute expression with this alias is already declared'));
        }
        if(!is_array($attribute)) {
            $attribute = array($attribute);
        }

        $fullExpression = $expression;
        // Replacing multiple attributes
        foreach($attribute as $attributeItem) {
            $attributeInstance = $this->getAttribute($attributeItem);

            if ($attributeInstance->getBackend()->isStatic()) {
                $attrField = 'e.' . $attributeItem;
            } else {
                $this->_addAttributeJoin($attributeItem, 'left');
                $attrField = $this->_getAttributeFieldName($attributeItem);
            }

            $fullExpression = str_replace('{{attribute}}', $attrField, $fullExpression);
            $fullExpression = str_replace('{{' . $attributeItem . '}}', $attrField, $fullExpression);
        }

        $this->getSelect()->from(null, array($alias=>$fullExpression));

        $this->_joinFields[$alias] = array(
            'table' => false,
            'field' => $fullExpression
        );

        return $this;
    }


    /**
     * Groups results by specified attribute
     *
     * @param string|array $attribute
     */
    public function groupByAttribute($attribute)
    {
        if(is_array($attribute)) {
            foreach ($attribute as $attributeItem) {
                $this->groupByAttribute($attributeItem);
            }
        } else {
            if (isset($this->_joinFields[$attribute])) {
                $this->getSelect()->group($this->_getAttributeFieldName($attribute));
                return $this;
            }

            if (isset($this->_joinAttributes[$attribute])) {
                $attrInstance = $this->_joinAttributes[$attribute]['attribute'];
                $entityField = $this->_getAttributeTableAlias($attribute).'.'.$attrInstance->getAttributeCode();
            } else {
                $attrInstance = $this->getEntity()->getAttribute($attribute);
                $entityField = 'e.'.$attribute;
            }

            if ($attrInstance->getBackend()->isStatic()) {
                $this->getSelect()->group($entityField);
            } else {
                $this->_addAttributeJoin($attribute);
                $this->getSelect()->group($this->_getAttributeTableAlias($attribute).'.value');
            }
        }

        return $this;
    }

    /**
     * Add attribute from joined entity to select
     *
     * Examples:
     * ('billing_firstname', 'customer_address/firstname', 'default_billing')
     * ('billing_lastname', 'customer_address/lastname', 'default_billing')
     * ('shipping_lastname', 'customer_address/lastname', 'default_billing')
     * ('shipping_postalcode', 'customer_address/postalcode', 'default_shipping')
     * ('shipping_city', $cityAttribute, 'default_shipping')
     *
     * Developer is encouraged to use existing instances of attributes and entities
     * After first use of string entity name it will be cached in the collection
     *
     * @todo connect between joined attributes of same entity
     * @param string $alias alias for the joined attribute
     * @param string|Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param string $bind attribute of the main entity to link with joined $filter
     * @param string $filter primary key for the joined entity (entity_id default)
     * @param string $joinType inner|left
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function joinAttribute($alias, $attribute, $bind, $filter=null, $joinType='inner', $storeId=null)
    {
        // validate alias
        if (isset($this->_joinAttributes[$alias])) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid alias, already exists in joined attributes'));
        }

        // validate bind attribute
        if (is_string($bind)) {
            $bindAttribute = $this->getAttribute($bind);
        }
        if (!$bindAttribute || (!$bindAttribute->getBackend()->isStatic() && !$bindAttribute->getId())) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid foreign key'));
        }

        // try to explode combined entity/attribute if supplied
        if (is_string($attribute)) {
            $attrArr = explode('/', $attribute);
            if (empty($entity) && isset($attrArr[1])) {
                $entity = $attrArr[0];
                $attribute = $attrArr[1];
            }
        }

        // validate entity
        if (empty($entity) && $attribute instanceof Mage_Eav_Model_Entity_Attribute_Abstract) {
            $entity = $attribute->getEntity();
        } elseif (is_string($entity)) {
            // retrieve cached entity if possible
            if (isset($this->_joinEntities[$entity])) {
                $entity = $this->_joinEntities[$entity];
            } else {
                $entity = Mage::getModel('eav/entity')->setType($attrArr[0]);
            }
        }
        if (!$entity || !$entity->getTypeId()) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid entity type'));
        }

        if ($storeId) {
            $entity->setStore($storeId);
        }
        // cache entity
        if (!isset($this->_joinEntities[$entity->getType()])) {
            $this->_joinEntities[$entity->getType()] = $entity;
        }

        // validate attribute
        if (is_string($attribute)) {
            $attribute = $entity->getAttribute($attribute);
        }
        if (!$attribute) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid attribute type'));
        }

        if (empty($filter)) {
            $filter = $entity->getEntityIdField();
        }

        // add joined attribute
        $this->_joinAttributes[$alias] = array(
            'bind'=>$bind,
            'bindAttribute'=>$bindAttribute,
            'attribute'=>$attribute,
            'filter'=>$filter,
        );

        $this->_addAttributeJoin($alias, $joinType);

        return $this;
    }

    /**
     * Join regular table field and use an attribute as fk
     *
     * Examples:
     * ('country_name', 'directory/country_name', 'name', 'country_id=shipping_country', "{{table}}.language_code='en'", 'left')
     *
     * @param string $alias 'country_name'
     * @param string $table 'directory/country_name'
     * @param string $field 'name'
     * @param string $bind 'PK(country_id)=FK(shipping_country_id)'
     * @param string|array $cond "{{table}}.language_code='en'" OR array('language_code'=>'en')
     * @param string $joinType 'left'
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function joinField($alias, $table, $field, $bind, $cond=null, $joinType='inner')
    {
        // validate alias
        if (isset($this->_joinFields[$alias])) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Joined field with this alias is already declared'));
        }

        // validate table
        if (strpos($table, '/')!==false) {
            $table = Mage::getSingleton('core/resource')->getTableName($table);
        }
        $tableAlias = $this->_getAttributeTableAlias($alias);

        // validate bind
        list($pk, $fk) = explode('=', $bind);
        $bindCond = $tableAlias.'.'.$pk.'='.$this->_getAttributeFieldName($fk);

        // process join type
        switch ($joinType) {
            case 'left':
                $joinMethod = 'joinLeft';
                break;

            default:
                $joinMethod = 'join';
        }
        $condArr = array($bindCond);

        // add where condition if needed
        if (!is_null($cond)) {
            if (is_array($cond)) {
                foreach ($cond as $k=>$v) {
                    $condArr[] = $this->_getConditionSql($tableAlias.'.'.$k, $v);
                }
            } else {
                $condArr[] = str_replace('{{table}}', $tableAlias, $cond);
            }
        }
        $cond = '('.join(') AND (', $condArr).')';

        // join table
        $this->getSelect()->$joinMethod(array($tableAlias=>$table), $cond, array($alias=>$field));

        // save joined attribute
        $this->_joinFields[$alias] = array(
            'table'=>$tableAlias,
            'field'=>$field,
        );

        return $this;
    }

    /**
     * Join a table
     *
     * @param string $table
     * @param string $bind
     * @param string|array $fields
     * @param null|array $cond
     * @param string $joinType
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function joinTable($table, $bind, $fields=null, $cond=null, $joinType='inner')
    {
        // validate table
        if (strpos($table, '/')!==false) {
            $table = Mage::getSingleton('core/resource')->getTableName($table);
        }
        $tableAlias = $table;

        // validate fields and aliases
        if (!$fields) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid joined fields'));
        }
        foreach ($fields as $alias=>$field) {
            if (isset($this->_joinFields[$alias])) {
                throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Joined field with this alias (%s) is already declared', $alias));
            }
            $this->_joinFields[$alias] = array(
                'table'=>$tableAlias,
                'field'=>$field,
            );
        }

        // validate bind
        list($pk, $fk) = explode('=', $bind);
        $bindCond = $tableAlias.'.'.$pk.'='.$this->_getAttributeFieldName($fk);

        // process join type
        switch ($joinType) {
            case 'left':
                $joinMethod = 'joinLeft';
                break;

            default:
                $joinMethod = 'join';
        }
        $condArr = array($bindCond);

        // add where condition if needed
        if (!is_null($cond)) {
            if (is_array($cond)) {
                foreach ($cond as $k=>$v) {
                    $condArr[] = $this->_getConditionSql($tableAlias.'.'.$k, $v);
                }
            } else {
                $condArr[] = str_replace('{{table}}', $tableAlias, $cond);
            }
        }
        $cond = '('.join(') AND (', $condArr).')';

// join table
        $this->getSelect()->$joinMethod(array($tableAlias=>$table), $cond, $fields);
    }

    /**
     * Remove an attribute from selection list
     *
     * @param string $attribute
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function removeAttributeToSelect($attribute=null)
    {
        if (is_null($attribute)) {
            $this->_selectAttributes = array();
        } else {
            unset($this->_selectAttributes[$attribute]);
        }
        return $this;
    }

    /**
     * Set collection page start and records to show
     *
     * @param integer $pageNum
     * @param integer $pageSize
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function setPage($pageNum, $pageSize)
    {
        //$this->getSelect()->limitPage($pageNum, $pageSize);
        $this->setCurPage($pageNum)
            ->getPageSize($pageSize);
        return $this;
    }

    /**
     * Load collection data into object items
     *
     * @param integer $storeId
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if (!$this->_read) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('No connection available'));
        }

        $this->_beforeLoad();

        $this->_loadEntities($printQuery, $logQuery);
        $this->_loadAttributes($printQuery, $logQuery);

        foreach ($this->_items as $item) {
            $item->setOrigData();
        }

        $this->_afterLoad();

        return $this;
    }

    /**
     * Retrive all ids for collection
     *
     * @return array
     */
    public function getAllIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->from(null, 'e.'.$this->getEntity()->getIdFieldName());
        return $this->_read->fetchCol($idsSelect);
    }

    /**
     * Save all the entities in the collection
     *
     * @todo make batch save directly from collection
     */
    public function save()
    {
        #$this->walk('save');
        foreach ($this->getItems() as $item) {
            //$this->getEntity()->save($item);
            $item->save();
        }
        return $this;
    }


    /**
     * Delete all the entities in the collection
     *
     * @todo make batch delete directly from collection
     */
    public function delete()
    {
        #$this->walk('delete');
        foreach ($this->getItems() as $k=>$item) {
            $this->getEntity()->delete($item);
            unset($this->_items[$k]);
        }
        return $this;
    }

    /**
     * Import 2D array into collection as objects
     *
     * If the imported items already exist, update the data for existing objects
     *
     * @param array $arr
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function importFromArray($arr)
    {
        $entityIdField = $this->getEntity()->getEntityIdField();
        foreach ($arr as $row) {
            $entityId = $row[$entityIdField];
            if (!isset($this->_items[$entityId])) {
                $this->_items[$entityId] = $this->getObject();
                $this->_items[$entityId]->setData($row);
            }  else {
                $this->_items[$entityId]->addData($row);
            }
        }
        return $this;
    }

    /**
     * Get collection data as a 2D array
     *
     * @return array
     */
    public function exportToArray()
    {
        $result = array();
        $entityIdField = $this->getEntity()->getEntityIdField();
        foreach ($this->getItems() as $item) {
            $result[$item->getData($entityIdField)] = $item->getData();
        }
        return $result;
    }

    /**
     * Walk through the collection and run method with optional arguments
     *
     * Returns array with results for each item
     *
     * @param string $method
     * @param array $args
     * @return array
     */
    public function walk($method, array $args=array())
    {
        $results = array();
        foreach ($this->getItems() as $id=>$item) {
            $results[$id] = call_user_func_array(array($item, $method), $args);
        }
        return $results;
    }

    public function getRowIdFieldName()
    {
        if (is_null($this->_rowIdFieldName)) {
            $this->_rowIdFieldName = $this->getEntity()->getIdFieldName();
        }
        return $this->_rowIdFieldName;
    }

    public function setRowIdFieldName($fieldName)
    {
        $this->_rowIdFieldName = $fieldName;
        return $this;
    }

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

        try {
            $rows = $this->_read->fetchAll($this->getSelect());
        } catch (Exception $e) {
            $this->printLogQuery(true, true, $this->getSelect());
            throw $e;
        }

        if (!$rows) {
            return $this;
        }

        foreach ($rows as $v) {
            $object = $this->getObject();
            $this->_items[$v[$this->getRowIdFieldName()]] = $object->setData($v);
            if (!isset($this->_itemsById[$object->getId()])) {
            	$this->_itemsById[$object->getId()] = array();
            }
            $this->_itemsById[$object->getId()][] = $object;
        }
        return $this;
    }

    /**
     * Load attributes into loaded entities
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function _loadAttributes($printQuery = false, $logQuery = false)
    {
        if (empty($this->_items) || empty($this->_itemsById) || empty($this->_selectAttributes)) {
            return $this;
        }

        $entity = $this->getEntity();
        $entityIdField = $entity->getEntityIdField();

        $condition = "entity_type_id=".$entity->getTypeId();
        $condition .= " and ".$this->_read->quoteInto("$entityIdField in (?)", array_keys($this->_itemsById));
        $condition .= " and ".$this->_read->quoteInto("attribute_id in (?)", $this->_selectAttributes);

        $attrById = array();
        foreach ($entity->getAttributesByTable() as $table=>$attributes) {
            $sql = "select $entityIdField, attribute_id, value from $table where $condition";
            $this->printLogQuery($printQuery, $logQuery, $sql);
            try {
                $values = $this->_read->fetchAll($sql);
            } catch (Exception $e) {
                $this->printLogQuery(true, true, $sql);
                throw $e;
            }
            if (empty($values)) {
                continue;
            }

            foreach ($values as $v) {
                if (!isset($this->_itemsById[$v[$entityIdField]])) {
                    throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Data integrity: No header row found for attribute'));
                }
                if (!isset($attrById[$v['attribute_id']])) {
                    $attrById[$v['attribute_id']] = $entity->getAttribute($v['attribute_id'])->getAttributeCode();
                }
                foreach ($this->_itemsById[$v[$entityIdField]] as $object) {
                	$object->setData($attrById[$v['attribute_id']], $v['value']);
                }
            }
        }

        return $this;
    }

    /**
     * Get alias for attribute value table
     *
     * @param string $attributeCode
     * @return string
     */
    protected function _getAttributeTableAlias($attributeCode)
    {
        return '_table_'.$attributeCode;
    }

    protected function _getAttributeFieldName($attributeCode)
    {
        if (isset($this->_joinFields[$attributeCode])) {
            $attr = $this->_joinFields[$attributeCode];
            return $attr['table'] ? $attr['table'] .'.'.$attr['field'] : $attr['field'];
        }

        $attribute = $this->getAttribute($attributeCode);
        if (!$attribute) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid attribute name: %s', $attributeCode));
        }

        if ($attribute->getBackend()->isStatic()) {
            if (isset($this->_joinAttributes[$attributeCode])) {
                $fieldName = $this->_getAttributeTableAlias($attributeCode).'.'.$attributeCode;
            } else {
                $fieldName = 'e.'.$attributeCode;
            }
        } else {
            $fieldName = $this->_getAttributeTableAlias($attributeCode).'.value';
        }
        return $fieldName;
    }

    /**
     * Add attribute value table to the join if it wasn't added previously
     *
     * @todo REFACTOR!!!
     * @param string $attributeCode
     * @param string $joinType inner|left
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _addAttributeJoin($attributeCode, $joinType='inner')
    {
        if (!empty($this->_filterAttributes[$attributeCode])) {
            return $this;
        }

        $attrTable = $this->_getAttributeTableAlias($attributeCode);
        if (isset($this->_joinAttributes[$attributeCode])) {
            $attribute = $this->_joinAttributes[$attributeCode]['attribute'];
            $entity = $attribute->getEntity();
            $entityIdField = $entity->getEntityIdField();
            $fkName = $this->_joinAttributes[$attributeCode]['bind'];
            $fkAttribute = $this->_joinAttributes[$attributeCode]['bindAttribute'];
            $fkTable = $this->_getAttributeTableAlias($fkName);
            if ($fkAttribute->getBackend()->isStatic()) {
                if (isset($this->_joinAttributes[$fkName])) {
                    $fk = $fkTable.".".$fkAttribute->getAttributeCode();
                } else {
                    $fk = "e.".$fkAttribute->getAttributeCode();
                }
            } else {
                $this->_addAttributeJoin($fkAttribute->getAttributeCode(), $joinType);
                $fk = "$fkTable.value";
            }
            $pk = $attrTable.'.'.$this->_joinAttributes[$attributeCode]['filter'];
        } else {
            $entity = $this->getEntity();
            $entityIdField = $entity->getEntityIdField();
            $attribute = $entity->getAttribute($attributeCode);
            $fk = "e.$entityIdField";
            $pk = "$attrTable.$entityIdField";
        }

        if (!$attribute) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid attribute name: %s', $attributeCode));
        }

        if ($attribute->getBackend()->isStatic()) {
            $attrFieldName = "$attrTable.".$attribute->getAttributeCode();
        } else {
            $attrFieldName = "$attrTable.value";
        }

        $read = $this->getEntity()->getReadConnection();
        $select = $this->getSelect();

        $condArr = array("$pk = $fk");
        /*if ($entity->getUseDataSharing()) {
            $condArr[] = $read->quoteInto("$attrTable.store_id in (?)", $entity->getSharedStoreIds());
        }
        else {
            $condArr[] = $read->quoteInto("$attrTable.store_id=?", $entity->getStoreId());
        }*/
        if (!$attribute->getBackend()->isStatic()) {
            $condArr[] = $read->quoteInto("$attrTable.attribute_id=?", $attribute->getId());
        }

        // process join type
        switch ($joinType) {
            case 'left':
                $joinMethod = 'joinLeft';
                break;

            default:
                $joinMethod = 'join';
        }

        $select->$joinMethod(
            array($attrTable => $attribute->getBackend()->getTable()),
            '('.join(') AND (', $condArr).')',
            array($attributeCode=>$attrFieldName)
        );

        $this->removeAttributeToSelect($attributeCode);

        $this->_filterAttributes[$attributeCode] = $attribute->getId();

        return $this;
    }

    /**
     * Build SQL statement for condition
     *
     * If $condition integer or string - exact value will be filtered
     *
     * If $condition is array is - one of the following structures is expected:
     * - array("from"=>$fromValue, "to"=>$toValue)
     * - array("like"=>$likeValue)
     * - array("neq"=>$notEqualValue)
     * - array("in"=>array($inValues))
     * - array("nin"=>array($notInValues))
     *
     * If non matched - sequential array is expected and OR conditions
     * will be built using above mentioned structure
     *
     * @param string $fieldName
     * @param integer|string|array $condition
     * @return string
     */
    protected function _getConditionSql($fieldName, $condition) {
        $sql = '';
        if (is_array($condition)) {
            if (isset($condition['from']) || isset($condition['to'])) {
                if (isset($condition['from'])) {
                    if (empty($condition['date'])) {
                        if ( empty($condition['datetime'])) {
                            $from = $condition['from'];
                        }
                        else {
                            $from = $this->_read->convertDateTime($condition['from']);
                        }
                    }
                    else {
                        $from = $this->_read->convertDate($condition['from']);
                    }
                    $sql.= $this->_read->quoteInto("$fieldName >= ?", $from);
                }
                if (isset($condition['to'])) {
                    $sql.= empty($sql) ? '' : ' and ';

                    if (empty($condition['date'])) {
                        if ( empty($condition['datetime'])) {
                            $to = $condition['to'];
                        }
                        else {
                            $to = $this->_read->convertDateTime($condition['to']);
                        }
                    }
                    else {
                        $to = $this->_read->convertDate($condition['to']);
                    }

                    $sql.= $this->_read->quoteInto("$fieldName <= ?", $to);
                }
            }
            elseif (isset($condition['eq'])) {
                $sql = $this->_read->quoteInto("$fieldName = ?", $condition['eq']);
            }
            elseif (isset($condition['neq'])) {
                $sql = $this->_read->quoteInto("$fieldName != ?", $condition['neq']);
            }
            elseif (isset($condition['like'])) {
                $sql = $this->_read->quoteInto("$fieldName like ?", $condition['like']);
            }
            elseif (isset($condition['nlike'])) {
                $sql = $this->_read->quoteInto("$fieldName not like ?", $condition['nlike']);
            }
            elseif (isset($condition['in'])) {
                $sql = $this->_read->quoteInto("$fieldName in (?)", $condition['in']);
            }
            elseif (isset($condition['nin'])) {
                $sql = $this->_read->quoteInto("$fieldName not in (?)", $condition['nin']);
            }
            elseif (isset($condition['is'])) {
                $sql = $this->_read->quoteInto("$fieldName is ?", $condition['is']);
            }
            else {
                $orSql = array();
                foreach ($condition as $orCondition) {
                    $orSql[] = "(".$this->_getConditionSql($fieldName, $orCondition).")";
                }
                $sql = "(".join(" or ", $orSql).")";
            }
        } else {
            $sql = $this->_read->quoteInto("$fieldName = ?", $condition);
        }
        return $sql;
    }

    /**
     * Get condition sql for the attribute
     *
     * @see self::_getConditionSql
     * @param string $attribute
     * @param mixed $condition
     * @return string
     */
    protected function _getAttributeConditionSql($attribute, $condition)
    {
        if (isset($this->_joinFields[$attribute])) {
            return $this->_getConditionSql($this->_getAttributeFieldName($attribute), $condition);
        }
        // process linked attribute
        if (isset($this->_joinAttributes[$attribute])) {
            $entity = $this->getAttribute($attribute)->getEntity();
            $entityTable = $entity->getEntityTable();
        } else {
            $entity = $this->getEntity();
            $entityTable = 'e';
        }

        if ($entity->isAttributeStatic($attribute)) {
            $conditionSql = $this->_getConditionSql('e.'.$attribute, $condition);
        } else {
            $this->_addAttributeJoin($attribute);
            $conditionSql = $this->_getConditionSql($this->_getAttributeTableAlias($attribute).'.value', $condition);
        }
        return $conditionSql;
    }

    public function setPageSize($pageSize)
    {
        $this->_pageSize = $pageSize;
        return $this;
    }

    public function setCurPage($page)
    {
        $this->_pageStart = $page;
        return $this;
    }

    public function getLastPageNumber()
    {
        $collectionSize = (int) $this->getSize();
        if (0 === $collectionSize) {
            return 1;
        }
        elseif($this->_pageSize) {
            return ceil($collectionSize/$this->_pageSize);
        }
        else{
            return 1;
        }
    }

    public function getCurPage($curPageIncrement = 0)
    {
        return $this->_getPageStart($curPageIncrement);
    }

    protected function _getPageStart($curPageIncrement = 0)
    {
        $this->_pageStart = (int) $this->_pageStart;
        if ($this->_pageStart < 1) {
            $this->_pageStart = 1;
        }
        elseif ($this->_pageStart > $this->getLastPageNumber()) {
        	$this->_pageStart = $this->getLastPageNumber();
        }
        $pageStart = $this->_pageStart + $curPageIncrement;
        if ($pageStart > 0 && $pageStart <= $this->getLastPageNumber()) {
            return $pageStart;
        }
        elseif ($pageStart > $this->getLastPageNumber()) {
        	return $this->getLastPageNumber();
        }
        return 1;
    }

    public function getPageSize()
    {
        return $this->_pageSize;
    }

    /**
     * Get sql for get record count
     *
     * @return  string
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

        $sql = $countSelect->__toString();
        $sql = preg_replace('/^select\s+.+?\s+from\s+/is', 'select count(*) from ', $sql);
        return $sql;
    }

    public function getSize()
    {
        if (is_null($this->_rowCount)) {
            $this->_rowCount = $this->_read->fetchOne($this->getSelectCountSql());
        }
        return $this->_rowCount;
    }

    /**
     * Set sorting order
     *
     * $attribute can also be an array of attributes
     *
     * @param string|array $attribute
     * @param string $dir
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function setOrder($attribute, $dir='desc')
    {
        if (is_array($attribute)) {
            foreach ($attribute as $attr) {
                $this->addAttributeToSort($attr, $dir);
            }
        } else {
            $this->addAttributeToSort($attribute, $dir);
        }
        return $this;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->_items);
    }

    /**
     * Print and/or log query
     *
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return  Varien_Data_Collection_Db
     */
    public function printLogQuery($printQuery = false, $logQuery = false, $sql = null) {
        if ($printQuery) {
            echo is_null($sql) ? $this->getSelect()->__toString() : $sql;
        }

        if ($logQuery){
            Mage::log(is_null($sql) ? $this->getSelect()->__toString() : $sql);
        }
        return $this;
    }

    public function count()
    {
        return count($this->_items);
    }

    public function toArray(array $arrAttributes = array())
    {
        $arr = array();
        foreach ($this->_items as $k=>$item) {
            $arr[$k] = $item->toArray($arrAttributes);
        }
        return $arr;
    }

    protected function _beforeLoad()
    {
        return $this;
    }

    protected function _afterLoad()
    {
        return $this;
    }

    public function clear()
    {
        $this->_items = array();
        $this->_itemsById = array();
        return $this;
    }
}
