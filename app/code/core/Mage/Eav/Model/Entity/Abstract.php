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
 * Entity/Attribute/Model - entity abstract
 *
 * @category   Mage
 * @package    Mage_Eav
 */
abstract class Mage_Eav_Model_Entity_Abstract implements Mage_Eav_Model_Entity_Interface
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
     * Entity type configuration
     *
     * @var Mage_Eav_Model_Entity_Type
     */
    protected $_config;

    /**
     * Attributes array by attribute id
     *
     * @var array
     */
    protected $_attributesById = array();

    /**
     * Attributes array by attribute name
     *
     * @var unknown_type
     */
    protected $_attributesByCode = array();

    /**
     * 2-dimentional array by table name and attribute name
     *
     * @var array
     */
    protected $_attributesByTable = array();

    /**
     * Attributes that are static fields in entity table
     *
     * @var array
     */
    protected $_staticAttributes = array();

    protected $_entityTable;

    protected $_entityIdField;

    protected $_valueEntityIdField;

    protected $_valueTablePrefix;

    protected $_isPartialLoad = false;

    protected $_isPartialSave = false;

    /**
     * Set connections for entity operations
     *
     * @param Zend_Db_Adapter_Abstract $read
     * @param Zend_Db_Adapter_Abstract $write
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function setConnection(Zend_Db_Adapter_Abstract $read, Zend_Db_Adapter_Abstract $write=null)
    {
        $this->_read = $read;
        $this->_write = $write ? $write : $read;
        return $this;
    }

    /**
     * Retrieve read DB connection
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getReadConnection()
    {
        return $this->_read;
    }

    /**
     * Retrieve write DB connection
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getWriteConnection()
    {
        return $this->_write;
    }

    /**
     * For compatibility with Mage_Core_Model_Abstract
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->_entityIdField;
    }

    /**
     * Set configuration for the entity
     *
     * Accepts config node or name of entity type
     *
     * @param string|Mage_Eav_Model_Entity_Type $type
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function setType($type)
    {

        if (is_string($type)) {
            $config = Mage::getSingleton('eav/config')->getEntityType($type);
        } elseif ($type instanceof Mage_Eav_Model_Entity_Type) {
            $config = $type;
        } else {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Unknown parameter'));
        }

        if (!$config) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid entity type %s', $type));
        }

        $this->_config = $config;
        $this->_afterSetConfig();
        return $this;
    }

    /**
     * Retrieve current entity config
     *
     * @return Mage_Eav_Model_Entity_Type
     */
    public function getConfig()
    {
        if (empty($this->_config)) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Entity is not initialized'));
        }
        return $this->_config;
    }

    /**
     * Get entity type name
     *
     * @return string
     */
    public function getType()
    {
        return $this->getConfig()->getEntityTypeCode();
    }

    /**
     * Get entity type id
     *
     * @return integer
     */
    public function getTypeId()
    {
        return (int)$this->getConfig()->getEntityTypeId();
    }

    /**
     * Unset attributes
     *
     * If NULL or not supplied removes configuration of all attributes
     * If string - removes only one, if array - all specified
     *
     * @param array|string|null $attributes
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function unsetAttributes($attributes=null)
    {
        if (empty($attributes)) {
            $this->_attributesByCode = array();
            $this->_attributesById = array();
            $this->_attributesByTable = array();
            return $this;
        }

        if (is_string($attributes)) {
            $attributes = array($attributes);
        }

        if (!is_array($attributes)) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Unknown parameter'));
        }

        foreach ($attributes as $attrCode) {
            if (!isset($this->_attributesByCode[$attrCode])) {
                continue;
            }

            $attr = $this->getAttribute($attrCode);
            unset($this->_attributesById[$attr->getId()]);
            unset($this->_attributesByTable[$attr->getBackend()->getTable()][$attrCode]);
            unset($this->_attributesByCode[$attrCode]);
        }

        return $this;
    }

    /**
     * Retrieve attribute instance by name, id or config node
     *
     * This will add the attribute configuration to entity's attributes cache
     *
     * If attribute is not found false is returned
     *
     * @param string|integer|Mage_Core_Model_Config_Element $attribute
     * @return boolean|Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getAttribute($attribute)
    {
        if (is_numeric($attribute)) {

            $attributeId = $attribute;

            if (isset($this->_attributesById[$attributeId])) {
                return $this->_attributesById[$attributeId];
            }
            $attributeInstance = Mage::getSingleton('eav/config')->getAttribute($this->getTypeId(), $attributeId);
            $attributeCode = $attributeInstance->getAttributeCode();

        } elseif (is_string($attribute)) {

            $attributeCode = $attribute;

            if (isset($this->_attributesByCode[$attributeCode])) {
                return $this->_attributesByCode[$attributeCode];
            }
            $attributeInstance = Mage::getSingleton('eav/config')
                ->getAttribute($this->getTypeId(), $attributeCode);

        } elseif ($attribute instanceof Mage_Eav_Model_Entity_Attribute_Abstract) {

            $attributeInstance = $attribute;
            $attributeCode = $attributeInstance->getAttributeCode();
            if (isset($this->_attributesByCode[$attributeCode])) {
                return $this->_attributesByCode[$attributeCode];
            }
        }

        if (empty($attributeInstance)
            || !($attributeInstance instanceof Mage_Eav_Model_Entity_Attribute_Abstract)
            || !$attributeInstance->getId() ) {
            return false;
        }

        if (empty($attributeId)) {
            $attributeId = $attributeInstance->getAttributeId();
        }

        if (!$attributeInstance->getAttributeCode()) {
            $attributeInstance->setAttributeCode($attributeCode);
        }
        if (!$attributeInstance->getAttributeModel()) {
            $attributeInstance->setAttributeModel($this->_getDefaultAttributeModel());
        }

        $this->addAttribute($attributeInstance);

        return $attributeInstance;
    }

    /**
     * Adding attribute to entity
     *
     * @param   Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @return  Mage_Eav_Model_Entity_Abstract
     */
    public function addAttribute(Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        $attribute->setEntity($this);
        $attributeCode = $attribute->getAttributeCode();

        $this->_attributesByCode[$attributeCode] = $attribute;

        if ($attribute->getBackend()->isStatic()) {
            $this->_staticAttributes[$attributeCode] = $attribute;
        } else {
            $this->_attributesById[$attribute->getId()] = $attribute;

            $attributeTable = $attribute->getBackend()->getTable();
            $this->_attributesByTable[$attributeTable][$attributeCode] = $attribute;
        }
        return $this;
    }

    public function isPartialLoad($flag=null)
    {
        $result = $this->_isPartialLoad;
        if (!is_null($flag)) {
            $this->_isPartialLoad = $flag;
        }
        return $result;
    }

    public function isPartialSave($flag=null)
    {
        $result = $this->_isPartialSave;
        if (!is_null($flag)) {
            $this->_isPartialSave = $flag;
        }
        return $result;
    }

    /**
     * Retrieve configuration for all attributes
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function loadAllAttributes($object=null)
    {
        if (is_null($object)) {
            $setId = null;
        }
        elseif($object->getAttributeSetId()) {
            $setId = $object->getAttributeSetId();
        }
        else {
            $setId = $this->getConfig()->getDefaultAttributeSetId();
        }

        $attributes = $this->getConfig()->getAttributeCollection($setId);
        if ($setId) {
            $attributes->setAttributeSetFilter($setId);
        }
        $attributes->load();

        foreach ($attributes->getItems() as $attribute) {
            $this->getAttribute($attribute);
        }
        return $this;
    }

    /**
     * Walk through the attributes and run method with optional arguments
     *
     * Returns array with results for each attribute
     *
     * if $method is in format "part/method" will run method on specified part
     * for example: $this->walkAttributes('backend/validate');
     *
     * @param string $method
     * @param array $args
     * @param array $part attribute, backend, frontend, source
     * @return array
     */
    public function walkAttributes($partMethod, array $args=array())
    {
        $methodArr = explode('/', $partMethod);
        switch (sizeof($methodArr)) {
            case 1:
                $part = 'attribute';
                $method = $methodArr[0];
                break;

            case 2:
                $part = $methodArr[0];
                $method = $methodArr[1];
                break;
        }
        $results = array();
        foreach ($this->getAttributesByCode() as $attrCode=>$attribute) {
            switch ($part) {
                case 'attribute':
                    $instance = $attribute;
                    break;

                case 'backend':
                    $instance = $attribute->getBackend();
                    break;

                case 'frontend':
                    $instance = $attribute->getFrontend();
                    break;

                case 'source':
                    $instance = $attribute->getSource();
                    break;
            }
            $results[$attrCode] = call_user_func_array(array($instance, $method), $args);
        }
        return $results;
    }

    /**
     * Get attributes by name array
     *
     * @return array
     */
    public function getAttributesByCode()
    {
        return $this->_attributesByCode;
    }

    /**
     * Get attributes by id array
     *
     * @return array
     */
    public function getAttributesById()
    {
        return $this->_attributesById;
    }

    /**
     * Get attributes by table and name array
     *
     * @return array
     */
    public function getAttributesByTable()
    {
        return $this->_attributesByTable;
    }

    /**
     * Get entity table name
     *
     * @return string
     */
    public function getEntityTable()
    {
        if (empty($this->_entityTable)) {
            $table = $this->getConfig()->getEntityTable();
            if (empty($table)) {
                $table = Mage_Eav_Model_Entity::DEFAULT_ENTITY_TABLE;
            }
            $this->_entityTable = Mage::getSingleton('core/resource')->getTableName($table);
        }
        return $this->_entityTable;
    }

    /**
     * Get entity id field name in entity table
     *
     * @return string
     */
    public function getEntityIdField()
    {
        if (empty($this->_entityIdField)) {
            $this->_entityIdField = $this->getConfig()->getEntityIdField();
            if (empty($this->_entityIdField)) {
                $this->_entityIdField = Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD;
            }
        }
        return $this->_entityIdField;
    }

    /**
     * Get default entity id field name in attribute values tables
     *
     * @return string
     */
    public function getValueEntityIdField()
    {
        return $this->getEntityIdField();
    }

    /**
     * Get prefix for value tables
     *
     * @return string
     */
    public function getValueTablePrefix()
    {
        if (empty($this->_valueTablePrefix)) {
            $prefix = (string)$this->getConfig()->getValueTablePrefix();
            if (!empty($prefix)) {
                $this->_valueTablePrefix = Mage::getSingleton('core/resource')->getTableName($prefix);
            } else {
                $this->_valueTablePrefix = $this->getEntityTable();
            }
        }
        return $this->_valueTablePrefix;
    }

    /**
     * Check whether the attribute is a real field in entity table
     *
     * @see Mage_Eav_Model_Entity_Abstract::getAttribute for $attribute format
     * @param integer|string|Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @return unknown
     */
    public function isAttributeStatic($attribute)
    {
        $attrInstance = $this->getAttribute($attribute);
        return $attrInstance && $attrInstance->getBackend()->isStatic();
    }

    /**
     * Validate all object's attributes against configuration
     *
     * @param Varien_Object $object
     * @return Varien_Object
     */
    public function validate($object)
    {
        $this->loadAllAttributes();
        $this->walkAttributes('backend/validate', array($object));

        return $this;
    }

    /**
     * Retrieve select object for loading base entity row
     *
     * @param   Varien_Object $object
     * @param   mixed $rowId
     * @return  Zend_Db_Select
     */
    protected function _getLoadRowSelect($object, $rowId)
    {
        $select = $this->_read->select()
            ->from($this->getEntityTable())
            ->where('entity_type_id=?', $this->getConfig()->getId())
            ->where($this->getEntityIdField()."=?", $rowId);

        /*if ($this->getUseDataSharing()) {
            $select->where("store_id in (?)", $this->getSharedStoreIds());
        }*/

        return $select;
    }

    /**
     * Retrieve select object for loading entity attributes values
     *
     * @param   Varien_Object $object
     * @param   mixed $rowId
     * @return  Zend_Db_Select
     */
    protected function _getLoadAttributesSelect($object, $table)
    {
        $select = $this->_read->select()
            ->from($table)
            ->where($this->getEntityIdField() . '=?', $object->getId());
            /*->where("store_id=?", $storeId)*/;
        return $select;
    }

    /**
     * Load entity's attributes into the object
     *
     * @param   Varien_Object $object
     * @param   integer $entityId
     * @param   array|null $attributes
     * @return  Mage_Eav_Model_Entity_Abstract
     */
    public function load($object, $entityId, $attributes=array())
    {
        if (!$this->_read) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('No connection available'));
        }

        $select = $this->_getLoadRowSelect($object, $entityId);

        $row = $this->_read->fetchRow($select);
        $object->setData($row);

        if (empty($attributes)) {
            $this->loadAllAttributes($object);
        } else {
            foreach ($attributes as $attrCode) {
                $this->getAttribute($attrCode);
            }
        }

        foreach ($this->getAttributesByTable() as $table=>$attributes) {
            $select = $this->_getLoadAttributesSelect($object, $table);
            $values = $this->_read->fetchAll($select);
            if (empty($values)) {
                continue;
            }

            foreach ($values as $v) {
                $attribute = $this->getAttribute($v['attribute_id']);
                if (!$attribute) {
                    continue;
                }
                $attributeCode = $attribute->getAttributeCode();
                $object->setData($attributeCode, $v['value']);
                $this->getAttribute($v['attribute_id'])->getBackend()->setValueId($v['value_id']);
            }
        }

        $object->setOrigData();
        $this->_afterLoad($object);
        return $this;
    }

    /**
     * Save entity's attributes into the object's resource
     *
     * @param Varien_Object $object
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function save(Varien_Object $object)
    {
        if (!$this->_write) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('No connection available'));
        }

        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        if (!$this->isPartialSave()) {
            $this->loadAllAttributes($object);
        }

        if (!$object->getEntityTypeId()) {
            $object->setEntityTypeId($this->getTypeId());
        }

        /*if ($this->getUseDataSharing() && !$object->getStoreId()) {
            $object->setStoreId($this->getStoreId());
        }*/

        if (is_null($object->getParentId())) {
            $object->setParentId(0);
        }

        $this->_write->beginTransaction();

        $this->_beforeSave($object);

        try {
            $this->_processSaveData($this->_collectSaveData($object));
            $this->_write->commit();
        } catch (Exception $e) {
            $this->_write->rollback();
            throw $e;
        }

        $this->_afterSave($object);


        return $this;
    }


    public function saveAttribute(Varien_Object $object, $attributeCode)
    {
        $attribute = $this->getAttribute($attributeCode);
        $backend = $attribute->getBackend();
        $table = $backend->getTable();
        $entity = $attribute->getEntity();
        $entityIdField = $entity->getEntityIdField();
        $row = array(
            'entity_type_id' => $entity->getTypeId(),
            'attribute_id' => $attribute->getId(),
            //'store_id' => $object->getStoreId(),
            $entityIdField=> $object->getData($entityIdField),
        );
        $newValue = $object->getData($attributeCode);
        if ($newValue==='') {
            $attrType = $backend->getType();
            if ($attrType=='int' || $attrType=='decimal' || $attrType=='datetime') {
                $newValue = null;
            }
        }
        $whereArr = array();
        foreach ($row as $f=>$v) {
            $whereArr[] = $this->_read->quoteInto("$f=?", $v);
        }
        $where = '('.join(') AND (', $whereArr).')';

        $this->_write->beginTransaction();

        try {
            $select = $this->_read->select()->from($table, 'value_id')->where($where);
            $origValueId = $this->_read->fetchOne($select);

            if ($origValueId === false && !is_null($newValue)) {
                $this->_insertAttribute($object, $attribute, $newValue);
                $backend->setValueId($this->_write->lastInsertId());

            } elseif ($origValueId !== false && !is_null($newValue)) {
                $this->_updateAttribute($object, $attribute, $origValueId, $newValue);
                //$this->_write->update($table, array('value'=>$newValue), $where);
            } elseif ($origValueId !== false && is_null($newValue)) {
                $this->_write->delete($table, $where);
            }

            $this->_write->commit();
        } catch (Exception $e) {
            $this->_write->rollback();
            throw $e;
        }

        return $this;
    }

    /**
     * Delete entity using current object's data
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function delete($object)
    {
        if (!$this->_write) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('No connection available'));
        }
        #$object = $this->getObject();

        if (is_numeric($object)) {
            $id = (int)$object;
        } elseif ($object instanceof Varien_Object) {
            $id = (int)$object->getData($this->getEntityIdField());
        }

        $this->_write->beginTransaction();

        $this->_beforeDelete($object);

        try {
            $this->_write->delete($this->getEntityTable(), $this->getEntityIdField()."=".$id);
            $this->loadAllAttributes();
            foreach ($this->getAttributesByTable() as $table=>$attributes) {
                $this->_write->delete($table, $this->getEntityIdField()."=".$id);
            }
        } catch (Exception $e) {
            $this->_write->rollback();
            throw $e;
        }

        $this->_afterDelete($object);

        $this->_write->commit();

        return $this;
    }


    /**
     * Start resource transaction
     *
     * @return Mage_Core_Model_Resource_Abstract
     */
    public function beginTransaction()
    {
        $this->_write->beginTransaction();
        return $this;
    }

    /**
     * Commit resource transaction
     *
     * @return Mage_Core_Model_Resource_Abstract
     */
    public function commit()
    {
        $this->_write->commit();
        return $this;
    }

    /**
     * Roll back resource transaction
     *
     * @return Mage_Core_Model_Resource_Abstract
     */
    public function rollBack()
    {
        $this->_write->rollBack();
        return $this;
    }

    public function setNewIncrementId(Varien_Object $object)
    {
        if ($object->getIncrementId()) {
            return $this;
        }

        $incrementId = $this->getConfig()->fetchNewIncrementId($object->getStoreId());

        if (false!==$incrementId) {
            $object->setIncrementId($incrementId);
        }

        return $this;
    }

    protected function _collectSaveData($newObject)
    {
        #$newObject = $this->getObject();
        $newData = $newObject->getData();

        $entityId = $newObject->getData($this->getEntityIdField());
        if (!empty($entityId)) {
            // get current data in db for this entity
            $className = get_class($newObject);
            $origObject = new $className();
//            $origObject = clone $newObject;
            $origObject->setData(array());
            //$this->load($origObject, $entityId, array_keys($this->_attributesByCode));
            $this->load($origObject, $entityId);
            $origData = $origObject->getOrigData();

            // drop attributes that are unknown in new data
            // not needed after introduction of partial entity loading
            foreach ($origData as $k=>$v) {
                if (!array_key_exists($k, $newData)) {
                    unset($origData[$k]);
                    continue;
                }
            }
        }

        foreach ($newData as $k=>$v) {
            if (is_numeric($k) || is_array($v)) {
                continue;
                throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid data object key'));
            }

            $attribute = $this->getAttribute($k);
            if (empty($attribute)) {
                continue;
            }

            $isGlobal = $attribute->getIsGlobal();
            $attrId = $attribute->getAttributeId();
            // if attribute is static add to entity row and continue
            if ($this->isAttributeStatic($k)) {
                $entityRow[$k] = $v;
                unset($newData[$k]);
                if (isset($origData)) {
                    unset($origData[$k]);
                }
                continue;
            }
            $attrType = $attribute->getBackend()->getType();
            $isEmpty = is_array($v)
                || is_null($v)
                || $v===false && $attrType!='int'
                || $v==='' && ($attrType=='int' || $attrType=='decimal' || $attrType=='datetime');

            if (isset($origData[$k])) {
                //if (is_null($v) || strlen($v)==0) {
                $attrType = $attribute->getBackend()->getType();
                if ($isEmpty) {
                    if ($isGlobal) {
                        $delete[$attribute->getBackend()->getTable()]['attribute_ids'][] = $attrId;
                    } else {
                        $delete[$attribute->getBackend()->getTable()]['value_ids'][] = $attribute->getBackend()->getValueId();
                    }
                } elseif ($v!==$origData[$k]) {
                    $update[$attrId] = array(
                        'value_id' => $attribute->getBackend()->getValueId(),
                        'value'    => $v,
                    );
                }
            }
            // If value is not empty or value eq 0
            elseif (!$isEmpty) {
                $insert[$attrId] = $v;
            }
        }

        $result = compact('newObject', 'entityRow', 'insert', 'update', 'delete');
        return $result;
    }

    protected function _processSaveData($saveData)
    {
        /**
         * $saveData = array(
         *  'newObject',
         *  'entityRow',
         *  'insert',
         *  'update',
         *  'delete'
         * )
         */
        extract($saveData);

        $insertEntity = true;
        $entityIdField = $this->getEntityIdField();
        $entityId = $newObject->getData($entityIdField);
        $condition = $this->_write->quoteInto("$entityIdField=?", $entityId);

        if (!empty($entityId)) {
            $select = $this->_write->select()
                ->from($this->getEntityTable(), $entityIdField)
                ->where($condition);
            if ($this->_write->fetchOne($select)) {
                $insertEntity = false;
            }
        }

        if ($insertEntity) {
            // insert entity table row
            $this->_write->insert($this->getEntityTable(), $entityRow);
            $entityId = $this->_write->lastInsertId();
            $newObject->setId($entityId);
        } else {
            // update entity table row
            $this->_write->update($this->getEntityTable(), $entityRow, $condition);
        }

        // insert attribute values
        if (!empty($insert)) {
            foreach ($insert as $attrId=>$value) {
                $attribute = $this->getAttribute($attrId);
                $this->_insertAttribute($newObject, $attribute, $value);
            }
        }

        // update attribute values
        if (!empty($update)) {
            foreach ($update as $attrId=>$v) {
                $attribute = $this->getAttribute($attrId);
                $this->_updateAttribute($newObject, $attribute, $v['value_id'], $v['value']);
            }
        }

        // delete empty attribute values
        if (!empty($delete)) {
            foreach ($delete as $table=>$values) {
                if (!empty($values['value_ids'])) {
                    $this->_write->delete($table, $this->_write->quoteInto('value_id in (?)', $values['value_ids']));
                }
                if (!empty($values['attribute_ids'])) {
                    $this->_write->delete($table, "entity_id='".$entityId."' and ".$this->_write->quoteInto('attribute_id in (?)', $values['attribute_ids']));
                }
            }
        }

        return $this;
    }

    protected function _insertAttribute($object, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $value, $storeIds = array())
    {
        $entityIdField = $attribute->getBackend()->getEntityIdField();
        $row = array(
            $entityIdField  => $object->getId(),
            'entity_type_id'=> $object->getEntityTypeId(),
            //'store_id'      => $object->getStoreId(),
            'attribute_id'  => $attribute->getId(),
            'value'         => $value,
        );
        // If we need save attribute in multiple store
        if (empty($storeIds)) {
            $this->_write->insert($attribute->getBackend()->getTable(), $row);
        }
        else {
            foreach ($storeIds as $storeId) {
                $row['store_id'] = $storeId;
                // Check existing of value for store
                $select = $this->_write->select()
                   ->from($attribute->getBackend()->getTable())
                   ->where($this->_write->quoteInto($entityIdField.'=?', $object->getId()))
                   ->where($this->_write->quoteInto('entity_type_id=?', $object->getEntityTypeId()))
                   ->where($this->_write->quoteInto('store_id=?', $storeId))
                   ->where($this->_write->quoteInto('attribute_id=?', $attribute->getId()));

                if ($this->_write->fetchOne($select)) {
                    $this->_write->update(
                        $attribute->getBackend()->getTable(),
                        array('value'=>$value),
                        implode(' ', $select->getPart(Zend_Db_Select::WHERE))
                    );
                }
                else {
                    $this->_write->insert($attribute->getBackend()->getTable(), $row);
                }
            }
        }
        return $this;
    }

    protected function _updateAttribute($object, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $valueId, $value)
    {
        if ((bool)$attribute->getIsGlobal()) {
            $this->_write->update($attribute->getBackend()->getTable(),
                array('value'=>$value),
                'entity_type_id='.(int)$object->getEntityTypeId() . ' AND
                 entity_id='.(int)$object->getId().' AND
                 attribute_id='.(int)$attribute->getId()
            );

        }
        else {
            $this->_write->update($attribute->getBackend()->getTable(),
                array('value'=>$value),
                'value_id='.(int)$valueId
            );
        }
        return $this;
    }

    public function checkAttributeUniqueValue(Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $object)
    {
        if ($attribute->getBackend()->getType()==='static') {
            $select = $this->_write->select()
                ->from($this->getEntityTable(), $this->getEntityIdField())
                ->where('entity_type_id=?', $this->getConfig()->getId())
                ->where($attribute->getAttributeCode().'=?', $object->getData($attribute->getAttributeCode()));
        } else {
            $select = $this->_write->select()
                ->from($attribute->getBackend()->getTable(), $attribute->getBackend()->getEntityIdField())
                ->where('entity_type_id=?', $this->getConfig()->getId())
                ->where('attribute_id=?', $attribute->getId())
                ->where('value=?', $object->getData($attribute->getAttributeCode()))
                ->where('store_id IN (?)', $this->getSharedStoreIds());
        }
        $data = $this->_write->fetchCol($select);

        if ($object->getId()) {
            if (isset($data[0])) {
                return $data[0] == $object->getId();
            }
            return true;
        }
        else {
            return !count($data);
        }
    }

    protected function _afterLoad(Varien_Object $object)
    {
        $this->walkAttributes('backend/afterLoad', array($object));
    }

    protected function _beforeSave(Varien_Object $object)
    {
        $this->walkAttributes('backend/beforeSave', array($object));
    }

    protected function _afterSave(Varien_Object $object)
    {
        $this->walkAttributes('backend/afterSave', array($object));
    }

    protected function _beforeDelete(Varien_Object $object)
    {
        $this->walkAttributes('backend/beforeDelete', array($object));
    }

    protected function _afterDelete(Varien_Object $object)
    {
        $this->walkAttributes('backend/afterDelete', array($object));
    }

    protected function _getDefaultAttributeModel()
    {
        return Mage_Eav_Model_Entity::DEFAULT_ATTRIBUTE_MODEL;
    }

    protected function _getDefaultAttributes()
    {
        return array('entity_type_id', 'attribute_set_id', 'created_at', 'updated_at', 'parent_id', 'increment_id');
    }

    protected function _afterSetConfig()
    {

        $defaultAttributes = $this->_getDefaultAttributes();

        /*if ($this->getConfig()->getIsDataSharing()) {
            $defaultAttributes[] = 'store_id';
        }*/

        $defaultAttributes[] = $this->getEntityIdField();

        $attributes = $this->getAttributesByCode();
        foreach ($defaultAttributes as $attr) {
            if (empty($attributes[$attr]) && !$this->getAttribute($attr)) {
                $attribute = Mage::getModel('eav/entity_attribute');
                $attribute->setAttributeCode($attr);
                $attribute->setBackendType('static');
                $this->addAttribute($attribute);
            }
        }
    }

    public function getDefaultAttributeSourceModel()
    {
        return Mage_Eav_Model_Entity::DEFAULT_SOURCE_MODEL;
    }
}
