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
 * Entity type model
 * 
 */
class Mage_Eav_Model_Entity_Type extends Mage_Core_Model_Abstract
{
    protected $_attributes;
    protected $_attributesBySet = array();
    protected $_sets;
    
    protected function _construct()
    {
        $this->_init('eav/entity_type');
    }

    public function loadByCode($code)
    {
        $this->_getResource()->loadByCode($this, $code);
        return $this;
    }
    
    /**
     * Retrieve entity type attributes collection
     *
     * @param   int $setId
     * @return  Varien_Data_Collection_Db
     */
    public function getAttributeCollection($setId = null)
    {
        if (is_null($setId)) {
            if (is_null($this->_attributes)) {
                $this->_attributes = Mage::getModel('eav/entity_attribute')->getResourceCollection()
                    ->setEntityTypeFilter($this->getId());
            }
            $collection = $this->_attributes;
        }
        else {
            if (!isset($this->_attributesBySet[$setId])) {
                $this->_attributesBySet[$setId] = Mage::getModel('eav/entity_attribute')->getResourceCollection()
                    ->setEntityTypeFilter($this->getId());
            }
            $collection = $this->_attributesBySet[$setId];
        }
        return $collection;
    }
    
    /**
     * Retrieve entity tpe sets collection
     *
     * @return Varien_Data_Collection_Db
     */
    public function getAttributeSetCollection()
    {
        if (empty($this->_sets)) {
            $this->_sets = Mage::getModel('eav/entity_attribute_set')->getResourceCollection()
                ->setEntityTypeFilter($this->getId());
        }
        return $this->_sets;
    }
    
    public function fetchNewIncrementId($storeId=null)
    {
        if (!$this->getIncrementModel()) {
            return false;
        }
        
        if (!$this->getIncrementPerStore()) {
            $storeId = 0;
        } elseif (is_null($storeId)) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Valid store_id is expected!'));
        }
        
        $entityStoreConfig = Mage::getModel('eav/entity_store')
            ->loadByEntityStore($this->getId(), $storeId);
            
        if (!$entityStoreConfig->getId()) {
            $entityStoreConfig
                ->setEntityTypeId($this->getId())
                ->setStoreId($storeId)
                ->setIncrementPrefix($storeId)
                ->save();
        }
        
        $incrementInstance = Mage::getModel($this->getIncrementModel())
            ->setPrefix($entityStoreConfig->getIncrementPrefix())
            ->setPadLength($entityStoreConfig->getIncrementPadLength())
            ->setPadChar($entityStoreConfig->getIncrementPadChar())
            ->setLastId($entityStoreConfig->getIncrementLastId())
        ;
        
        // do read lock on eav/entity_store to solve potential timing issues
        // (most probably already done by beginTransaction of entity save)
        
        $incrementId = $incrementInstance->getNextId();
        
        $entityStoreConfig->setIncrementLastId($incrementId);

        $entityStoreConfig->save();
        
        return $incrementId;
    }
    
    public function getIsDataSharing()
    {
        return $this->getData('is_data_sharing');
    }
    
    public function getEntityIdField()
    {
        return $this->getData('entity_id_field');
    }
    
    public function getEntityTable()
    {
        return $this->getData('entity_table');
    }
    
    public function getValueTablePrefix()
    {
        return $this->getData('value_table_prefix');
    }
    
    public function getDataSharingKey()
    {
        return $this->getData('data_sharing_key');
    }
    
    public function getDefaultAttributeSetId()
    {
        return $this->getData('default_attribute_set_id');
    }
    
    public function getEntityTypeId()
    {
        return $this->getData('entity_type_id');
    }
}
