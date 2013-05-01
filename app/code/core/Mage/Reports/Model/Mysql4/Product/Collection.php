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
 * @package    Mage_Reports
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Products Report collection
 *
 * @category   Mage
 * @package    Mage_Reports
 */

class Mage_Reports_Model_Mysql4_Product_Collection extends Mage_Catalog_Model_Entity_Product_Collection
{
    protected $productEntityId;

    public function __construct()
    {
        $product = Mage::getResourceSingleton('catalog/product');
        /* @var $product Mage_Catalog_Model_Entity_Product */
        $this->productEntityId = $product->getEntityIdField();

        parent::__construct();
    }

    protected function _joinFields()
    {
        $this->_totals = new Varien_Object();

        $this->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('name');
        /*$this->getSelect()->from('', array(
                    'viewed' => 'CONCAT("","")',
                    'added' => 'CONCAT("","")',
                    'purchased' => 'CONCAT("","")',
                    'fulfilled' => 'CONCAT("","")',
                    'revenue' => 'CONCAT("","")',
                   ));*/
    }

    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->from("", "count(DISTINCT e.entity_id)");
        $sql = $countSelect->__toString();
        return $sql;
    }

    public function addCartsCount()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset();
        $quoteItem = Mage::getResourceSingleton('sales/quote_item');
        /* @var $quoteItem Mage_Sales_Model_Entity_Quote */
        $productIdAttr = $quoteItem->getAttribute('product_id');
        /* @var $productIdAttr Mage_Eav_Model_Entity_Attribute_Abstract */
        $productIdAttrId = $productIdAttr->getAttributeId();
        $productIdTableName = $productIdAttr->getBackend()->getTable();
        $productIdFieldName = $productIdAttr->getBackend()->isStatic() ? 'product_id' : 'value';

        $quote = Mage::getResourceSingleton('sales/quote');
        /* @var $quote Mage_Sales_Model_Entity_Quote */
        $isActiveAtrr = $quote->getAttribute('is_active');
        /* @var $attrIsActive Mage_Eav_Model_Entity_Attribute_Abstract */
        $isActiveTableName = $isActiveAtrr->getBackend()->getTable();
        $isActiveFieldName = $isActiveAtrr->getBackend()->isStatic() ? 'is_active' : 'value';

        $countSelect->from(array("quote_items" => $productIdTableName), "count(*)")
            ->from(array("quotes1" => $isActiveTableName), array())
            ->from(array("quotes2" => $isActiveTableName), array())
            ->where("quote_items.{$productIdFieldName} = e.{$this->productEntityId}")
            ->where("quote_items.attribute_id = {$productIdAttrId}")
            ->where("quote_items.entity_id = quotes1.entity_id")
            ->where("quotes2.entity_id = quotes1.parent_id")
            ->where("quotes2.is_active = 1");

        $this->getSelect()
            ->from("", array("carts" => "({$countSelect})"))
            ->group("e.{$this->productEntityId}");

        return $this;
    }

    public function addOrdersCount()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset();
        $orderItem = Mage::getResourceSingleton('sales/order_item');
        /* @var $orderItem Mage_Sales_Model_Entity_Quote */
        $attr = $orderItem->getAttribute('product_id');
        /* @var $attr Mage_Eav_Model_Entity_Attribute_Abstract */
        $attrId = $attr->getAttributeId();
        $tableName = $attr->getBackend()->getTable();
        $fieldName = $attr->getBackend()->isStatic() ? 'product_id' : 'value';

        $countSelect->from($tableName, "count(*)")
            ->where("{$tableName}.{$fieldName} = e.{$this->productEntityId}
                        and {$tableName}.attribute_id = {$attrId}");

        $this->getSelect()
            ->from("", array("orders" => "({$countSelect})"))
            ->group("e.{$this->productEntityId}");

        return $this;
    }

    public function resetSelect()
    {
        parent::resetSelect();
        $this->_joinFields();
        return $this;
    }

    public function setOrder($attribute, $dir='desc')
    {
        switch ($attribute)
        {
            //case 'viewed':
            //case 'added':
            //case 'purchased':
            //case 'fulfilled':
            //case 'revenue':
            case 'carts':
            case 'orders':
                $this->getSelect()->order($attribute . ' ' . $dir);
                break;
            default:
                parent::setOrder($attribute, $dir);
        }

        return $this;
    }
}