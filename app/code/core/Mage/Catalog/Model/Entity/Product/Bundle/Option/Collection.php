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
 * Catalog product bundle option collection
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
 class Mage_Catalog_Model_Entity_Product_Bundle_Option_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
 {
    protected $_linkCollection = null;
    protected $_storeId = 0;
    protected $_useProductItemFlag = false;


    protected function _construct()
    {
        $this->_init('catalog/product_bundle_option');
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        $this->_joinValues();

        return $this;
    }

    public function getStoreId()
    {
        return (int)$this->_storeId;
    }

    public function setProductIdFilter($productId)
    {
        $this->getSelect()
            ->where('main_table.product_id = ?', $productId);
        return $this;
    }


    protected function _joinValues()
    {
        $this->getSelect()
            ->joinLeft(array('value'=>$this->getTable('product_bundle_option_value')),
                      'value.option_id=main_table.option_id AND value.store_id='.(int)$this->getStoreId(),
                      array('label', 'position', 'store_id'));

        if($this->getStoreId()>0) {
            $this->getSelect()
                ->joinLeft(array('default_value'=>$this->getTable('product_bundle_option_value')),
                           'default_value.option_id=main_table.option_id AND default_value.store_id=0',
                           array('label AS default_label', 'position AS default_position'));
        }
    }

    protected function _loadLinks()
    {
        $optionsIds = $this->getColumnValues('option_id');

        if(sizeof($optionsIds)==0) {
            return $this;
        }

        if($this->_useProductItemFlag) {
            $this->getLinkCollection()
                ->useProductItem();
        }

        $this->getLinkCollection()
            ->setOptionIds($optionsIds)
            ->setStoreId($this->getStoreId())
            ->addFieldToFilter('option_id', array('in'=>$optionsIds))
            ->load();

        foreach($this->getItems() as $item) {
            foreach ($this->getLinkCollection() as $link) {
                if($this->_useProductItemFlag) {
                    $item->getLinkCollection()
                        ->useProductItem();
                }
                if($item->getId()==$link->getOptionId()) {
                    $item->getLinkCollection()->addItem($link);
                }
            }
        }

        return $this;
    }

    public function getLinkCollection()
    {
        if(is_null($this->_linkCollection)) {
            $this->_linkCollection = Mage::getResourceModel('catalog/product_bundle_option_link_collection');
        }

        return $this->_linkCollection;
    }

    public function load($printQuery=false, $logQuery=false) {
        if ($this->isLoaded()) {
            return $this;
        }
        parent::load($printQuery, $logQuery);
        $this->_loadLinks();
        return $this;
    }

    public function getItemModel()
    {
        return new $this->_itemObjectClass;
    }

    public function useProductItem()
    {
        $this->_useProductItemFlag = true;
        return $this;
    }

     /**
     * Convert collection to array
     *
     * @return array
     */
    public function toArray($arrRequiredFields = array())
    {
        $array = array();
        foreach ($this->_items as $item) {
            $array[] = $item->toArray();
        }
        return $array;
    }
 }
