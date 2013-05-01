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


class Mage_Sales_Model_Invoice extends Mage_Core_Model_Abstract
{

    const STATUS_OPEN = 1;
    const STATUS_PAYED = 2;
    const STATUS_CANCELED = 3;

    protected static $_statuses = null;

    const TYPE_INVOICE = 1;
    const TYPE_CMEMO = 2;

    protected static $_types = null;

    protected $_addresses;

    protected $_items;

    protected $_order;
    protected $_invoice;

    protected $_payment;

    protected $_orderCurrency = null;

    protected function _construct()
    {
        $this->_init('sales/invoice');
    }

    public function setPayment(Mage_Sales_Model_Invoice_Payment $payment)
    {
        $this->_payment = $payment->setInvoice($this);
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return Mage_Sales_Model_Invoice_Payment
     */
    public function getPayment()
    {
        if (!$this->_payment instanceof Mage_Sales_Model_Order) {
            if ($this->getId()) {
                $payments = Mage::getResourceModel('sales/invoice_payment_collection')
                    ->addAttributeToSelect('*')
                    ->setInvoiceFilter($this->getId())
                    ->load();
                foreach ($payments as $payment) {
                    $this->_payment = $payment->setInvoice($this);
                    return $this->_payment;
                }
            }
        }
        return $this->_payment;
    }

    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        $this->setOrderId($order->getId());
        $this->setStoreId($order->getStoreId());
        $this->setRealOrderId($order->getRealOrderId());
        return $this;
    }

    /**
     * Get the order the invoice for created for
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!$this->_order instanceof Mage_Sales_Model_Order) {
            $this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
        }
        return $this->_order;
    }

    public function setInvoice(Mage_Sales_Model_Invoice $invoice)
    {
        $this->_invoice = $invoice;
        $this->setParentId($invoice->getId());
        return $this;
    }

    /**
     * Get the order the invoice for created for
     *
     * @return Mage_Sales_Model_Invoice
     */
    public function getInvoice()
    {
        if (!$this->_invoice instanceof Mage_Sales_Model_Invoice) {
            $this->_invoice = Mage::getModel('sales/invoice')->load($this->getParentId());
        }
        return $this->_invoice;
    }

    public function createFromOrder(Mage_Sales_Model_Order $order)
    {
        $this->setOrder($order);
        $this->setInvoiceType(self::TYPE_INVOICE);
        $this->setInvoiceStatusId(self::STATUS_OPEN);
        $this->setBaseCurrencyCode($order->getBaseCurrencyCode());
        $this->setStoreCurrencyCode($order->getStoreCurrencyCode());
        $this->setOrderCurrencyCode($order->getOrderCurrencyCode());
        return $this;
    }

    public function createFromInvoice(Mage_Sales_Model_Invoice $invoice)
    {
        $this->setOrder($invoice->getOrder());
        $this->setInvoiceType(self::TYPE_CMEMO);
        $this->setInvoiceStatusId(self::STATUS_OPEN);
        $this->setBaseCurrencyCode($invoice->getBaseCurrencyCode());
        $this->setStoreCurrencyCode($invoice->getStoreCurrencyCode());
        $this->setOrderCurrencyCode($invoice->getOrderCurrencyCode());
        foreach ($invoice->getAddressesCollection() as $address) {
            $newAddress = clone $address;
            $newAddress->setParentId(null);
            $this->addAddress($newAddress);
        }
        return $this;
    }

    public static function getStatuses()
    {
        if (is_null(self::$_statuses)) {
            self::$_statuses = array(
                self::STATUS_OPEN => Mage::helper('sales')->__('Pending'),
                self::STATUS_PAYED => Mage::helper('sales')->__('Payed'),
                self::STATUS_CANCELED => Mage::helper('sales')->__('Canceled'),
            );
        }
        return self::$_statuses;
    }

    public static function getStatusName($statusId)
    {
        if (is_null(self::$_statuses)) {
            self::getStatuses();
        }
        if (isset(self::$_statuses[$statusId])) {
            return self::$_statuses[$statusId];
        }
        return Mage::helper('sales')->__('Unknown Status');
    }

    public static function getTypes()
    {
        if (is_null(self::$_types)) {
            self::$_types = array(
                self::TYPE_INVOICE => Mage::helper('sales')->__('Invoice'),
                self::TYPE_CMEMO => Mage::helper('sales')->__('Credit Memo'),
            );
        }
        return self::$_types;
    }

    public static function getTypeName($typeId)
    {
        if (is_null(self::$_types)) {
            self::getTypes();
        }
        if (isset(self::$_types[$typeId])) {
            return self::$_types[$typeId];
        }
        return Mage::helper('sales')->__('Unknown Type');
    }

/*********************** ADDRESSES ***************************/

    public function getAddressesCollection()
    {
        if (is_null($this->_addresses)) {
            $this->_addresses = Mage::getResourceModel('sales/invoice_address_collection');

            if ($this->getId()) {
                $this->_addresses
                    ->addAttributeToSelect('*')
                    ->setInvoiceFilter($this->getId())
                    ->load();
                foreach ($this->_addresses as $address) {
                    $address->setInvoice($this);
                }
            }
        }
        return $this->_addresses;
    }

    public function getBillingAddress()
    {
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getAddressType()=='billing') {
                return $address;
            }
        }
        return false;
    }

    public function getShippingAddress()
    {
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getAddressType()=='shipping') {
                return $address;
            }
        }
        return false;
    }

    public function getAddressById($addressId)
    {
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getId()==$addressId) {
                return $address;
            }
        }
        return false;
    }

    public function addAddress(Mage_Sales_Model_Invoice_Address $address)
    {
        $address->setInvoice($this)->setParentId($this->getId());
        if (!$address->getId()) {
            $this->getAddressesCollection()->addItem($address);
        }
        return $this;
    }

    public function setBillingAddress(Mage_Sales_Model_Invoice_Address $address)
    {
        $old = $this->getBillingAddress();
        if (!empty($old)) {
            $address->setId($old->getId());
        }
        $this->addAddress($address->setAddressType('billing'));
        return $this;
    }

    public function setShippingAddress(Mage_Sales_Model_Invoice_Address $address)
    {
        $old = $this->getShippingAddress();
        if (!empty($old)) {
            $address->setId($old->getId());
        }
        $this->addAddress($address->setAddressType('shipping'));
        return $this;
    }

/*********************** ITEMS ***************************/

    public function getItemsCollection()
    {
        if (empty($this->_items)) {
            $this->_items = Mage::getResourceModel('sales/invoice_item_collection');

            if ($this->getId()) {
                $this->_items
                    ->addAttributeToSelect('*')
                    ->setInvoiceFilter($this->getId())
                    ->load();
                foreach ($this->_items as $item) {
                    $item->setInvoice($this);
                }
            }
        }
        return $this->_items;
    }

    public function getAllItems()
    {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted()) {
                $items[] =  $item;
            }
        }
        return $items;
    }

    public function getItemById($itemId)
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getId()==$itemId) {
                return $item;
            }
        }
        return false;
    }

    public function addItem(Mage_Sales_Model_Invoice_Item $item)
    {
        $item->setInvoice($this)->setParentId($this->getId());
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }

    protected function _beforeSave()
    {
        if (!$this->getEntityId()) {
            if (self::TYPE_INVOICE == $this->getInvoiceType()) {
                // we are creating new invoice for order
                if (! $this->getOrder()) {
                    Mage::throwException(Mage::helper('sales')->__('No order for invoice'));
                }
                foreach ($this->getOrder()->getAddressesCollection() as $address) {
//                    print_r($address->getData());
                    $this->addAddress(Mage::getModel('sales/invoice_address')->importOrderAddress($address));
                }
            } elseif (self::TYPE_CMEMO == $this->getInvoiceType()) {
                // we are creating new memo for invoice
                if (! $this->getInvoice()) {
                    Mage::throwException(Mage::helper('sales')->__('No invoice for credit memo'));
                }
                foreach ($this->getInvoice()->getAddressesCollection() as $address) {
                    $this->addAddress(Mage::getModel('sales/invoice_address')->importInvoiceAddress($address));
                }
            }
        }

        foreach ($this->getAllItems() as $item) {
            $this->setSubtotal($this->getSubtotal()+$item->getRowTotal());
        }
        $this->setGrandTotal($this->getSubtotal());

        return parent::_beforeSave();
    }

    public function processPayment()
    {
        $this->setPayment(Mage::getModel('sales/invoice_payment')->setInvoice($this)->importOrderPayment($this->getOrder()->getPayment()));

//        $method = $this->getPayment()->getMethod();
//
//        // TOFIX
//        if (!($modelName = Mage::getStoreConfig('payment/'.$method.'/model'))
//            ||!($model = Mage::getModel($modelName))) {
//            return $this;
//        }
//
//        $model->onInvoiceCreate($this->getPayment());
        $this->setInvoiceStatusId(self::STATUS_PAYED);

        if (self::STATUS_PAYED == $this->getInvoiceStatusId()) {
            $this->getPayment()->save();
            $this->setTotalPayed($this->getTotalDue());
            $this->save();
            foreach ($this->getItemsCollection() as  $item) {
                $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
                $orderItem->setQtyShipped($orderItem->getQtyShipped() + $item->getQty());
                $orderItem->save();
            }
        }


        return $this;
    }

    public function getOrderCurrency()
    {
        if (is_null($this->_orderCurrency)) {
            $this->_orderCurrency = Mage::getModel('directory/currency')->load($this->getOrderCurrencyCode());
        }
        return $this->_orderCurrency;
    }

    public function setData($key, $value='')
    {
        if ('items' === $key) {
            $errors = array();
            foreach ($value as $itemId => $qty) {
                if ($qty > 0) {
                    if ($this->isInvoice()) {
                        $orderItem = Mage::getModel('sales/order_item')->load($itemId);
                        if ($orderItem->getQtyToShip() < $qty) {
                            $errors[] = Mage::helper('sales')->__("There's not enough qty of product '%s' in order to ship", $orderItem->getSku() . ' - ' . $orderItem->getName());
                        } else {
                            $item = Mage::getModel('sales/invoice_item')->setInvoice($this)->importOrderItem($orderItem)->setQty($qty);
                            $item->calcRowTotal()->calcRowWeight()->calcTaxAmount();
                            $this->addItem($item);
                        }
                    } elseif ($this->isCmemo()) {
                        $invoiceItem = Mage::getModel('sales/invoice_item')->load($itemId);
                        if ($invoiceItem->getQty() < $qty) {
                            $errors[] = Mage::helper('sales')->__("There's not enough qty of product '%s' in invoice to return", $invoiceItem->getSku() . ' - ' . $invoiceItem->getName());
                        } else {
                            $item = Mage::getModel('sales/invoice_item')->setInvoice($this)->importInvoiceItem($invoiceItem)->setQty($qty);
                            $item->calcRowTotal()->calcRowWeight()->calcTaxAmount();
                            $this->addItem($item);
                        }
                    } else {
                        Mage::throwException(Mage::helper('sales')->__(e::helper('sales')->__('Unknown invoice type'));
                    }
                }
            }
            if (!empty($errors)) {
                Mage::throwException(implode(',<br>', $errors));
            }
        }
        return parent::setData($key, $value);
    }

    public function isInvoice()
    {
        return (self::TYPE_INVOICE == $this->getInvoiceType());
    }

    public function isCmemo()
    {
        return (self::TYPE_CMEMO == $this->getInvoiceType());
    }

    /**
     * Enter description here...
     *
     * @return Mage_Sales_Model_Invoice
     */
    public function calcTotalDue()
    {
        $this->setTotalDue(max($this->getGrandTotal() - $this->getTotalPaid(), 0));
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return float
     */
    public function getTotalDue()
    {
        $this->calcTotalDue();
        return $this->getData('total_due');
    }

    /**
     * Enter description here...
     *
     * @return Mage_Sales_Model_Invoice
     */
    public function collectTotals()
    {
        $this->setGrandTotal(0);
        foreach ($this->getItemsCollection() as $item) {
            $this->setGrandTotal($this->getGrandTotal() + $item->getRowTotal());
        }
        return $this;
    }

}
