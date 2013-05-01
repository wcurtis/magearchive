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
 * Entity/Attribute/Model - attribute abstract
 *
 * @category   Mage
 * @package    Mage_Eav
 */
abstract class Mage_Eav_Model_Entity_Attribute_Abstract extends Mage_Core_Model_Abstract implements Mage_Eav_Model_Entity_Attribute_Interface
{
    /**
     * Attribute name
     *
     * @var string
     */
    protected $_name;

    /**
     * Entity instance
     *
     * @var Mage_Eav_Model_Entity_Abstract
     */
    protected $_entity;

    /**
     * Backend instance
     *
     * @var Mage_Eav_Model_Entity_Attribute_Backend_Abstract
     */
    protected $_backend;

    /**
     * Frontend instance
     *
     * @var Mage_Eav_Model_Entity_Attribute_Frontend_Abstract
     */
    protected $_frontend;

    /**
     * Source instance
     *
     * @var Mage_Eav_Model_Entity_Attribute_Source_Abstract
     */
    protected $_source;

    protected function _construct()
    {
        $this->_init('eav/entity_attribute');
    }

    public function loadByCode($entityType, $code)
    {
        if (is_numeric($entityType)) {
            $entityTypeId = $entityType;
        } elseif (is_string($entityType)) {
            $entityType = Mage::getModel('eav/entity_type')->loadByCode($entityType);
        }
        if ($entityType instanceof Mage_Eav_Model_Entity_Type) {
            $entityTypeId = $entityType->getId();
        }
        if (empty($entityTypeId)) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid entity supplied'));
        }
        $this->_getResource()->loadByCode($this, $entityTypeId, $code);
        return $this;
    }

    /**
     * Retrieve attribute configuration (deprecated)
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getConfig()
    {
        return $this;
    }

    /**
     * Get attribute name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('attribute_code');
    }

    public function setAttributeId($data)
    {
        return $this->setData('attribute_id', $data);
    }

    public function getAttributeId()
    {
        return $this->getData('attribute_id');
    }

    public function setAttributeCode($data)
    {
        return $this->setData('attribute_code', $data);
    }

    public function getAttributeCode()
    {
        return $this->getData('attribute_code');
    }

    public function setAttributeModel($data)
    {
        return $this->setData('attribute_model', $data);
    }

    public function getAttributeModel()
    {
        return $this->getData('attribute_model');
    }

    public function setBackendType($data)
    {
        return $this->setData('backend_type', $data);
    }

    public function getBackendType()
    {
        return $this->getData('backend_type');
    }

    public function setBackendModel($data)
    {
        return $this->setData('backend_model', $data);
    }

    public function getBackendModel()
    {
        return $this->getData('backend_model');
    }

    public function setBackendTable($data)
    {
        return $this->setData('backend_table', $data);
    }

    public function getBackendTable()
    {
        return $this->getData('backend_table');
    }

    public function getIsVisibleOnFront()
    {
        return $this->getData('is_visible_on_front');
    }

    public function getDefaultValue()
    {
        return $this->getData('default_value');
    }

    /**
     * Get attribute alias as "entity_type/attribute_code"
     *
     * @param Mage_Eav_Model_Entity_Abstract $entity exclude this entity
     * @return string
     */
    public function getAlias($entity=null)
    {
        $alias = '';
        if (is_null($entity) || ($entity->getType() !== $this->getEntity()->getType())) {
            $alias .= $this->getEntity()->getType() . '/';
        }
        $alias .= $this->getAttributeCode();
        return  $alias;
    }

    /**
     * Set attribute name
     *
     * @param   string $name
     * @return  Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function setName($name)
    {
        return $this->setData('attribute_code', $name);
    }

    /**
     * Set attribute entity instance
     *
     * @param Mage_Eav_Model_Entity_Abstract $entity
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function setEntity($entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    /**
     * Retrieve entity instance
     *
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    public function getEntityIdField()
    {
        return $this->getEntity()->getValueEntityIdField();
    }

    /**
     * Retrieve backend instance
     *
     * @return Mage_Eav_Model_Entity_Attribute_Backend_Abstract
     */
    public function getBackend()
    {
        if (empty($this->_backend)) {
            if (!$this->getBackendModel()) {
                $this->setBackendModel($this->_getDefaultBackendModel());
            }
            $backend = Mage::getModel($this->getBackendModel());
            if (!$backend) {
                throw Mage::exception('Mage_Eav', 'Invalid backend model specified: '.$this->getBackendModel());
            }
            $this->_backend = $backend->setAttribute($this);
        }
        return $this->_backend;
    }

    /**
     * Retrieve frontend instance
     *
     * @return Mage_Eav_Model_Entity_Attribute_Frontend_Abstract
     */
    public function getFrontend()
    {
        if (empty($this->_frontend)) {
            if (!$this->getFrontendModel()) {
                $this->setFrontendModel($this->_getDefaultFrontendModel());
            }
            $this->_frontend = Mage::getModel($this->getFrontendModel())
                ->setAttribute($this);
        }
        return $this->_frontend;
    }

    /**
     * Retrieve source instance
     *
     * @return Mage_Eav_Model_Entity_Attribute_Source_Abstract
     */
    public function getSource()
    {
        if (empty($this->_source)) {
            if (!$this->getSourceModel()) {
                $this->setSourceModel($this->_getDefaultSourceModel());
            }
            $this->_source = Mage::getModel($this->getSourceModel())
                ->setAttribute($this);
        }
        return $this->_source;
    }

    public function usesSource()
    {
        return $this->getFrontendInput()==='select' || $this->getFrontendInput()==='multiselect';
    }

    protected function _getDefaultBackendModel()
    {
        return Mage_Eav_Model_Entity::DEFAULT_BACKEND_MODEL;
    }

    protected function _getDefaultFrontendModel()
    {
        return Mage_Eav_Model_Entity::DEFAULT_FRONTEND_MODEL;
    }

    protected function _getDefaultSourceModel()
    {
        return $this->getEntity()->getDefaultAttributeSourceModel();
    }
}
