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
 * Catalog super product attribute collection
 *
 * @category   Mage
 * @package    Mage_Catalog
 */

class Mage_Catalog_Model_Entity_Product_Super_Attribute_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_productId = 0;
    protected $_storeId   = 0;
    protected $_isJoined  = false;
    protected $_pricingCollection = null;
    
    protected function _construct()
    {
        $this->_init('catalog/product_super_attribute');
    }
    
    public function setProductId($productId)
    {
        $this->_productId = $productId;
        return $this;
    }
    
    public function getProductId() 
    {
        return $this->_productId;
    }
    
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }
    
    public function getStoreId()
    {
        return $this->_storeId;
    }
    
    public function getIsLoaded()
    {
        return $this->isLoaded();
    }
    
    public function setProductFilter($product)
    {
        $this->setProductId($product->getId())
            ->setStoreId(strlen($product->getBaseStoreId()) > 0 ? $product->getBaseStoreId() : $product->getStoreId());
        
        $this->addFieldToFilter('product_id', $this->getProductId());
        return $this;
    }
    
    public function getSize()
    {
        $this->_initSelect();
        return parent::getSize();
    }
    
    public function load($printQuery=false, $logQuery=false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        $this->_initSelect();
        parent::load($printQuery, $logQuery);
        return $this->_loadPricing();
    }
    
    protected function _initSelect()
    {
        if(!$this->_isJoined) {
            $this->getSelect()
                ->joinLeft(array('label'=>$this->getTable('product_super_attribute_label')),
                 'label.product_super_attribute_id=main_table.product_super_attribute_id AND label.store_id=' .(int) $this->getStoreId(),
                 array('value_id','value AS label','store_id'));
            $this->_isJoined = true;
        }
        
        return $this;
    }
    
    protected function _loadPricing() 
    {
        if(sizeof($this->getItems())==0) {
            return $this;
        }
        
        $this->getPricingCollection()
            ->addFieldToFilter(
                'main_table.' . $this->getResource()->getIdFieldName(),
                array('in'=>$this->getColumnValues($this->getResource()->getIdFieldName())
            ))
            ->load();
        
        foreach ($this->getPricingCollection() as $item) {
            $this->getItemById($item->getData($this->getResource()->getIdFieldName()))
                ->getPricingCollection()
                    ;//->addItem($item);           
        }
        
        return $this;
    }
    
    public function getPricingCollection()
    {
        if(is_null($this->_pricingCollection)) {
            $this->_pricingCollection = Mage::getResourceModel('catalog/product_super_attribute_pricing_collection');
        }
        
        return $this->_pricingCollection;
    }
}// Class Mage_Catalog_Model_Entity_Product_Attribute_Collection END