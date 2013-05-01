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


class Mage_Catalog_Model_Convert_Adapter_Product extends Mage_Eav_Model_Convert_Adapter_Entity
{
    public function __construct()
    {
        $this->setVar('entity_type', 'catalog/product');
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

		$attrToDb = array(
		  'type'=>'type_id',
		  'attribute_set'=>'attribute_set_id'
		);

		parent::setFilter($attrFilterArray,$attrToDb);
		parent::load();
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
            $this->addException(Mage::helper('catalog')->__('No product collections found'), Varien_Convert_Exception::FATAL);
        }

        foreach ($collections as $storeId=>$collection) {
            $this->addException(Mage::helper('catalog')->__('Records for "'.$stores[$storeId].'" store found'));

            if (!$collection instanceof Mage_Catalog_Model_Entity_Product_Collection) {
                $this->addException(Mage::helper('catalog')->__('Product collection expected'), Varien_Convert_Exception::FATAL);
            }
            try {
                $i = 0;
                foreach ($collection->getIterator() as $model) {
                    $new = false;
                    // if product is new, create default values first
                    if (!$model->getId()) {
                        $new = true;
                        $model->save();
                        #Mage::getResourceSingleton('catalog_entity/convert')->addProductToStore($model->getId(), 0);
                    }
                    if (!$new || 0!==$storeId) {
                        if (0!==$storeId) {
                            Mage::getResourceSingleton('catalog_entity/convert')->addProductToStore($model->getId(), $storeId);
                        }
                        $model->save();
                    }
                    $i++;
                }
                $this->addException(Mage::helper('catalog')->__("Saved ".$i." record(s)"));
            } catch (Exception $e) {
                if (!$e instanceof Varien_Convert_Exception) {
                    $this->addException(Mage::helper('catalog')->__('Problem saving the collection, aborting. Error: %s', $e->getMessage()),
                        Varien_Convert_Exception::FATAL);
                }
            }
        }
        return $this;
    }
}
