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
 * Catalog product bundle option resource model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
 class Mage_Catalog_Model_Entity_Product_Bundle_Option extends Mage_Core_Model_Mysql4_Abstract 
 {
    protected function _construct() 
    {
        $this->_init('catalog/product_bundle_option', 'option_id'); 
    }
    
    /**
     * Load an object
     *
     * @param Varien_Object $object
     * @param integer $id
     * @return boolean
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field=null)
    {
        if (is_null($field)) {
            $field = $this->getIdFieldName();
        }

        $read = $this->_getReadAdapter();

        $select = $read->select()->from(array('main'=>$this->getMainTable()))
            ->joinLeft(array('value'=>$this->getTable('product_bundle_option_value')),
                       'main.option_id=value.option_id AND value.store_id='.(int) $object->getStoreId(),
                       array('position','label','store_id'))
            ->where("main.$field=?", $value);
        $data = $read->fetchRow($select);

        if (!$data) {
            return false;
        }

        $object->setData($data);

        $this->_afterLoad($object);

        return true;
    }
    
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getWriteAdapter()->select()
            ->from($this->getTable('product_bundle_option_value'),'value_id')
            ->where('option_id=?', $object->getId())
            ->where('store_id=?',  (int)$object->getStoreId());
        
        $valueId = $this->_getWriteAdapter()->fetchOne($select);
        
        $data = array();
        
        $data['option_id'] = $object->getId();
        $data['store_id']  = $object->getStoreId(); 
        $data['label']     = $object->getLabel();
        $data['position']  = $object->getPosition();
        
        $this->_getWriteAdapter()->beginTransaction();
        try {
            if($valueId) {
                $this->_getWriteAdapter()->update($this->getTable('product_bundle_option_value'), $data,
                                                     'value_id='.(int)$valueId);
            } else {
                $this->_getWriteAdapter()->insert($this->getTable('product_bundle_option_value'), $data);
            }
            $this->_getWriteAdapter()->commit();
        }
        catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        }
        return $this;
    }
    
    public function toArray(Mage_Core_Model_Abstract $object) {
        return array(
                     'label'    =>  $object->getLabel(),
                     'position' =>  $object->getPosition(),
                     'id'       =>  $object->getId(),
                     'products' =>  $object->getLinkCollection()->toArray());
    }
 } // Class Mage_Catalog_Model_Entity_Product_Bundle_Option end