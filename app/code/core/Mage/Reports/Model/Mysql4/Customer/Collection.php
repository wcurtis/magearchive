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
 * Customers Report collection
 *
 * @category   Mage
 * @package    Mage_Reports
 */

class Mage_Reports_Model_Mysql4_Customer_Collection extends Mage_Customer_Model_Entity_Customer_Collection
{

    protected $_customerIdTableName;
    protected $_customerIdFieldName;
    protected $_orderEntityTableName;
    protected $_orderEntityFieldName;

    public function __construct()
    {
        parent::__construct();
    }

    public function addCartInfo()
    {
        foreach ($this->getItems() as $item)
        {
            $quote = Mage::getModel('sales/quote')->loadByCustomer($item->getId());

            if (is_object($quote))
            {
                $totals = $quote->getTotals();
                $item->setTotal($totals['subtotal']->getValue());
                $quote_items = Mage::getResourceModel('sales/quote_item_collection')->setQuoteFilter($quote->getId());
                $quote_items->load();
                $item->setItems($quote_items->count());
            } else {
                $item->remove();
            }

        }
        return $this;
    }

    public function addCustomerName()
    {
        $this->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname')
            ->addExpressionAttributeToSelect('name', 'CONCAT({{firstname}}," ",{{lastname}})', array('firstname', 'lastname'));

        return $this;
    }

    /**
     * Order for each customer
     */
    public function joinOrders($from = '', $to = '')
    {
        $order = Mage::getResourceSingleton('sales/order');
        /* @var $order Mage_Sales_Model_Entity_Order */
        $attr = $order->getAttribute('customer_id');
        /* @var $attr Mage_Eav_Model_Entity_Attribute_Abstract */
        $attrId = $attr->getAttributeId();
        $this->_customerIdTableName = $attr->getBackend()->getTable();
        $this->_customerIdFieldName = $attr->getBackend()->isStatic() ? 'customer_id' : 'value';

        $this->getSelect()
            ->joinLeft($this->_customerIdTableName,
                "{$this->_customerIdTableName}.{$this->_customerIdFieldName}=e.entity_id AND ".
                "{$this->_customerIdTableName}.attribute_id={$attrId}", array());

        $attr = $order->getAttribute('entity_id');
        /* @var $attr Mage_Eav_Model_Entity_Attribute_Abstract */
        $attrId = $attr->getAttributeId();
        $this->_orderEntityTableName = $attr->getBackend()->getTable();
        $this->_orderEntityFieldName = $attr->getBackend()->isStatic() ? 'entity_id' : 'value';

        if ($from != '' && $to != '') {
            $dateFilter = " and {$this->_orderEntityTableName}.created_at BETWEEN '{$from}' AND '{$to}'";
        } else {
            $dateFilter = '';
        }

        $this->getSelect()
            ->joinLeft($this->_orderEntityTableName,
                "{$this->_orderEntityTableName}.entity_id={$this->_customerIdTableName}.entity_id AND ".
                "{$this->_orderEntityTableName}.parent_id=0".$dateFilter, array());

        return $this;
    }

    public function addOrdersCount()
    {
        $this->getSelect()
            ->from('', array("orders_count" => "COUNT({$this->_orderEntityTableName}.entity_id)"))
            ->group("e.entity_id")
            ->having('orders_count > 0');

        return $this;
    }

    /**
     * Order summary info for each customer
     * such as orders_count, orders_avg_amount, orders_total_amount
     */
    public function addSumAvgTotals($storeId = 0)
    {
        /**
         * Join subtotal attribute
         */
        $order = Mage::getResourceSingleton('sales/order');
        /* @var $order Mage_Sales_Model_Entity_Order */
        $attr = $order->getAttribute('base_subtotal');
        /* @var $attr Mage_Eav_Model_Entity_Attribute_Abstract */
        $attrId = $attr->getAttributeId();
        $subtotalTableName = $attr->getBackend()->getTable();
        $subtotalFieldName = $attr->getBackend()->isStatic() ? 'base_subtotal' : 'value';

        $this->getSelect()
            ->joinLeft(array('_avg_'.$subtotalTableName => $subtotalTableName),
                "_avg_{$subtotalTableName}.entity_id={$this->_orderEntityTableName}.entity_id AND ".
                "_avg_{$subtotalTableName}.attribute_id={$attrId}", array())
            ->joinLeft(array('_sum_'.$subtotalTableName => $subtotalTableName),
                "_sum_{$subtotalTableName}.entity_id={$this->_orderEntityTableName}.entity_id AND ".
                "_sum_{$subtotalTableName}.attribute_id={$attrId}", array());

        /**
         * Join total_refunded attribute
         */
        $attr = $order->getAttribute('base_total_refunded');
        /* @var $attr Mage_Eav_Model_Entity_Attribute_Abstract */
        $attrId = $attr->getAttributeId();
        $totalRefundedTableName = $attr->getBackend()->getTable();
        $totalRefundedFieldName = $attr->getBackend()->isStatic() ? 'base_total_refunded' : 'value';

        $this->getSelect()
            ->joinLeft(array('_refund_'.$totalRefundedTableName => $totalRefundedTableName),
                "_refund_{$totalRefundedTableName}.entity_id={$this->_orderEntityTableName}.entity_id AND ".
                "_refund_{$totalRefundedTableName}.attribute_id={$attrId}", array());

        /**
         * Join total_canceled attribute
         */
        $attr = $order->getAttribute('base_total_canceled');
        /* @var $attr Mage_Eav_Model_Entity_Attribute_Abstract */
        $attrId = $attr->getAttributeId();
        $totalCanceledTableName = $attr->getBackend()->getTable();
        $totalCanceledFieldName = $attr->getBackend()->isStatic() ? 'base_total_canceled' : 'value';

        $this->getSelect()
            ->joinLeft(array('_cancel_'.$totalCanceledTableName => $totalCanceledTableName),
                "_cancel_{$totalCanceledTableName}.entity_id={$this->_orderEntityTableName}.entity_id AND ".
                "_cancel_{$totalCanceledTableName}.attribute_id={$attrId}", array());

        /**
         * Join discount_amount attribute
         */
        $attr = $order->getAttribute('base_discount_amount');
        /* @var $attr Mage_Eav_Model_Entity_Attribute_Abstract */
        $attrId = $attr->getAttributeId();
        $discountAmountTableName = $attr->getBackend()->getTable();
        $discountAmountFieldName = $attr->getBackend()->isStatic() ? 'base_discount_amount' : 'value';

        $this->getSelect()
            ->joinLeft(array('_discount_'.$discountAmountTableName => $discountAmountTableName),
                "_discount_{$discountAmountTableName}.entity_id={$this->_orderEntityTableName}.entity_id AND ".
                "_discount_{$discountAmountTableName}.attribute_id={$attrId}", array());

        if ($storeId == 0) {
            /**
             * Join store_to_base_rate attribute
             */
            $attr = $order->getAttribute('store_to_base_rate');
            /* @var $attr Mage_Eav_Model_Entity_Attribute_Abstract */
            $attrId = $attr->getAttributeId();
            $storeToBaseRateTableName = $attr->getBackend()->getTable();
            $storeToBaseRateFieldName = $attr->getBackend()->isStatic() ? 'store_to_base_rate' : 'value';

            $this->getSelect()
                ->joinLeft(array('_s2br_'.$storeToBaseRateTableName => $storeToBaseRateTableName),
                    "_s2br_{$storeToBaseRateTableName}.entity_id={$this->_orderEntityTableName}.entity_id AND ".
                    "_s2br_{$storeToBaseRateTableName}.attribute_id={$attrId}", array());

            /**
             * calculate average and total amount
             */
            $expr = "(_avg_{$subtotalTableName}.{$subtotalFieldName}-IFNULL(_discount_{$discountAmountTableName}.{$discountAmountFieldName},0)-IFNULL(_cancel_{$totalCanceledTableName}.{$totalCanceledFieldName},0)-IFNULL(_refund_{$totalRefundedTableName}.{$totalRefundedFieldName},0))/_s2br_{$storeToBaseRateTableName}.{$storeToBaseRateFieldName}";

        } else {

            /**
             * calculate average and total amount
             */
            $expr = "_avg_{$subtotalTableName}.{$subtotalFieldName}-IFNULL(_discount_{$discountAmountTableName}.{$discountAmountFieldName},0)-IFNULL(_cancel_{$totalCanceledTableName}.{$totalCanceledFieldName},0)-IFNULL(_refund_{$totalRefundedTableName}.{$totalRefundedFieldName},0)";
        }

        $this->getSelect()
            ->from('', array("orders_avg_amount" => "IFNULL(AVG({$expr}),0)"))
            ->from('', array("orders_sum_amount" => "IFNULL(SUM({$expr}),0)"));

        return $this;
    }

    public function orderByTotalAmount($dir = 'desc')
    {
        $this->getSelect()
            ->order("orders_sum_amount {$dir}");
        return $this;
    }

    public function orderByCustomerRegistration($dir = 'desc')
    {
        $this->addAttributeToSort('entity_id', $dir);
        return $this;
    }

    public function orderByOrdersCount($dir = 'desc')
    {
        $this->getSelect()
            ->order("orders_count {$dir}")
            ->having('orders_count > 0');
        return $this;
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
}