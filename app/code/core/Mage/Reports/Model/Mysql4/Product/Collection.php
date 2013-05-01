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

class Mage_Reports_Model_Mysql4_Product_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
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
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('price');
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
        $countSelect->reset(Zend_Db_Select::HAVING);
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

    public function addOrdersCount($from = '', $to = '')
    {
        $orderItem = Mage::getResourceSingleton('sales/order_item');
        /* @var $orderItem Mage_Sales_Model_Entity_Quote */
        $attr = $orderItem->getAttribute('product_id');
        /* @var $attr Mage_Eav_Model_Entity_Attribute_Abstract */
        $attrId = $attr->getAttributeId();
        $tableName = $attr->getBackend()->getTable();
        $fieldName = $attr->getBackend()->isStatic() ? 'product_id' : 'value';

        $this->getSelect()
            ->joinLeft(array("order_items" => $tableName),
                "order_items.{$fieldName} = e.{$this->productEntityId} and order_items.attribute_id = {$attrId}", array())
            ->from("", array("orders" => "count(`order`.entity_id)"))
            ->group("e.{$this->productEntityId}");

        $order = Mage::getResourceSingleton('sales/order');
        /* @var $order Mage_Sales_Model_Entity_Order */
        $attr = $order->getAttribute('created_at');
        /* @var $attr Mage_Eav_Model_Entity_Attribute_Abstract */
        $attrId = $attr->getAttributeId();
        $tableName = $attr->getBackend()->getTable();
        $fieldName = $attr->getBackend()->isStatic() ? 'created_at' : 'value';

        if ($from != '' && $to != '') {
            $dateFilter = " and `order`.created_at BETWEEN '{$from}' AND '{$to}'";
        } else {
            $dateFilter = '';
        }

        $this->getSelect()
            ->joinLeft(array("order" => $tableName),
                "`order`.entity_id = order_items.entity_id".$dateFilter, array());

        return $this;
    }

    public function addOrderedQty($from = '', $to = '')
    {
        $orderItem = Mage::getResourceSingleton('sales/order_item');
        /* @var $orderItem Mage_Sales_Model_Entity_Quote */
        $attr = $orderItem->getAttribute('product_id');
        /* @var $attr Mage_Eav_Model_Entity_Attribute_Abstract */
        $attrId = $attr->getAttributeId();
        $tableName = $attr->getBackend()->getTable();
        $fieldName = $attr->getBackend()->isStatic() ? 'product_id' : 'value';

        $this->getSelect()
            ->joinLeft(array("order_items" => $tableName),
                "order_items.{$fieldName} = e.{$this->productEntityId} and order_items.attribute_id = {$attrId}", array())
            ;//->from("", array("orders" => "count(`order`.entity_id)"))
            //->group("e.{$this->productEntityId}");

        $order = Mage::getResourceSingleton('sales/order');
        /* @var $order Mage_Sales_Model_Entity_Order */
        $attr = $order->getAttribute('created_at');
        /* @var $attr Mage_Eav_Model_Entity_Attribute_Abstract */
        $attrId = $attr->getAttributeId();
        $tableName = $attr->getBackend()->getTable();
        $fieldName = $attr->getBackend()->isStatic() ? 'created_at' : 'value';

        if ($from != '' && $to != '') {
            $dateFilter = " and `order`.created_at BETWEEN '{$from}' AND '{$to}'";
        } else {
            $dateFilter = '';
        }

        $this->getSelect()
                ->joinRight(array("order" => $tableName),
                    "`order`.entity_id = order_items.entity_id".$dateFilter, array());

        $attr = $orderItem->getAttribute('qty_ordered');
        /* @var $attr Mage_Eav_Model_Entity_Attribute_Abstract */
        $attrId = $attr->getAttributeId();
        $tableName = $attr->getBackend()->getTable();
        $fieldName = $attr->getBackend()->isStatic() ? 'qty_ordered' : 'value';

        $this->getSelect()
            ->joinLeft(array("order_items2" => $tableName),
                "order_items2.entity_id = `order_items`.entity_id and order_items2.attribute_id = {$attrId}", array())
            ->from("", array("ordered_qty" => "sum(order_items2.{$fieldName})"))
            ->group("e.{$this->productEntityId}")
            ->having('ordered_qty > 0');

        return $this;
    }

    public function setOrder($attribute, $dir='desc')
    {
        switch ($attribute)
        {
            case 'carts':
            case 'orders':
            case 'ordered_qty':
                $this->getSelect()->order($attribute . ' ' . $dir);
                break;
            default:
                parent::setOrder($attribute, $dir);
        }

        return $this;
    }

    public function addViewsCount($from = '', $to = '')
    {
        /**
         * Getting event type id for catalog_product_view event
         */

        foreach (Mage::getModel('reports/event_type')->getCollection() as $eventType) {
            if ($eventType->getEventName() == 'catalog_product_view') {
                $productViewEvent = $eventType->getId();
                break;
            }
        }

        if ($from != '' && $to != '') {
            $dateFilter = " and logged_at BETWEEN '{$from}' AND '{$to}'";
        } else {
            $dateFilter = '';
        }

        $this->joinField('views', 'reports/event', 'COUNT(event_id)', 'object_id=entity_id', 'event_type_id='.$productViewEvent.$dateFilter, 'left')
            ->groupByAttribute('entity_id')
            ->getSelect()->order('views desc')
            ->having('views > 0');

        return $this;
    }
}