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

/**
 * Order model
 *
 * Order Attributes
 *  entity_id (id)
 *  state_id
 *  is_virtual
 *  is_multi_payment
 *
 *  base_currency_code
 *  store_currency_code
 *  order_currency_code
 *  store_to_base_rate
 *  store_to_order_rate
 *
 *  remote_ip
 *  quote_id
 *  quote_address_id
 *  billing_address_id
 *  shipping_address_id
 *  coupon_code
 *  giftcert_code
 *  weight
 *
 *  shipping_method
 *  shipping_description
 *  tracking_numbers
 *
 *  subtotal
 *  tax_amount
 *  shipping_amount
 *  discount_amount
 *  giftcert_amount
 *  custbalance_amount
 *  grand_total
 *
 *  total_paid
 *  total_due
 *  total_qty_ordered
 *  applied_rule_ids
 *
 *  customer_id
 *  customer_group_id
 *  customer_email
 *  customer_note
 *  customer_note_notify
 *
 * Supported events:
 *  sales_order_load_after
 *  sales_order_save_before
 *  sales_order_save_after
 *  sales_order_delete_before
 *  sales_order_delete_after
 *
 */
class Mage_Sales_Model_Order extends Mage_Core_Model_Abstract
{
    /**
     * XML configuration paths
     */
    const XML_PATH_NEW_ORDER_EMAIL_TEMPLATE     = 'sales/new_order/email_template';
    const XML_PATH_NEW_ORDER_EMAIL_IDENTITY     = 'sales/new_order/email_identity';
    const XML_PATH_UPDATE_ORDER_EMAIL_TEMPLATE  = 'sales/order_update/email_template';
    const XML_PATH_UPDATE_ORDER_EMAIL_IDENTITY  = 'sales/order_update/email_identity';

    const STATE_NEW        = 'new';
    const STATE_PROCESSING = 'processing';
    const STATE_COMPLETE   = 'complete';
    const STATE_CLOSED     = 'closed';
    const STATE_CANCELED   = 'canceled';

    const PAYMENT_STATE_PENDING        = 1;
    const PAYMENT_STATE_NOT_AUTHORIZED = 2;
    const PAYMENT_STATE_AUTHORIZED     = 3;
    const PAYMENT_STATE_PARTIAL        = 4;
    const PAYMENT_STATE_PAID           = 5;

    const SHIPPING_STATE_PENDING   = 1;
    const SHIPPING_STATE_PARTIAL   = 2;
    const SHIPPING_STATE_SHIPPED   = 3;

    const REFUND_STATE_NOT_REFUND  = 1;
    const REFUND_STATE_PANDING     = 2;
    const REFUND_STATE_PARTIAL     = 3;
    const REFUND_STATE_REFUNDED    = 4;

    protected $_eventPrefix = 'sales_order';
    protected $_eventObject = 'order';

    protected $_addresses;
    protected $_items;
    protected $_payments;
    protected $_statusHistory;
    protected $_orderCurrency = null;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('sales/order');
    }

    /**
     * Retrieve order cancel availability
     *
     * @return bool
     */
    public function canCancel()
    {
        if ($this->getState() === self::STATE_CANCELED) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve order invoice availability
     *
     * @return bool
     */
    public function canInvoice()
    {
        return false;
    }

    /**
     * Retrieve order credit memo (refund) availability
     *
     * @return bool
     */
    public function canCreditmemo()
    {
        return false;
    }

    /**
     * Retrieve order hold availability
     *
     * @return bool
     */
    public function canHold()
    {
        return false;
    }

    /**
     * Retrieve order unhold availability
     *
     * @return bool
     */
    public function canUnhold()
    {
        return false;
    }

    /**
     * Retrieve order shipment availability
     *
     * @return bool
     */
    public function canShip()
    {
        return false;
    }

    /**
     * Retrieve order edit availability
     *
     * @return bool
     */
    public function canEdit()
    {
        if ($this->getState() === self::STATE_CANCELED) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve order reorder availability
     *
     * @return bool
     */
    public function canReorder()
    {
        return false;
    }

    /**
     * Retrieve order configuration model
     *
     * @return Mage_Sales_Model_Order_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('sales/order_config');
    }

    /**
     * Place order payments
     *
     * @return unknown
     */
    protected function _placePayment()
    {
        $this->getPayment()->place();
        return $this;
    }

    /**
     * Retrieve order payment model object
     *
     * @return Mage_Sales_Model_Order_Payment
     */
    public function getPayment()
    {
        foreach ($this->getPaymentsCollection() as $payment) {
            if (!$payment->isDeleted()) {
                return $payment;
            }
        }
        return false;
    }

    /**
     * Declare order billing address
     *
     * @param   Mage_Sales_Model_Order_Address $address
     * @return  Mage_Sales_Model_Order
     */
    public function setBillingAddress(Mage_Sales_Model_Order_Address $address)
    {
        $old = $this->getBillingAddress();
        if (!empty($old)) {
            $address->setId($old->getId());
        }
        $this->addAddress($address->setAddressType('billing'));
        return $this;
    }

    /**
     * Declare order shipping address
     *
     * @param   Mage_Sales_Model_Order_Address $address
     * @return  Mage_Sales_Model_Order
     */
    public function setShippingAddress(Mage_Sales_Model_Order_Address $address)
    {
        $old = $this->getShippingAddress();
        if (!empty($old)) {
            $address->setId($old->getId());
        }
        $this->addAddress($address->setAddressType('shipping'));
        return $this;
    }

    /**
     * Retrieve order billing address
     *
     * @return Mage_Sales_Model_Order_Address
     */
    public function getBillingAddress()
    {
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getAddressType()=='billing' && !$address->isDeleted()) {
                return $address;
            }
        }
        return false;
    }

    /**
     * Retrieve order shipping address
     *
     * @return Mage_Sales_Model_Order_Address
     */
    public function getShippingAddress()
    {
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getAddressType()=='shipping' && !$address->isDeleted()) {
                return $address;
            }
        }
        return false;
    }

    /**
     * Declare order state
     *
     * @param   string $state
     * @return  Mage_Sales_Model_Order
     */
    public function setState($state)
    {
        if ($state != $this->getState()) {
            $this->setData('state', $state);
            $this->addStatusToHistory($this->getConfig()->getStateDefaultStatus($state), $this->getCustomerNote());
        }
        return $this;
    }

    /**
     * Retrieve order status
     *
     * @return Mage_Sales_Model_Order_Status
     */
    public function getStatus()
    {
        return $this->getData('status');
    }

    /**
     * Retrieve label of order status
     *
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->getConfig()->getStatusLabel($this->getStatus());
    }

    /**
     * Add status change information to history
     *
     * @param   string $status
     * @param   string $comments
     * @param   boolean $is_customer_notified
     * @return  Mage_Sales_Model_Order
     */
    public function addStatusToHistory($status, $comment='', $isCustomerNotified=false)
    {
        $status = Mage::getModel('sales/order_status_history')
            ->setStatus($status)
            ->setComment($comment)
            ->setIsCustomerNotified($isCustomerNotified);
        $this->addStatusHistory($status);
        return $this;
    }


    /**
     * Place order
     *
     * @return Mage_Sales_Model_Order
     */
    public function place()
    {
        $this->_placePayment();
        return $this;
    }

    /**
     * Cancel order
     *
     * @return Mage_Sales_Model_Order
     */
    public function cancel()
    {
        if ($this->canCancel()) {
            $this->getPayment()->cancel();
            foreach ($this->getAllItems() as $item) {
                $item->cancel();
            }
            $this->setState(self::STATE_CANCELED);
        }
        return $this;
    }

    protected function _beforeSave()
    {
        if (!$this->getId()) {
            $this->setState(self::STATE_NEW);
        }
        return parent::_beforeSave();
    }

    /**
     * Add tracking number to order
     *
     * @param   string $number
     * @return  Mage_Sales_Model_Order
     */
    public function addTrackingNumber($number)
    {
        $numbers = $this->getTrackingNumbers();
        if (!in_array($number, $numbers)) {
            $numbers[] = $number;
            $this->setTrackingNumbers($numbers);
        }
        return $this;
    }

    /**
     * Remove tracking number
     *
     * @param   string $number
     * @return  Mage_Sales_Model_Order
     */
    public function removeTrackingNumber($number)
    {
        $numbers = $this->getTrackingNumbers();
        $key = array_search($number, $numbers);
        if ($key !== false) {
            unset($numbers[$key]);
            $this->setTrackingNumbers($numbers);
        }
        return $this;
    }

    /**
     * Retrieve tracking numbers
     *
     * @return array
     */
    public function getTrackingNumbers()
    {
        if ($this->getData('tracking_numbers')) {
            return explode(',', $this->getData('tracking_numbers'));
        }
        return array();
    }

    /**
     * Declare tracking numbers
     *
     * @param   mixed $numbers
     * @return  Mage_Sales_Model_Order
     */
    public function setTrackingNumbers($numbers)
    {
        if (is_array($numbers)) {
            $numbers = implode(',', $numbers);
        }
        $this->setData('tracking_numbers', $numbers);
        return $this;
    }

    public function getShippingCarrier()
    {
        $carrierModel = $this->getData('shipping_carrier');
        if (is_null($carrierModel)) {
            $carrierModel = false;
            /**
             * $method - carrier_method
             */
            if ($method = $this->getShippingMethod()) {
                $data = explode('_', $method);
                $carrierCode = $data[0];
                $className = Mage::getStoreConfig('carriers/'.$carrierCode.'/model');
                if ($className) {
                    $carrierModel = Mage::getModel($className);
                }
            }
            $this->setData('shipping_carrier', $carrierModel);
        }
        return $carrierModel;
    }

    public function processPayments()
    {
        $method = $this->getPayment()->getMethod();

        if (!($modelName = Mage::getStoreConfig('payment/'.$method.'/model'))
            ||!($model = Mage::getModel($modelName))) {
            return $this;
        }

        $this->setDocument($this->getOrder());

        $model->onOrderValidate($this->getPayment());

        if ($this->getPayment()->getStatus()!=='APPROVED') {
            $errors = $this->getErrors();
            $errors[] = $this->getPayment()->getStatusDescription();
            $this->setErrors($errors);
        }

        return $this;
    }

    /**
     * Sending email with order data
     *
     * @return Mage_Sales_Model_Order
     */
    public function sendNewOrderEmail()
    {
        $itemsBlock = Mage::getHelper('sales/order_email_items')
            ->setOrder($this);
        $paymentBlock = Mage::helper('payment')->getInfoBlock($this->getPayment());

        Mage::getModel('core/email_template')->sendTransactional(
            Mage::getStoreConfig(self::XML_PATH_NEW_ORDER_EMAIL_TEMPLATE),
            Mage::getStoreConfig(self::XML_PATH_NEW_ORDER_EMAIL_IDENTITY),
            $this->getCustomerEmail(),
            $this->getBillingAddress()->getName(),
            array(
              'order'       => $this,
              'billing'     => $this->getBillingAddress(),
              'payment_html'=> $paymentBlock->toHtml(),
              'items_html'  => $itemsBlock->toHtml(),
            )
        );
        return $this;
    }

    /**
     * Sending email with order update information
     *
     * @return Mage_Sales_Model_Order
     */
    public function sendOrderUpdateEmail($comment='')
    {
        Mage::getModel('core/email_template')
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_UPDATE_ORDER_EMAIL_TEMPLATE),
                Mage::getStoreConfig(self::XML_PATH_UPDATE_ORDER_EMAIL_IDENTITY),
                $this->getCustomerEmail(),
                $this->getBillingAddress()->getName(),
                array(
                    'order'=>$this,
                    'billing'=>$this->getBillingAddress(),
                    'comment'=>$comment
                )
            );
        return $this;
    }

/*********************** ADDRESSES ***************************/

    public function getAddressesCollection()
    {
        if (is_null($this->_addresses)) {
            $this->_addresses = Mage::getResourceModel('sales/order_address_collection');

            if ($this->getId()) {
                $this->_addresses
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($this->getId())
                    ->load();
                foreach ($this->_addresses as $address) {
                    $address->setOrder($this);
                }
            }
        }

        return $this->_addresses;
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

    public function addAddress(Mage_Sales_Model_Order_Address $address)
    {
        $address->setOrder($this)->setParentId($this->getId());
        if (!$address->getId()) {
            $this->getAddressesCollection()->addItem($address);
        }
        return $this;
    }

/*********************** ITEMS ***************************/

    public function getItemsCollection()
    {
        if (is_null($this->_items)) {
            $this->_items = Mage::getResourceModel('sales/order_item_collection');

            if ($this->getId()) {
                $this->_items
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($this->getId())
                    ->load();
                foreach ($this->_items as $item) {
                    $item->setOrder($this);
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

    public function addItem(Mage_Sales_Model_Order_Item $item)
    {
        $item->setOrder($this)->setParentId($this->getId());
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }

/*********************** PAYMENTS ***************************/

    public function getPaymentsCollection()
    {
        if (is_null($this->_payments)) {
            $this->_payments = Mage::getResourceModel('sales/order_payment_collection');

            if ($this->getId()) {
                $this->_payments
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($this->getId())
                    ->load();
                foreach ($this->_payments as $payment) {
                    $payment->setOrder($this);
                }
            }
        }
        return $this->_payments;
    }

    public function getAllPayments()
    {
        $payments = array();
        foreach ($this->getPaymentsCollection() as $payment) {
            if (!$payment->isDeleted()) {
                $payments[] =  $payment;
            }
        }
        return $payments;
    }


    public function getPaymentById($paymentId)
    {
        foreach ($this->getPaymentsCollection() as $payment) {
            if ($payment->getId()==$paymentId) {
                return $payment;
            }
        }
        return false;
    }

    public function addPayment(Mage_Sales_Model_Order_Payment $payment)
    {
        $payment->setOrder($this)
            ->setParentId($this->getId());
        if (!$payment->getId()) {
            $this->getPaymentsCollection()->addItem($payment);
        }
        return $this;
    }

    public function setPayment(Mage_Sales_Model_Order_Payment $payment)
    {
        if (!$this->getIsMultiPayment() && ($old = $this->getPayment())) {
            $payment->setId($old->getId());
        }
        $this->addPayment($payment);

        return $payment;
    }

/*********************** STATUSES ***************************/

    /**
     * Enter description here...
     *
     * @return Mage_Sales_Model_Entity_Order_Status_History_Collection
     */
    public function getStatusHistoryCollection()
    {
        if (is_null($this->_statusHistory)) {
            $this->_statusHistory = Mage::getResourceModel('sales/order_status_history_collection');

            if ($this->getId()) {
                $this->_statusHistory
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($this->getId())
                    ->load();
                foreach ($this->_statusHistory as $status) {
                    $status->setOrder($this);
                }
            }
        }
        return $this->_statusHistory;
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getAllStatusHistory()
    {
        $history = array();
        foreach ($this->getStatusHistoryCollection() as $status) {
            if (!$status->isDeleted()) {
                $history[] =  $status;
            }
        }
        return $history;
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getVisibleStatusHistory()
    {
        $history = array();
        foreach ($this->getStatusHistoryCollection() as $status) {
            if (!$status->isDeleted() && $status->getComment() && $status->getIsCustomerNotified()) {
                $history[] =  $status;
            }
        }
        return $history;
    }

    public function getStatusHistoryById($statusId)
    {
        foreach ($this->getStatusHistoryCollection() as $status) {
            if ($status->getId()==$statusId) {
                return $status;
            }
        }
        return false;
    }

    public function addStatusHistory(Mage_Sales_Model_Order_Status_History $status)
    {
        $status->setOrder($this)
            ->setParentId($this->getId())
            ->setStoreId($this->getStoreId());
        $this->setStatus($status->getStatus());
        if (!$status->getId()) {
            $this->getStatusHistoryCollection()->addItem($status);
        }
        return $this;
    }


    /**
     * Enter description here...
     *
     * @return string
     */
    public function getRealOrderId()
    {
        $id = $this->getData('real_order_id');
        if (is_null($id)) {
            $id = $this->getIncrementId();
        }
        return $id;
    }

    /**
     * Enter description here...
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getOrderCurrency()
    {
        if (is_null($this->_orderCurrency)) {
            $this->_orderCurrency = Mage::getModel('directory/currency')->load($this->getOrderCurrencyCode());
        }
        return $this->_orderCurrency;
    }

    /**
     * Retrieve formated price value includeing order rate
     *
     * @param   float $price
     * @return  string
     */
    public function formatPrice($price)
    {
        if (!($rate = floatval($this->getStoreToOrderRate()))) {
            $rate = 1;
        }
        //$price = $price*$rate;
        return $this->getOrderCurrency()->format($price);
    }

    /**
     * Enter description here...
     *
     * @return Mage_Sales_Model_Order
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

    public function getCreatedAtFormated($format)
    {
        return Mage::getHelper('core/text')->formatDate($this->getCreatedAt(), $format);
    }

    public function getEmailCustomerNote()
    {
        if ($this->getCustomerNoteNotify()) {
            return $this->getCustomerNote();
        }
        return '';
    }
}
