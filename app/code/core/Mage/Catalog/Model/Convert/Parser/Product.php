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


class Mage_Catalog_Model_Convert_Parser_Product extends Mage_Eav_Model_Convert_Parser_Abstract
{
    protected $_resource;

    /**
     * Product collections per store
     *
     * @var array
     */
    protected $_collections;

    protected $_productTypes = array(
        1=>'Simple Product',
        2=>'Bundle',
        3=>'Configurable Product',
        4=>'Grouped Product',
    );

    /**
     * @return Mage_Catalog_Model_Mysql4_Convert
     */
    public function getResource()
    {
        if (!$this->_resource) {
            $this->_resource = Mage::getResourceSingleton('catalog_entity/convert');
                #->loadStores()
                #->loadProducts()
                #->loadAttributeSets()
                #->loadAttributeOptions();
        }
        return $this->_resource;
    }

    public function getCollection($storeId)
    {
        if (!isset($this->_collections[$storeId])) {
            $this->_collections[$storeId] = Mage::getResourceModel('catalog/product_collection');
            $this->_collections[$storeId]->getEntity()->setStore($storeId);
        }
        return $this->_collections[$storeId];
    }

    public function getProductTypeName($id)
    {
        return isset($this->_productTypes[$id]) ? $this->_productTypes[$id] : false;
    }

    public function getProductTypeId($name)
    {
        return array_search($name, $this->_productTypes);
    }

    public function parse()
    {
        $data = $this->getData();

        $entityTypeId = Mage::getSingleton('eav/config')->getEntityType('catalog_product')->getId();

        $result = array();
        foreach ($data as $i=>$row) {
            $this->setPosition('Line: '.($i+1));
            try {
                // validate SKU
                if (empty($row['sku'])) {
                    $this->addException(Mage::helper('catalog')->__('Missing SKU, skipping the record'), Varien_Convert_Exception::ERROR);
                    continue;
                }
                $this->setPosition('Line: '.($i+1).', SKU: '.$row['sku']);

                // try to get entity_id by sku if not set
                if (empty($row['entity_id'])) {
                    $row['entity_id'] = $this->getResource()->getProductIdBySku($row['sku']);
                }

                // if attribute_set not set use default
                if (empty($row['attribute_set'])) {
                    $row['attribute_set'] = 'Default';
                }
                // get attribute_set_id, if not throw error
                $row['attribute_set_id'] = $this->getAttributeSetId($entityTypeId, $row['attribute_set']);
                if (!$row['attribute_set_id']) {
                    $this->addException(Mage::helper('catalog')->__("Invalid attribute set specified, skipping the record"), Varien_Convert_Exception::ERROR);
                    continue;
                }

                if (empty($row['type'])) {
                    $row['type'] = 'Simple Product';
                }
                // get product type_id, if not throw error
                $row['type_id'] = $this->getProductTypeId($row['type']);
                if (!$row['type_id']) {
                    $this->addException(Mage::helper('catalog')->__("Invalid product type specified, skipping the record"), Varien_Convert_Exception::ERROR);
                    continue;
                }

                // get store ids
                $storeIds = $this->getStoreIds(isset($row['store']) ? $row['store'] : $this->getVar('store'));
                if (!$storeIds) {
                    $this->addException(Mage::helper('catalog')->__("Invalid store specified, skipping the record"), Varien_Convert_Exception::ERROR);
                    continue;
                }

                // import data
                $rowError = false;
                foreach ($storeIds as $storeId) {
                    $collection = $this->getCollection($storeId);
                    $entity = $collection->getEntity();

                    $model = Mage::getModel('catalog/product');
                    $model->setStoreId($storeId);
                    if (!empty($row['entity_id'])) {
                        $model->load($row['entity_id']);
                    }
                    foreach ($row as $field=>$value) {
                        $attribute = $entity->getAttribute($field);

                        if (!$attribute) {
                            continue;
                            #$this->addException(Mage::helper('catalog')->__("Unknown attribute: %s", $field), Varien_Convert_Exception::ERROR);
                        }
                        if ($attribute->usesSource()) {
                            $source = $attribute->getSource();
                            $optionId = $this->getSourceOptionId($source, $value);
                            if (is_null($optionId)) {
                                $rowError = true;
                                $this->addException(Mage::helper('catalog')->__("Invalid attribute option specified for attribute %s (%s), skipping the record", $field, $value), Varien_Convert_Exception::ERROR);
                                continue;
                            }
                            $value = $optionId;
                        }
                        $model->setData($field, $value);

                    }//foreach ($row as $field=>$value)
                    if (!$rowError) {
                        $collection->addItem($model);
                    }
                } //foreach ($storeIds as $storeId)
            } catch (Exception $e) {
                if (!$e instanceof Varien_Convert_Exception) {
                    $this->addException(Mage::helper('catalog')->__("Error during retrieval of option value: %s", $e->getMessage()), Varien_Convert_Exception::FATAL);
                }
            }
        }
        $this->setData($this->_collections);
        return $this;
    }

    public function unparse()
    {
        $systemFields = array('store_id', 'attribute_set_id', 'entity_type_id', 'parent_id', 'created_at', 'updated_at', 'type_id');
        $collections = $this->getData();

        if ($collections instanceof Mage_Eav_Model_Entity_Collection_Abstract) {
            $collections = array($collections->getEntity()->getStoreId()=>$collections);
        } elseif (!is_array($collections)) {
            $this->addException(Mage::helper('catalog')->__("Array of Entity collections is expected"), Varien_Convert_Exception::FATAL);
        }

        foreach ($collections as $storeId=>$collection) {
            if (!$collection instanceof Mage_Eav_Model_Entity_Collection_Abstract) {
                $this->addException(Mage::helper('catalog')->__("Entity collection is expected"), Varien_Convert_Exception::FATAL);
            }

            $data = array();
            foreach ($collection->getIterator() as $i=>$model) {
                $this->setPosition('Line: '.($i+1).', SKU: '.$model->getSku());

                $row = array(
                    'store'=>$this->getStoreCode($this->getVar('store') ? $this->getVar('store') : $storeId),
                    'attribute_set'=>$this->getAttributeSetName($model->getEntityTypeId(), $model->getAttributeSetId()),
                    'type'=>$this->getProductTypeName($model->getTypeId()),
                );

                foreach ($model->getData() as $field=>$value) {
                    if (in_array($field, $systemFields)) {
                        continue;
                    }
                    $attribute = $model->getResource()->getAttribute($field);
                    if (!$attribute) {
                        continue;
                    }

                    if ($attribute->usesSource()) {
                        $option = $attribute->getSource()->getOptionText($value);
                        if (empty($option) || is_array($option) && !isset($option['label'])) {
                            $this->addException(Mage::helper('catalog')->__("Invalid option id specified for %s (%s), skipping the record", $field, $value), Varien_Convert_Exception::ERROR);
                            continue;
                        }
                        if (is_array($option)) {
                            $value = $option['label'];
                        } else {
                            $value = $option;
                        }
                    }
                    $row[$field] = $value;
                }
                $data[] = $row;
            }
        }

        $this->setData($data);
        return $this;
    }

    public function getExternalAttributes()
    {
        $internal = array();

        $entityTypeId = Mage::getSingleton('eav/config')->getEntityType('catalog_product')->getId();
        $productAttributes = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($entityTypeId)
            ->load()->getIterator();

        $attributes = array(
            'store'=>'store',
            'attribute_set'=>'attribute_set',
            'type'=>'type',
            'entity_id'=>'entity_id',
        );

        foreach ($productAttributes as $attr) {
            $code = $attr->getAttributeCode();
            if (in_array($code, $internal) || $attr->getFrontendInput()=='hidden') {
                continue;
            }
            $attributes[$code] = $code;
        }

        return $attributes;
    }
}