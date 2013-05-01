<?php

class Mage_Catalog_Block_Product_List_Promotion extends Mage_Catalog_Block_Product_List
{
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $collection = Mage::getResourceModel('catalog/product_collection');
            Mage::getModel('catalog/layer')->prepareProductCollection($collection);
// your custom filter
            $collection->addAttributeToFilter('promotion', 1);

            $this->_productCollection = $collection;
            Mage::dispatchEvent('catalog_block_product_list_collection', array(
                'collection'=>$this->_productCollection,
            ));
        }
        return $this->_productCollection;
    }
}