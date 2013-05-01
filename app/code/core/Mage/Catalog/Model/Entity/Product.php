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
 * Product entity resource model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Entity_Product extends Mage_Catalog_Model_Entity_Abstract
{
    protected $_productStoreTable;
    protected $_categoryProductTable;

    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->setType('catalog_product')
            ->setConnection(
                $resource->getConnection('catalog_read'),
                $resource->getConnection('catalog_write')
            );

        $this->_productStoreTable   = $resource->getTableName('catalog/product_store');
        $this->_categoryProductTable= $resource->getTableName('catalog/category_product');
    }

    public function getIdBySku($sku)
    {
         return $this->_read->fetchOne('select entity_id from '.$this->getEntityTable().' where sku=?', $sku);
    }

    protected function _afterLoad(Varien_Object $object)
    {
        Mage::dispatchEvent('catalog_product_load_after', array('product'=>$object));
        parent::_afterLoad($object);
        return $this;
    }

    protected function _beforeSave(Varien_Object $object)
    {
        Mage::dispatchEvent('catalog_product_save_before', array('product'=>$object));
        if (!$object->getId() && $object->getSku()) {
           $object->setId($this->getIdBySku($object->getSku()));
        }

        return parent::_beforeSave($object);
    }

    protected function _afterSave(Varien_Object $object)
    {
        Mage::dispatchEvent('catalog_product_save_after', array('product'=>$object));
        parent::_afterSave($object);

        $this->_saveBundle($object)
            ->_saveSuperConfig($object)
            ->_saveStores($object)
            ->_saveCategories($object)
            ->_saveLinkedProducts($object);

    	return $this;
    }

    protected function _insertAttribute($object, Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $value, $storeIds = array())
    {
        return parent::_insertAttribute($object, $attribute, $value, $this->getStoreIds($object));
    }

    /**
     * Save product stores configuration
     *
     * @param   Varien_Object $object
     * @return  this
     */
    protected function _saveStores(Varien_Object $object)
    {
        $postedStores = $object->getPostedStores();

        // If product saving from some store
        if ($object->getStoreId()) {
            if (!is_null($postedStores) && empty($postedStores)) {
                $this->_removeFromStore($object, $object->getStoreId());
                $object->setData('store_id', null);
                $object->setStoresChangedFlag(true);
            }
        }
        // If product saving from default store
        else {
            // Retrieve current stores collection of product
            $storeIds = $this->getStoreIds($object);

            if (!isset($postedStores[0])) {
                $postedStores[0] = false;
            }

            $postedStoresIds = array_keys($postedStores);

            $insertStoreIds = array_diff($postedStoresIds, $storeIds);
            $deleteStoreIds = array_diff($storeIds, $postedStoresIds);

            if (sizeof($insertStoreIds) > 0 || sizeof($deleteStoreIds) > 0) {
                $object->setStoresChangedFlag(true);
            }

            // Insert in stores
            foreach ($insertStoreIds as $storeId) {
            	$this->_insertToStore($object, $storeId, $postedStores[$storeId]);
            }

            // Delete product from stores
            foreach ($deleteStoreIds as $storeId) {
            	$this->_removeFromStore($object, $storeId);
            }
        }
        return $this;
    }

    /**
     * Remove product data from some store
     *
     * @param   Mage_Catalog_Model_Product $product
     * @param   int $storeId
     * @return  this
     */
    protected function _removeFromStore($product, $storeId)
    {
        $attributes = $this->getAttributesByTable();
        $tables = array_keys($attributes);
        foreach ($tables as $tableName) {
            $this->getWriteConnection()->delete(
                $tableName,
                $this->getWriteConnection()->quoteInto('store_id=? AND ', $storeId).
                $this->getWriteConnection()->quoteInto($this->getEntityIdField().'=? ', $product->getData($this->getEntityIdField()))
            );
        }

        $this->getWriteConnection()->delete(
            $this->_productStoreTable,
            $this->getWriteConnection()->quoteInto('product_id=? AND ', $product->getId()).
            $this->getWriteConnection()->quoteInto('store_id=?', $storeId)
        );
        return $this;
    }

    /**
     * Insert product from $baseStoreId to $storeId
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $storeId
     * @param int $baseStoreId
     * @return this
     */
    public function _insertToStore($product, $storeId, $baseStoreId = 0)
    {
    	$data = array(
    	   'store_id'   => (int) $storeId,
    	   'product_id' => $product->getId(),
    	);
    	$this->getWriteConnection()->insert($this->_productStoreTable, $data);

    	if ($storeId && ($storeId != $baseStoreId)) {
    	    $newProduct = Mage::getModel('catalog/product')
    	       ->setStoreId($baseStoreId)
    	       ->load($product->getId());
            if ($newProduct->getId()) {
                $newProduct
                    ->setStoreId($storeId)
                    ->setBaseStoreId($baseStoreId)
                    ->save();
            }
    	}
    	return $this;
    }

    protected function _saveCategories(Varien_Object $object)
    {
        $postedCategories = $object->getPostedCategories();
        $oldCategories    = $this->getCategoryCollection($object)
            ->load();

        $delete = array();
        $insert = array();

        if (!is_array($postedCategories)) {
            if ($object->getId()) {
                //no changes made
                return $this;
            } else {
                $postedCategories = array();
            }
        }
        $categories = array();

        foreach ($oldCategories as $category) {
            if ($object->getStoreId()) {
                $stores = $category->getStoreIds();
                if (!in_array($object->getStoreId(), $stores)) {
                    continue;
                }
            }

            $categories[] = $category->getId();
        }

        $delete = array_diff($categories, $postedCategories);
        $insert = array_diff($postedCategories, $categories);

        // Delete unselected category
        if (!empty($delete)) {
            $this->getWriteConnection()->delete(
                $this->_categoryProductTable,
                $this->getWriteConnection()->quoteInto('product_id=? AND ', (int)$object->getId()) .
                $this->getWriteConnection()->quoteInto('category_id in(?)', $delete)
            );
        }

        foreach ($insert as $categoryId) {
            if (empty($categoryId)) {
                continue;
            }
        	$data = array(
        	   'product_id'    => $object->getId(),
        	   'category_id'   => $categoryId,
        	   'position'      => '0'
        	);
        	$this->getWriteConnection()->insert($this->_categoryProductTable, $data);
        }
        return $this;
    }

    protected function _saveLinkedProducts(Varien_Object $object)
    {
        foreach($object->getLinkedProductsForSave() as $linkType=>$data) {
	    	$linkedProducts = $object->getLinkedProducts($linkType)->load();

	       	foreach($data as $linkId=>$linkAttributes) {
	       		if(!$linkedProduct = $linkedProducts->getItemByColumnValue('product_id', $linkId)) {
	       			$linkedProduct = clone $linkedProducts->getObject();
	       			$linkedProduct->setAttributeCollection($linkedProducts->getLinkAttributeCollection());
	       			$linkedProduct->addLinkData($linkedProducts->getLinkTypeId(), $object, $linkId);
	       		}

	   			foreach ($linkedProducts->getLinkAttributeCollection() as $attribute) {
	   				if(isset($linkAttributes[$attribute->getCode()])) {
	   					$linkedProduct->setData($attribute->getCode(), $linkAttributes[$attribute->getCode()]);
	   				}
	   			}

	   			$linkedProduct->save();
	       	}

	       	// Now delete unselected items

	       	foreach($linkedProducts as $linkedProduct) {
				if(!isset($data[$linkedProduct->getId()])) {
					$linkedProduct->delete();
				}
	       	}
    	}
    	return $this;
    }

    public function _saveBundle($product)
    {
    	if(!$product->isBundle()) {
    		return $this;
    	}

    	$options = $product->getBundleOptions();

    	if(!is_array($options)) { // If data copied from other store
    		$optionsCollection = $this->getBundleOptionCollection($product, true)
    			->load();
    		$options = $optionsCollection->toArray();
    	} else {
    		$optionsCollection = $this->getBundleOptionCollection($product)
    			->load();
    	}

    	$optionIds = array();

    	foreach($options as $option) {
    		if($option['id'] && $optionObject = $optionsCollection->getItemById($option['id'])) {
    			$optionObject
    				->setStoreId($product->getStoreId());
    			$optionIds[] = $optionObject->getId();
    		} else {
    			$optionObject = $optionsCollection->getItemModel()
    				->setProductId($product->getId())
    				->setStoreId($product->getStoreId());
    		}

    		$optionObject->setLabel($option['label']);
    		$optionObject->setPosition($option['position']);

    		$optionObject->save();

    		if(!isset($option['products'])) {
    			$links = array();
    			$linksIds = array();
    			if(isset($option['links']) && is_array($option['links'])) {
    				$links = $option['links'];
    				$linksIds = array_keys($option['links']);
    			}

    			foreach ($links as $productId=>$link) {
    				if(!$linkObject=$optionObject->getLinkCollection()->getItemByColumnValue('product_id', $productId)) {
    					$linkObject = clone $optionObject->getLinkCollection()->getObject();
    				}

    				$linkObject
    					->addData($link)
    					->setOptionId($optionObject->getId())
    					->setProductId($productId);
    				$linkObject->save();
    			}

    			foreach ($optionObject->getLinkCollection() as $linkObject) {
    				if(!in_array($linkObject->getProductId(),$linksIds)) {
    					$linkObject->delete();
    				}
    			}
    		}
    	}

    	foreach ($optionsCollection as $optionObject) {
    		if(!in_array($optionObject->getId(),$optionIds)) {
				$optionObject->delete();
			}
    	}

    	return $this;
    }

    public function _saveSuperConfig($object)
    {
    	if(!$object->isSuperConfig()) {
    		return $this;
    	}

    	$attributes = $object->getSuperAttributesForSave();
    	if ($attributes) {
        	foreach($attributes as $attribute) {
        		$attributeModel = Mage::getModel('catalog/product_super_attribute')
        			->setData($attribute)
        			->setStoreId($object->getStoreId())
        			->setProductId($object->getId())
        			->setId($attribute['id'])
        			->save();
        	}
    	}

    	$linkExistsProductIds = array();
    	$links = $object->getSuperLinksForSave();
    	foreach (array_keys($links) as $productId) {
    		$linkModel = Mage::getModel('catalog/product_super_link')
    			->loadByProduct($productId, $object->getId())
    			->setProductId($productId)
    			->setParentId($object->getId())
    			->save();

    		$linkExistsProductIds[] = $productId;
    	}

    	$linkCollection = $this->getSuperLinkCollection($object)->load();

    	foreach($linkCollection as $item) {
    		if(!in_array($item->getProductId(), $linkExistsProductIds)) {
    			$item->delete();
    		}
    	}

    	return $this;
    }

    public function getCategoryCollection($product)
    {
        $collection = Mage::getResourceModel('catalog/category_collection')
            ->joinField('product_id',
                'catalog/category_product',
                'product_id',
                'category_id=entity_id',
                null)
            ->addFieldToFilter('product_id', (int) $product->getId());
        return $collection;
    }


    public function getBundleOptionCollection($product, $useBaseStoreId=false)
    {
    	$collection = Mage::getModel('catalog/product_bundle_option')->getResourceCollection()
    			->setProductIdFilter($product->getId());

    	if($useBaseStoreId) {
    		$collection->setStoreId($product->getBaseStoreId());
    	} else {
    		$collection->setStoreId($product->getStoreId());
    	}

    	return $collection;
    }

   	public function getSuperAttributes($product, $asObject=false, $applyLinkFilter=false)
   	{
   		$result = array();
   		if(!$product->getId()) {
    		$position = 0;
    		$superAttributesIds = $product->getSuperAttributesIds();
    		foreach ($product->getAttributes() as $attribute) {
    			if(in_array($attribute->getAttributeId(), $superAttributesIds)) {
    				if(!$asObject) {
						$row = $attribute->toArray(array('attribute_id','attribute_code','id','frontend_label'));
						$row['values'] = array();
						$row['label'] = $row['frontend_label'];
						$row['position'] = $position++;
    				} else {
    					$row = $attribute;
    				}
    				$result[] = $row;
    			}
    		}
    	} else {
    		if($applyLinkFilter) {
    			if(!$product->getSuperLinkCollection()->getIsLoaded()) {
	    			$product->getSuperLinkCollection()
	    				->joinField('store_id',
					                'catalog/product_store',
					                'store_id',
					                'product_id=entity_id',
					                '{{table}}.store_id='.(int) $product->getStoreId());
	    			$product->getSuperAttributeCollection()->getPricingCollection()
	    					->addLinksFilter($product->getSuperLinks());
                    $product->getSuperAttributeCollection()->getPricingCollection()->clear();
	    			$product->getSuperAttributeCollection()->clear();
	    			$product->getSuperAttributeCollection()->load();

    			}
    		}

    		$superAttributesIds = $product->getSuperAttributeCollectionLoaded()->getColumnValues('attribute_id');
    		foreach ($superAttributesIds as $attributeId) {
                foreach($product->getAttributes() as $attribute) {
    		    	if ($attributeId == $attribute->getAttributeId()) {
        				if(!$asObject) {
        					$superAttribute = $product->getSuperAttributeCollectionLoaded()->getItemByColumnValue('attribute_id', $attribute->getAttributeId());
    						$row = $attribute->toArray(array('attribute_id','attribute_code','frontend_label'));
    						$row['values'] = $superAttribute->getValues($attribute);
    						$row['label'] = $superAttribute->getLabel();
    						$row['id'] = $superAttribute->getId();
    						$row['position'] = $superAttribute->getPosition();
        				} else {
        					$row = $attribute;
        				}
        				$result[] = $row;
    		    	}
    		    }
    		}
    	}
    	return $result;
   	}

   	public function getSuperLinks($product)
   	{
   		$result = array();

   		$attributes = $product->getSuperAttributes(true);
   		if(!$product->getSuperLinkCollection()->getIsLoaded()) {
	   		$product->getSuperLinkCollection()
	   				->useProductItem();

	   		foreach ($attributes as $attribute) {
	   			$product->getSuperLinkCollection()
	   				->addAttributeToSelect($attribute->getAttributeCode());
	   		}
   		}

   		foreach ($product->getSuperLinkCollectionLoaded() as $link) {
   			$resultAttributes = array();
   			foreach($attributes as $attribute) {
   				$resultAttribute = array();
   				$resultAttribute['attribute_id'] = $attribute->getAttributeId();
   				$resultAttribute['value_index']	 = $link->getData($attribute->getAttributeCode());
   				$resultAttribute['label']	     = $attribute->getFrontend()->getLabel();
   				$resultAttributes[] 			 = $resultAttribute;
   			}

   			$result[$link->getEntityId()] = $resultAttributes;
   		}

    	return $result;
   	}

    public function getStoreCollection($product)
    {
        $collection = Mage::getResourceModel('core/store_collection')
            ->setLoadDefault(true);
        /* @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */

        $collection->getSelect()
            ->join($this->_productStoreTable, $this->_productStoreTable.'.store_id=main_table.store_id')
            ->where($this->_productStoreTable.'.product_id='.(int)$product->getId());

        return $collection;
    }

    public function getSuperAttributeCollection($product)
    {
    	$collection = Mage::getResourceModel('catalog/product_super_attribute_collection');
    	$collection->setProductFilter($product)
    		->setOrder('position', 'asc');
    	return $collection;
    }

    public function getSuperLinkCollection($product)
    {
    	$collection = Mage::getResourceModel('catalog/product_super_link_collection');
    	$collection->setProductFilter($product);
    	return $collection;
    }

    public function getStoreIds($product)
    {
        $stores = array();
        $collection = $this->getStoreCollection($product)
            ->load();
        foreach ($collection as $store) {
        	$stores[] = $store->getId();
        }
        return $stores;
    }

    public function getDefaultAttributeSourceModel()
    {
        return 'eav/entity_attribute_source_table';
    }

    protected function _getDefaultAttributes()
    {
    	$attributes = parent::_getDefaultAttributes();
    	$attributes[] = 'type_id';
    	return $attributes;
    }

    /**
     * Validate all object's attributes against configuration
     *
     * @param Varien_Object $object
     * @return Varien_Object
     */
    public function validate($object)
    {
        parent::validate($object);
        return $this;
    }

    public function copy(Mage_Catalog_Model_Product $object)
    {
        $uniqAttributes = array();


        $storeIds = $this->getStoreIds($object);
        $oldId = $object->getId();

        $storeIds = array_combine($storeIds, array_fill(0, sizeof($storeIds), 0));
        if(!isset($storeIds[0])) {
            $storeIds[0] = 0;
        }

        $catagoryCollection = $this->getCategoryCollection($object)
            ->load();
        $categories = array();
        foreach ($catagoryCollection as $category) {
        	$categories[] = $category->getId();
        }

        $object->setStoreId(0)
            ->load($object->getId());

        $newProduct = Mage::getModel('catalog/product')
	       ->setStoreId(0)
	       ->addData($object->getData());

        $this->_prepareCopy($newProduct);
        $newProduct->setPostedStores($storeIds);
        $newProduct->setPostedCategories($categories);
        $newProduct->save();

        $newId = $newProduct->getId();

        foreach ($storeIds as $storeId) {
        	if ($storeId) {
        	    $oldProduct = Mage::getModel('catalog/product')
        	       ->setStoreId($storeId)
        	       ->load($oldId);

                $newProduct = Mage::getModel('catalog/product')
        	       ->setStoreId($storeId)
        	       ->load($newId)
        	       ->addData($oldProduct->getData());

                $this->_prepareCopy($newProduct);
                $newProduct->setId($newId);
                $newProduct->save();
        	}
        }
        $object->setId($newId);
        return $this;
    }

    protected function _prepareCopy($object)
    {
        $object->setId(null);
        foreach ($object->getAttributes() as $attribute) {
        	if ($attribute->getIsUnique()) {
        	    $object->setData($attribute->getAttributeCode(), null);
        	}
        }
        $object->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
        return $this;
    }
}
