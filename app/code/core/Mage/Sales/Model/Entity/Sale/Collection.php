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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Sales_Model_Entity_Sale_Collection extends Varien_Object implements IteratorAggregate
{

    /**
     * Read connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;

    protected $_items = array();

    protected $_totals = array('lifetime' => 0, 'num_orders' => 0);

    /**
     * Entity attribute
     *
     * @var Mage_Eav_Model_Entity_Abstract
     */
    protected $_entity;

    /**
     * Collection's Zend_Db_Select object
     *
     * @var Zend_Db_Select
     */
    protected $_select;

    /**
     * Enter description here...
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;

    public function __construct()
    {
        $this->_entity = Mage::getModel('sales_entity/order');
        $this->_read = $this->_entity->getReadConnection();
    }

    public function setCustomerFilter(Mage_Customer_Model_Customer $customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    public function load($printQuery = false, $logQuery = false)
    {

        $this->_select = new Zend_Db_Select($this->_read);
        $this->getSelect()->from(array('paid' => $this->getAttribute('total_paid')->getBackend()->getTable()),
                array('store_id', 'lifetime' => 'sum(paid.value * rate.value)', 'avgsale' => 'avg(paid.value * rate.value)', 'num_orders' => 'count(paid.entity_id)'))
            ->join(
                array('rate' => $this->getAttribute('store_to_order_rate')->getBackend()->getTable()),
                'rate.' . $this->getEntity()->getIdFieldName() . '=paid.' . $this->getEntity()->getIdFieldName()
                . ' and rate.entity_type_id='. $this->getEntity()->getTypeId()
                . ' and rate.attribute_id='. $this->getAttribute('store_to_order_rate')->getId(),
                array('value')
            )
            ->where('paid.entity_type_id=?', $this->getEntity()->getTypeId())
            ->where('paid.attribute_id=?', $this->getAttribute('total_paid')->getId())
            ->group('paid.store_id')
        ;
        if ($this->_customer instanceof Mage_Customer_Model_Customer) {
            $this->getSelect()
                ->join(
                    array('customer' => $this->getAttribute('customer_id')->getBackend()->getTable()),
                    'customer.' . $this->getEntity()->getIdFieldName() . '=paid.' . $this->getEntity()->getIdFieldName()
                    . ' and customer.entity_type_id='. $this->getEntity()->getTypeId()
                    . ' and customer.attribute_id='. $this->getAttribute('customer_id')->getId(),
                    array('value')
                )
                ->where('customer.value=?', $this->_customer->getId())
            ;
        }

        $this->printLogQuery($printQuery, $logQuery);
        try {
            $values = $this->_read->fetchAll($this->getSelect()->__toString());
        } catch (Exception $e) {
            $this->printLogQuery(true, true, $sql);
            throw $e;
        }
        $stores = Mage::getResourceModel('core/store_collection')->setWithoutDefaultFilter()->load()->toOptionHash();
        if (! empty($values)) {
            foreach ($values as $v) {
                $obj = new Varien_Object($v);
                $this->_items[ $v['store_id'] ] = $obj;
                $this->_items[ $v['store_id'] ]->setStoreName($stores[$obj->getStoreId()]);
                $this->_items[ $v['store_id'] ]->setAvgNormalized($obj->getAvgsale() * $obj->getNumOrders());
                foreach ($this->_totals as $key => $value) {
                    $this->_totals[$key] += $obj->getData($key);
                }
            }
            if ($this->_totals['num_orders']) {
                $this->_totals['avgsale'] = $this->_totals['lifetime'] / $this->_totals['num_orders'];
            }
        }

        return $this;
    }

    /**
     * Print and/or log query
     *
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return  Mage_Sales_Model_Entity_Order_Attribute_Collection_Paid
     */
    public function printLogQuery($printQuery = false, $logQuery = false, $sql = null) {
        if ($printQuery) {
            echo is_null($sql) ? $this->getSelect()->__toString() : $sql;
        }

        if ($logQuery){
            Mage::log(is_null($sql) ? $this->getSelect()->__toString() : $sql);
        }
        return $this;
    }

    /**
     * Get zend db select instance
     *
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
        return $this->_select;
    }

    /**
     * Enter description here...
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getAttribute($attr)
    {
        return $this->_entity->getAttribute($attr);
    }

    /**
     * Enter description here...
     *
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Enter description here...
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_items);
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Enter description here...
     *
     * @return Varien_Object
     */
    public function getTotals()
    {
        return new Varien_Object($this->_totals);
    }

}
