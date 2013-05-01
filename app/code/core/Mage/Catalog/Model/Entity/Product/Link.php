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
 * Catalog product link resource model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */

class Mage_Catalog_Model_Entity_Product_Link extends Mage_Core_Model_Mysql4_Abstract
{
    protected function  _construct() 
    {
        $this->_init('catalog/product_link', 'link_id');
    }
    
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        foreach(array_unique($object->getAttributeCollection()->getColumnValues('data_type')) as $table) {
            // Loading of link attributes from unique data tables.
            $attributeFirst = $object->getAttributeCollection()->getItemByColumnValue('data_type', $table);
            $select = $this->_getReadAdapter()->select()
                ->from($attributeFirst->getTypeTable())
                ->where('link_id = ?', $object->getId());
            
            $attributesValues = $this->_getReadAdapter()->fetchAll($select);
            foreach ($attributesValues as $attributeValue) {
                $attribute = $object->getAttributeCollection()->getItemById($attributeValue['product_link_attribute_id']);
                if($attribute) {
                    $object->setData($attribute->getCode(), $attributeValue['value']);
                }
            }
        }
        
        return $this;
    }
    
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $originAttributes = array();
        foreach (array_unique($object->getAttributeCollection()->getColumnValues('data_type')) as $table) {
            // Loading of link attributes ids from unique data tables.
            $attributeFirst = $object->getAttributeCollection()->getItemByColumnValue('data_type', $table);
            $select = $this->_getWriteAdapter()->select()
                ->from($attributeFirst->getTypeTable(), array('value_id', 'product_link_attribute_id'))
                ->where('link_id = ?', $object->getId());
            
            $attributesValues = $this->_getWriteAdapter()->fetchAll($select);
    
            foreach ($attributesValues as $attributeValue) {
                $attribute = $object->getAttributeCollection()->getItemById($attributeValue['product_link_attribute_id']);
                
                if($attribute) {
                    $originAttributes[$attribute->getId()] = $attributeValue;
                }
            }
        }
    
        $this->_getWriteAdapter()->beginTransaction();
        try {
            
            foreach ($object->getAttributeCollection() as $attribute)
            {
                
                if(isset($originAttributes[$attribute->getId()]) && trim($object->getData($attribute->getCode()))!='') {
                    // If attribute value exists update existing record
                    $data = array();
                    $data['value'] = $object->getData($attribute->getCode());
                    $condition = $this->_getWriteAdapter()->quoteInto('value_id = ?', $originAttributes[$attribute->getId()]['value_id']);              
                    $this->_getWriteAdapter()->update($attribute->getTypeTable(), $data, $condition);
                } elseif (isset($originAttributes[$attribute->getId()])) {
                    $condition = $this->_getWriteAdapter()->quoteInto('value_id = ?', $originAttributes[$attribute->getId()]['value_id']);              
                    $this->_getWriteAdapter()->delete($attribute->getTypeTable(), $condition);
                } elseif (trim($object->getData($attribute->getCode()))!='') {
                    // If attribute value not empty and not exists insert new record
                    $data = array();
                    $data['value'] = $object->getData($attribute->getCode());
                    $data['product_link_attribute_id'] = $attribute->getId();
                    $data['link_id'] = $object->getId();
                    $this->_getWriteAdapter()->insert($attribute->getTypeTable(), $data);
                }
            }
            
            $this->_getWriteAdapter()->commit();
        }
        catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        }
        
        return $this;
    }
    
}// Class Mage_Catalog_Model_Entity_Product_Link END