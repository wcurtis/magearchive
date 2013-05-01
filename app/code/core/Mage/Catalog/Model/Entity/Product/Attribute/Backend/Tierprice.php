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
 * Catalog product tier price backend attribute model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */

class Mage_Catalog_Model_Entity_Product_Attribute_Backend_Tierprice extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
	/**
	 * DB connections list
	 *
	 * @var array
	 */
	protected $_connections = array();

	/**
	 * Attribute main table
	 *
	 * @var string
	 */
	protected $_mainTable = null;

	public function getMainTable()
	{
		if (is_null($this->_mainTable)) {
			$this->_mainTable = Mage::getSingleton('core/resource')->getTableName('catalog/product_attribute_tier_price');
		}

		return $this->_mainTable;
	}

	public function afterLoad($object)
    {
    	$storeId = $object->getStoreId();

        $attributeId   = $this->getAttribute()->getId();
        $entityId	   = $object->getId();
        $entityIdField = $this->getEntityIdField();

        $select = $this->getConnection('read')->select()
        	->from($this->getMainTable(), array('qty AS price_qty', 'value AS price'))
        	->where('store_id = ?', $storeId)
        	->where($entityIdField . ' = ?', $entityId)
        	->where('attribute_id = ?', $attributeId);

        $object->setData($this->getAttribute()->getName(), $this->getConnection('read')->fetchAll($select));
    }

    public function beforeSave($object)
    {

    }

    public function afterSave($object)
    {
        $storeId = $object->getStoreId();

        $attributeId   = $this->getAttribute()->getId();
        $entityId      = $object->getId();
        $entityTypeId  = $this->getAttribute()->getEntity()->getTypeId();
        $entityIdField = $this->getEntityIdField();

        $connection = $this->getConnection('write');

        $condition = array(
            $connection->quoteInto($entityIdField . ' = ?', $entityId),
            $connection->quoteInto('attribute_id = ?', $attributeId)
        );

        if (!$this->getAttribute()->getIsGlobal()) {
            $condition[] = $connection->quoteInto('store_id = ?', $storeId);
        }

        $connection->delete($this->getMainTable(), $condition);

        $tierPrices = $object->getData($this->getAttribute()->getName());

        if (!is_array($tierPrices)) {
            return;
        }

        $minimalPrice = $object->getPrice();

        foreach ($tierPrices as $tierPrice) {
            if (empty($tierPrice['price_qty']) || !isset($tierPrice['price']) || strlen($storeId)==0 || !empty($tierPrice['delete'])) {
                continue;
            }


            $data = array();
            $data[$entityIdField]   = $entityId;
            $data['attribute_id']   = $attributeId;
            $data['qty']            = $tierPrice['price_qty'];
            $data['value']          = $tierPrice['price'];
            $data['entity_type_id'] = $entityTypeId;



            if ($tierPrice['price']<$minimalPrice) {
                $minimalPrice = $tierPrice['price'];
            }

            if ($this->getAttribute()->getIsGlobal()) {
                // Fixing on create saving
                if ($object->getPostedStores()) {
                    $storeIds = array_keys($object->getPostedStores());
                } else {
                    $storeIds = $object->getStoreIds();
                }

                if (!in_array(0, $storeIds)) {
                    $storeIds[] = 0;
                }

                foreach ($storeIds as $storeId) {
                    $data['store_id'] = $storeId;
                    $connection->insert($this->getMainTable(), $data);
                }
            }
            else {
                $data['store_id'] = $storeId;
                $connection->insert($this->getMainTable(), $data);
            }
        }

        $object->setMinimalPrice($minimalPrice);
        $this->getAttribute()->getEntity()->saveAttribute($object, 'minimal_price');
    }

    /*public function afterSave($object)
    {
    	$storeId = $object->getStoreId();

        $attributeId   = $this->getAttribute()->getId();
        $entityId	   = $object->getId();
        $entityTypeId  = $this->getAttribute()->getEntity()->getTypeId();
        $entityIdField = $this->getEntityIdField();

        $connection = $this->getConnection('write');

    	$condition = array(
    		$connection->quoteInto($entityIdField . ' = ?', $entityId),
    		$connection->quoteInto('attribute_id = ?', $attributeId)
    	);

    	if (!$this->getAttribute()->getIsGlobal()) {
    	    $condition[] = $connection->quoteInto('store_id = ?', $storeId);
    	}

    	$connection->delete($this->getMainTable(), $condition);

    	$tierPrices = $object->getData($this->getAttribute()->getName());

    	if (!is_array($tierPrices)) {
    		return;
    	}

    	$minimalPrice = $object->getPrice();

    	return $this;
    	foreach ($tierPrices as $tierPrice) {
    		if( !isset($tierPrice['price_qty']) || !isset($tierPrice['value']) || strlen($storeId)==0 ) {
    			continue;
    		}

    		$data = array();
    		$data[$entityIdField] 	= $entityId;
    		$data['attribute_id'] 	= $attributeId;
    		$data['qty']		  	= $tierPrice['price_qty'];
    		$data['value']		  	= $tierPrice['value'];
    		$data['tier_type']		= $tierPrice['type'];
    		$data['entity_type_id'] = $entityTypeId;

    		if ($tierPrice['value']<$minimalPrice) {
    		    $minimalPrice = $tierPrice['value'];
    		}

    		if ($this->getAttribute()->getIsGlobal()) {
    		    foreach ($object->getStoreIds() as $storeId) {
        		    $data['store_id'] = $storeId;
        		    $connection->insert($this->getMainTable(), $data);
    		    }
    		}
    		else {
    		    $data['store_id'] = $storeId;
    		    $connection->insert($this->getMainTable(), $data);
    		}
    	}
    	$object->setMinimalPrice($minimalPrice);
    	$this->getAttribute()->getEntity()->saveAttribute($object, 'minimal_price');
    }*/

    public function afterDelete($object)
    {
    	if ($object->getUseDataSharing()) {
            $storeId = $object->getData('store_id');
        } else {
            $storeId = $object->getStoreId();
        }

        $attributeId   = $this->getAttribute()->getId();
        $entityId	   = $object->getId();
        $entityTypeId  = $object->getTypeId();
        $entityIdField = $this->getEntityIdField();

        $connection = $this->getConnection('write');

    	$condition = array(
    		$connection->quoteInto('store_id = ?', $storeId),
    		$connection->quoteInto($entityIdField . ' = ?', $entityId),
    		$connection->quoteInto('attribute_id = ?', $attributeId)
    	);

    	$connection->delete($this->getMainTable(), $condition);
    }

    /**
     * Return DB connection
     *
     * @param	string		$type
     * @return	Zend_Db_Adapter_Abstract
     */
    public function getConnection($type)
    {
    	if (!isset($this->_connections[$type])) {
    		$this->_connections[$type] = Mage::getSingleton('core/resource')->getConnection('catalog_' . $type);
    	}

    	return $this->_connections[$type];
    }

}// Class Mage_Catalog_Model_Entity_Product_Attribute_Backend_Tierprice END