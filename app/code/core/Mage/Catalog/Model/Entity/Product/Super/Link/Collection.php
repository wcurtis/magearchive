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
 * Catalog super product link collection
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Entity_Product_Super_Link_Collection extends Mage_Catalog_Model_Entity_Product_Collection
{

    protected $_isLoaded  = false;

    public function __construct()
    {
        $this->setEntity(Mage::getResourceSingleton('catalog/product'))
               ->setObject('catalog/product_super_link');
    }

    protected function _joinLink()
    {
        $this->joinField('link_id', 'catalog/product_super_link', 'link_id', 'product_id=entity_id')
            ->joinField('parent_id', 'catalog/product_super_link', 'parent_id', 'link_id=link_id')
            ->joinField('product_id', 'catalog/product_super_link', 'product_id', 'link_id=link_id');

        return $this;
    }

    public function resetSelect()
    {
        $result = parent::resetSelect();
        $this->_joinLink();
        return $result;
    }

    public function getIsLoaded()
    {
        return $this->_isLoaded;
    }

    public function load($printQuery=false, $logQuery=false)
    {
        $oldStoreId = $this->getEntity()->getStoreId();
        if(!isset($this->_joinFields['store_id'])) {
            $this->getEntity()->setStore(0);
        }
        $this->_isLoaded = true;
        parent::load($printQuery, $logQuery);
        if(!isset($this->_joinFields['store_id'])) {
            $this->getEntity()->setStore($oldStoreId);
        }
        return $this;
    }

    public function useProductItem()
    {
        $this->setObject('catalog/product');
        return $this;
    }

    public function setProductFilter($product)
    {
        $this->addFieldToFilter('parent_id', (int) $product->getId());
        return $this;
    }

    public function setStoreFilterByProduct($product)
    {
        if(!isset($this->_joinFields['store_id'])) {
            $this->joinField(
               'link_store_id',
               'catalog/product_store',
               'store_id' ,
               'product_id=entity_id',
               array('store_id'=>$product->getStoreId())
            );
        }
        return $this;
    }

}
