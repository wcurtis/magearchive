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


class Mage_Catalog_Model_Convert_Adapter_Product
    extends Mage_Eav_Model_Convert_Adapter_Entity
{
    protected $_configs = array(
        'min_qty', 'backorders', 'min_sale_qty', 'max_sale_qty');

    protected $_inventoryFields = array(
        'qty', 'min_qty', 'use_config_min_qty',
        'is_qty_decimal', 'backorders', 'use_config_backorders',
        'min_sale_qty','use_config_min_sale_qty','max_sale_qty',
        'use_config_max_sale_qty','is_in_stock','notify_stock_qty','use_config_notify_stock_qty'

    );


    public function __construct()
    {
        $this->setVar('entity_type', 'catalog/product');
        if (!Mage::registry('Object_Cache_Product')) {
            $this->setProduct(Mage::getModel('catalog/product'));
        }

        if (!Mage::registry('Object_Cache_StockItem')) {
            $this->setStockItem(Mage::getModel('cataloginventory/stock_item'));
        }
    }

    protected function _getCollectionForLoad($entityType)
    {
        $collection = parent::_getCollectionForLoad($entityType);
        $collection->setStore($this->getStoreId())->addStoreFilter();
        return $collection;
    }

	public function load()
	{
		$attrFilterArray = array();
		$attrFilterArray ['name'] = 'like';
		$attrFilterArray ['sku'] = 'like';
		$attrFilterArray ['type'] = 'eq';
		$attrFilterArray ['attribute_set'] = 'eq';
		$attrFilterArray ['visibility'] = 'eq';
		$attrFilterArray ['status'] = 'eq';
		$attrFilterArray ['price'] = 'fromTo';
		$attrFilterArray ['qty'] = 'fromTo';
		$attrFilterArray ['store_id'] = 'eq';

		$attrToDb = array(
		  'type'=>'type_id',
		  'attribute_set'=>'attribute_set_id'
		);
		
		$filters = $this->_parseVars();

		if ($qty = $this->getFieldValue($filters, 'qty')) {                
			
			$qtyAttr = array();
			$qtyAttr['alias'] = 'qty';
			$qtyAttr['attribute'] = 'cataloginventory/stock_item';
			$qtyAttr['field'] = 'qty';
			$qtyAttr['bind'] = 'product_id=entity_id';
			$qtyAttr['cond'] = "{{table}}.qty between '".(isset($qty['from'])?$qty['from']:0)."' and '".(isset($qty['to'])?$qty['to']:0)."'";
        	$qtyAttr['joinType'] = 'inner';
        	$this->setJoinFeild($qtyAttr);
		}		


		parent::setFilter($attrFilterArray,$attrToDb); 

		if ($price = $this->getFieldValue($filters, 'price')) {
			$this->_filter[] = array('attribute'=>'price','from'=>$price['from'],'to'=>$price['to']);
			$this->setJoinAttr(array(
					   'alias' => 'price',
        	           'attribute' => 'catalog_product/price',
                       'bind' => 'entity_id',
                       'joinType' => 'LEFT'
                    ));
		}
		$this->_getCollectionForLoad($this->getVar('entity_type'));
		
		parent::load();
	}

	public function setProduct(Mage_Catalog_Model_Product $object)
	{
	    $id = Varien_Object_Cache::singleton()->save($object);
	    //$this->_product = $object;
	    Mage::register('Object_Cache_Product', $id);
	}

	public function getProduct()
	{
	    return Varien_Object_Cache::singleton()->load(Mage::registry('Object_Cache_Product'));
	}

	public function setStockItem(Mage_CatalogInventory_Model_Stock_Item $object)
	{
	    $id = Varien_Object_Cache::singleton()->save($object);
	    //$this->_product = $object;
	    Mage::register('Object_Cache_StockItem', $id);

	    //$this->_stockItem = $object;
	}

	public function getStockItem()
	{
	    return Varien_Object_Cache::singleton()->load(Mage::registry('Object_Cache_StockItem'));
	    //return $this->_stockItem;
	}

    public function save()
    {
        $stores = array();
        foreach (Mage::getConfig()->getNode('stores')->children() as $storeNode) {
            $stores[(int)$storeNode->system->store->id] = $storeNode->getName();
        }

        $collections = $this->getData();
        if ($collections instanceof Mage_Catalog_Model_Entity_Product_Collection) {
            $collections = array($collections->getEntity()->getStoreId()=>$collections);
        } elseif (!is_array($collections)) {
            $this->addException(Mage::helper('catalog')->__('No product collections found'), Mage_Dataflow_Model_Convert_Exception::FATAL);
        }

        //$stockItems = $this->getInventoryItems();
        $stockItems = Mage::registry('current_imported_inventory');
        if ($collections) foreach ($collections as $storeId=>$collection) {
            $this->addException(Mage::helper('catalog')->__('Records for "'.$stores[$storeId].'" store found'));

            if (!$collection instanceof Mage_Catalog_Model_Entity_Product_Collection) {
                $this->addException(Mage::helper('catalog')->__('Product collection expected'), Mage_Dataflow_Model_Convert_Exception::FATAL);
            }
            try {
                $i = 0;
                foreach ($collection->getIterator() as $model) {
                    $new = false;
                    // if product is new, create default values first
                    if (!$model->getId()) {
                        $new = true;
                        $model->save();

                        // if new product and then store is not default
                        // we duplicate product as default product with store_id -
                        if (0 !== $storeId ) {
                            $data = $model->getData();
                            $default = Mage::getModel('catalog/product');
                            $default->setData($data);
                            $default->setStoreId(0);
                            $default->save();
                            unset($default);
                        } // end

                        #Mage::getResourceSingleton('catalog_entity/convert')->addProductToStore($model->getId(), 0);
                    }
                    if (!$new || 0!==$storeId) {
                        if (0!==$storeId) {
                            Mage::getResourceSingleton('catalog_entity/convert')->addProductToStore($model->getId(), $storeId);
                        }
                        $model->save();
                    }

                    if (isset($stockItems[$model->getSku()]) && $stock = $stockItems[$model->getSku()]) {
                        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($model->getId());
                        $stockItemId = $stockItem->getId();

                        if (!$stockItemId) {
                            $stockItem->setData('product_id', $model->getId());
                            $stockItem->setData('stock_id', 1);
                            $data = array();
                        } else {
                            $data = $stockItem->getData();
                        }

                        foreach($stock as $field => $value) {
                            if (!$stockItemId) {
                                if (in_array($field, $this->_configs)) {
                                    $stockItem->setData('use_config_'.$field, 0);
                                }
                                $stockItem->setData($field, $value?$value:0);
                            } else {

                                if (in_array($field, $this->_configs)) {
                                    if ($data['use_config_'.$field] == 0) {
                                        $stockItem->setData($field, $value?$value:0);
                                    }
                                } else {
                                    $stockItem->setData($field, $value?$value:0);
                                }
                            }
                        }
                        $stockItem->save();
                        unset($data);
                        unset($stockItem);
                        unset($stockItemId);
                    }
                    unset($model);
                    $i++;
                }
                $this->addException(Mage::helper('catalog')->__("Saved ".$i." record(s)"));
            } catch (Exception $e) {
                if (!$e instanceof Mage_Dataflow_Model_Convert_Exception) {
                    $this->addException(Mage::helper('catalog')->__('Problem saving the collection, aborting. Error: %s', $e->getMessage()),
                        Mage_Dataflow_Model_Convert_Exception::FATAL);
                }
            }
        }
        //unset(Zend::unregister('imported_stock_item'));
        unset($collections);
        return $this;
    }

    public function saveRow($args)
    {
//        static $import, $product, $stockItem;
        $mem = memory_get_usage(); $origMem = $mem; $memory = $mem;

//        if (!$product) {
//            $import = Mage::getModel('dataflow/import');
//            $product = Mage::getModel('catalog/product');
//            $stockItem = Mage::getModel('cataloginventory/stock_item');
//        }

        $product = $this->getProduct();
        $stockItem = $this->getStockItem();

        set_time_limit(240);

//        $row = unserialize($args['row']['value']);
        $row = $args;
        $newMem = memory_get_usage(); $memory .= ', '.($newMem-$mem); $mem = $newMem;



        $product->importFromTextArray($row);
        //echo '<pre>';
        //print_r($product->getData());
        $newMem = memory_get_usage(); $memory .= ', '.($newMem-$mem); $mem = $newMem;


        if (!$product->getData()) {
            return;
        }

        try {
            $product->save();
            $productId = $product->getId();
            $product->unsetData();

            $newMem = memory_get_usage(); $memory .= ', '.($newMem-$mem); $mem = $newMem;
            if ($stockItem) {
                $stockItem->loadByProduct($productId);
                if (!$stockItem->getId()) {
                    $stockItem->setProductId($productId)->setStockId(1);
                }
                foreach ($row['row'] as $field=>$value) {
                    if (in_array($field, $this->_inventoryFields)) {
                        if ($value != '') $stockItem->setData($field, $value);
                    }
                }
                $stockItem->save();
                $stockItem->unsetData();
            }

            $newMem = memory_get_usage(); $memory .= ', '.($newMem-$mem); $mem = $newMem;

            $newMem = memory_get_usage(); $memory .= ', '.($newMem-$mem); $mem = $newMem;

            $newMem = memory_get_usage(); $memory .= ', '.($newMem-$mem); $mem = $newMem;

            $newMem = memory_get_usage(); $memory .= ' = '.($newMem-$origMem); $mem = $newMem;


        } catch (Exception $e) {

        }
        unset($row);
        return array('memory'=>$memory);
    }

    function setInventoryItems($items)
    {
        $this->_inventoryItems = $items;
    }

    function getInventoryItems()
    {
        return $this->_inventoryItems;
    }
}
