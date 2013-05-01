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
 * Quote model
 *
 * Attributes:
 ** GENERAL
 *  entity_id (id)
 *  is_active
 *
 ** NOT SORTED
 *  remote_ip
 *  checkout_method
 *  password_hash
 *  billing_address_id
 *  converted_at
 *  coupon_code
 *  giftcert_code
 *  grand_total
 *  orig_order_id
 *  applied_rule_ids
 *  is_virtual
 *  is_multi_shipping
 *  is_multi_payment
 *
 ** SHIPPING
 *  shipping_method
 *  shipping_description
 *  shipping_rate
 *
 ** CURRENCY ATTRIBUTES
 *  base_currency_code
 *  store_currency_code
 *  quote_currency_code
 *  store_to_base_rate
 *  store_to_quote_rate
 *
 ** CUSTOMER ATTIBUTES
 *  customer_id
 *  customer_tax_class_id
 *  customer_group_id
 *  customer_email
 *  customer_note
 *  customer_note_notify
 *
 * Supported events:
 *  sales_quote_load_after
 *  sales_quote_save_before
 *  sales_quote_save_after
 *  sales_quote_delete_before
 *  sales_quote_delete_after
 *
 */
class Mage_Sales_Model_Quote extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'sales_quote';
    protected $_eventObject = 'quote';

    /**
     * Quote store model object
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * Quote customer model object
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;

    /**
     * Quote addresses collection
     *
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_addresses;

    /**
     * Quote items collection
     *
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_items;

    /**
     * Quote payments
     *
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_payments;

    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('sales/quote');
    }

    /**
     * Retrieve quote store model object
     *
     * @return  Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            if ($storeId = $this->getStoreId()) {
                $store = Mage::app()->getStore($this->getStoreId());
                /* @var $store Mage_Core_Model_Store */
                if ($store->getId()) {
                    $this->setStore($store);
                }
            }
            if (is_null($this->_store)) {
                $this->setStore(Mage::app()->getStore());
            }
        }
        return $this->_store;
    }

    public function getSharedStoreIds()
    {
        return $this->getStore()->getWebsite()->getStoresIds();
    }

    /**
     * Declare quote store model
     *
     * @param   Mage_Core_Model_Store $store
     * @return  Mage_Sales_Model_Quote
     */
    public function setStore(Mage_Core_Model_Store $store)
    {
        $this->_store = $store;
        $this->setStoreId($store->getId());
        return $this;
    }

    /**
     * Declare quote store identifier
     *
     * @param   int $storeId
     * @return  Mage_Sales_Model_Quote
     */
    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);
        if (! is_null($this->_store) && ($this->_store->getId() != $storeId)) {
            $this->_store = null;
        }
        return $this;
    }

    /**
     * Prepare data before save
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _beforeSave()
    {
        $baseCurrencyCode  = Mage::app()->getBaseCurrencyCode();
        $storeCurrency = $this->getStore()->getBaseCurrency();
        $quoteCurrency = $this->getStore()->getCurrentCurrency();

        $this->setBaseCurrencyCode($baseCurrencyCode);
        $this->setStoreCurrencyCode($storeCurrency->getCode());
        $this->setQuoteCurrencyCode($quoteCurrency->getCode());
        $this->setStoreToBaseRate($storeCurrency->getRate($baseCurrencyCode));
        $this->setStoreToQuoteRate($storeCurrency->getRate($quoteCurrency));

        return parent::_beforeSave();
    }

    /**
     * Assign customer model object data to quote
     *
     * @param   Mage_Customer_Model_Customer $customer
     * @return  Mage_Sales_Model_Quote
     */
    public function assignCustomer(Mage_Customer_Model_Customer $customer)
    {
        if ($customer->getId()) {
            $this->setCustomer($customer);

            $defaultBillingAddress = $customer->getDefaultBillingAddress();
            if ($defaultBillingAddress && $defaultBillingAddress->getId()) {
                $billingAddress = Mage::getModel('sales/quote_address')
                    ->importCustomerAddress($defaultBillingAddress);
                $this->setBillingAddress($billingAddress);
            }

            $defaultShippingAddress= $customer->getDefaultShippingAddress();
            if ($defaultShippingAddress && $defaultShippingAddress->getId()) {
                $shippingAddress = Mage::getModel('sales/quote_address')
                    ->importCustomerAddress($defaultShippingAddress);
            }
            else {
                $shippingAddress = Mage::getModel('sales/quote_address');
            }
            $this->setShippingAddress($shippingAddress);
        }

        return $this;
    }

    /**
     * Define customer object
     *
     * @param   Mage_Customer_Model_Customer $customer
     * @return  Mage_Sales_Model_Quote
     */
    public function setCustomer(Mage_Customer_Model_Customer $customer)
    {
        $this->_customer = $customer;
        $this->setCustomerId($customer->getId());
        $this->setCustomerEmail($customer->getEmail());
        $this->setCustomerFirstname($customer->getFirstname());
        $this->setCustomerLastname($customer->getLastname());
        $this->setCustomerGroupId($customer->getGroupId());
        $this->setCustomerTaxClassId($customer->getTaxClassId());
        return $this;
    }

    /**
     * Retrieve customer model object
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (is_null($this->_customer)) {
            $this->_customer = Mage::getModel('customer/customer');
            if ($customerId = $this->getCustomerId()) {
                $this->_customer->load($customerId);
                if (!$this->_customer->getId()) {
                    $this->_customer->setCustomerId(null);
                }
            }
        }
        return $this->_customer;
    }

    /**
     * Retrieve quote address collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getAddressesCollection()
    {
        if (is_null($this->_addresses)) {
            $this->_addresses = Mage::getModel('sales/quote_address')->getCollection();

            if ($this->getId()) {
                $this->_addresses->addAttributeToSelect('*')
                    ->setQuoteFilter($this->getId())
                    ->load();
                foreach ($this->_addresses as $address) {
                    $address->setQuote($this);
                }
            }
        }
        return $this->_addresses;
    }

    /**
     * Retrieve quote address by type
     *
     * @param   string $type
     * @return  Mage_Sales_Model_Quote_Address
     */
    protected function _getAddressByType($type)
    {
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getAddressType() == $type && !$address->isDeleted()) {
                return $address;
            }
        }

        $address = Mage::getModel('sales/quote_address')->setAddressType($type);
        $this->addAddress($address);
        return $address;
    }

    /**
     * Retrieve quote billing address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getBillingAddress()
    {
        return $this->_getAddressByType('billing');
    }

    /**
     * retrieve quote shipping address
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getShippingAddress()
    {
        return $this->_getAddressByType('shipping');
    }


    /*public function toArray(array $arrAttributes = array())
    {
        $arr = parent::toArray($arrAttributes);
        $arr['addresses'] = $this->getAddressesCollection()->toArray();
        $arr['items'] = $this->getItemsCollection()->toArray();
        $arr['payments'] = $this->getPaymentsCollection()->toArray();
        return $arr;
    }*/



    public function getAllShippingAddresses()
    {
        $addresses = array();
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getAddressType()=='shipping' && !$address->isDeleted()) {
                $addresses[] = $address;
            }
        }
        return $addresses;
    }

    public function getAllAddresses()
    {
        $addresses = array();
        foreach ($this->getAddressesCollection() as $address) {
            if (!$address->isDeleted()) {
                $addresses[] = $address;
            }
        }
        return $addresses;
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

    public function getAddressByCustomerAddressId($addressId)
    {
        foreach ($this->getAddressesCollection() as $address) {
            if (!$address->isDeleted() && $address->getCustomerAddressId()==$addressId) {
                return $address;
            }
        }
        return false;
    }

    public function getShippingAddressByCustomerAddressId($addressId)
    {
        foreach ($this->getAddressesCollection() as $address) {
            if (!$address->isDeleted() && $address->getAddressType()=='shipping' && $address->getCustomerAddressId()==$addressId) {
                return $address;
            }
        }
        return false;
    }

    public function removeAddress($addressId)
    {
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getId()==$addressId) {
                $address->isDeleted(true);
                break;
            }
        }
        return $this;
    }

    public function removeAllAddresses()
    {
        foreach ($this->getAddressesCollection() as $address) {
            $address->isDeleted(true);
        }
        return $this;
    }

    public function addAddress(Mage_Sales_Model_Quote_Address $address)
    {
        $address->setQuote($this)->setParentId($this->getId());
        if (!$address->getId()) {
            $this->getAddressesCollection()->addItem($address);
        }
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Mage_Sales_Model_Quote
     */
    public function setBillingAddress(Mage_Sales_Model_Quote_Address $address)
    {
        $old = $this->getBillingAddress();

        if (!empty($old)) {
            $old->addData($address->getData());
        } else {
            $this->addAddress($address->setAddressType('billing'));
        }
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Mage_Sales_Model_Quote
     */
    public function setShippingAddress(Mage_Sales_Model_Quote_Address $address)
    {
        if ($this->getIsMultiShipping()) {
            $this->addAddress($address->setAddressType('shipping'));
        }
        else {
            $old = $this->getShippingAddress();

            if (!empty($old)) {
                $old->addData($address->getData());
            } else {
                $this->addAddress($address->setAddressType('shipping'));
            }
        }
        return $this;
    }

    public function addShippingAddress(Mage_Sales_Model_Quote_Address $address)
    {
        $this->setShippingAddress($address);
        return $this;
    }

    /*********************** ITEMS ***************************/
    /**
     * Retrieve quote items collection
     *
     * @param   bool $loaded
     * @return  Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getItemsCollection($loaded = true)
    {
        if (is_null($this->_items)) {
            $this->_items = Mage::getResourceModel('sales/quote_item_collection');

            if ($this->getId()) {
                $this->_items->addAttributeToSelect('*')
                    ->setQuoteFilter($this->getId());
                if ($loaded) {
                    $this->_items->load();
                }
                foreach ($this->_items as $item) {
                    $item->setQuote($this);
                }
            }
            else {
                $this->_items->setQuoteFilter(false);
            }
        }
        return $this->_items;
    }

    /**
     * Retrieve quote items array
     *
     * @return array
     */
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

    /**
     * Checking items availability
     *
     * @return bool
     */
    public function hasItems()
    {
        return sizeof($this->getAllItems())>0;
    }

    public function isAllowedGuestCheckout()
    {
        return Mage::getStoreConfig('sales/guest_checkout/enabled');
    }

    /**
     * Checking availability of items with decimal qty
     *
     * @return bool
     */
    public function hasItemsWithDecimalQty()
    {
        foreach ($this->getAllItems() as $item) {
            if ($item->getProduct()->getQtyIsDecimal()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve item model object by item identifier
     *
     * @param   int $itemId
     * @return  Mage_Sales_Model_Quote_Item
     */
    public function getItemById($itemId)
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getId()==$itemId) {
                return $item;
            }
        }
        return false;
    }

    /**
     * Remove quote item by item identifier
     *
     * @param   int $itemId
     * @return  Mage_Sales_Model_Quote
     */
    public function removeItem($itemId)
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getId()==$itemId) {
                $item->isDeleted(true);
                break;
            }
        }
        return $this;
    }

    /**
     * Adding new item to quote
     *
     * @param   Mage_Sales_Model_Quote_Item $item
     * @return  Mage_Sales_Model_Quote
     */
    public function addItem(Mage_Sales_Model_Quote_Item $item)
    {
        $item->setQuote($this)
            ->setParentId($this->getId());
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }

    /**
     * Adding product to quote
     *
     * @param   mixed $product
     * @return  Mage_Sales_Model_Quote
     */
    public function addProduct($product, $qty=1)
    {
        if (is_int($product)) {
            $product = Mage::getModel('catalog/product')
                ->setStore($this->getStore())
                ->load($product);
        }

        if ($product instanceof Mage_Catalog_Model_Product) {
            $this->addCatalogProduct($product, $qty);
        }

        return $this;
    }

    /**
     * Adding catalog product object data to quote
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_Sales_Model_Quote_Item
     */
    public function addCatalogProduct(Mage_Catalog_Model_Product $product, $qty=1)
    {
        $item = $this->getItemByProduct($product);
        if (!$item) {
            $item = Mage::getModel('sales/quote_item');
        }
        /* @var $item Mage_Sales_Model_Quote_Item */

        $item->importCatalogProduct($product)
            ->addQty($qty);

        $this->addItem($item);

        return $item;
    }

    /**
     * Retrieve quote item by product id
     *
     * @param   int $productId
     * @return  Mage_Sales_Model_Quote_Item || false
     */
    public function getItemByProduct(Mage_Catalog_Model_Product $product)
    {
        foreach ($this->getAllItems() as $item) {
            if ($item->getSuperProductId()) {
                if ($product->getSuperProduct() && $item->getSuperProductId() == $product->getSuperProduct()->getId()) {
                    if ($item->getProductId() == $product->getId()) {
                        return $item;
                    }
                }
            }
            else {
                if ($item->getProductId() == $product->getId()) {
                    return $item;
                }
            }
        }
        return false;
    }

    public function getItemsSummaryQty()
    {
        $qty = $this->getData('all_items_qty');
        if (is_null($qty)) {
#            $qty = Mage::getResourceModel('sales_entity/quote')->fetchItemsSummaryQty($this);
            $qty = 0;
            foreach ($this->getAllItems() as $item) {
                $qty+= $item->getQty();
            }
            $this->setData('all_items_qty', $qty);
        }
        return $qty;
    }

    /*********************** PAYMENTS ***************************/
    public function getPaymentsCollection()
    {
        if (is_null($this->_payments)) {
            $this->_payments = Mage::getResourceModel('sales/quote_payment_collection');

            if ($this->getId()) {
                $this->_payments
                    ->addAttributeToSelect('*')
                    ->setQuoteFilter($this->getId())
                    ->load();
                foreach ($this->_payments as $payment) {
                    $payment->setQuote($this);
                }
            }
        }
        return $this->_payments;
    }

    public function getPayment()
    {
        foreach ($this->getPaymentsCollection() as $payment) {
            if (!$payment->isDeleted()) {
                return $payment;
            }
        }
        $payment = Mage::getModel('sales/quote_payment');
        $this->addPayment($payment);
        return $payment;
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

    public function addPayment(Mage_Sales_Model_Quote_Payment $payment)
    {
        $payment->setQuote($this)->setParentId($this->getId());
        if (!$payment->getId()) {
            $this->getPaymentsCollection()->addItem($payment);
        }
        return $this;
    }

    public function setPayment(Mage_Sales_Model_Quote_Payment $payment)
    {
        if (!$this->getIsMultiPayment() && ($old = $this->getPayment())) {
            $payment->setId($old->getId());
        }
        $this->addPayment($payment);

        return $payment;
    }

    public function removePayment()
    {
        $this->getPayment()->isDeleted(true);
        return $this;
    }

    /*********************** TOTALS ***************************/
    public function collectTotals()
    {
        $this->setGrandTotal(0);
        foreach ($this->getAllShippingAddresses() as $address) {
            $address->setGrandTotal(0);
            $address->collectTotals();
            $this->setGrandTotal((float) $this->getGrandTotal()+$address->getGrandTotal());
        }
        return $this;
    }

    public function getTotals()
    {
        return $this->getShippingAddress()->getTotals();
    }

    /*********************** ORDER ***************************/
    public function createOrder()
    {
        if ($this->getIsVirtual()) {
            $this->getBillingAddress()->createOrder();
        } elseif (!$this->getIsMultiShipping()) {
            $this->getShippingAddress()->createOrder();
        } else {
            foreach ($this->getAllShippingAddresses() as $address) {
                $address->createOrder();
            }
        }
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Quote
     */
    public function createFromOrder(Mage_Sales_Model_Order $order)
    {
        $this->setStoreId($order->getStoreId());
        $this->setBillingAddress(Mage::getModel('sales/quote_address')->importOrderAddress($order->getBillingAddress()));
        $this->setShippingAddress(Mage::getModel('sales/quote_address')->importOrderAddress($order->getShippingAddress()));
        foreach ($order->getItemsCollection() as $item) {
            if ($item->getQtyToShip() > 0) {
                $this->addItem(Mage::getModel('sales/quote_item')->importOrderItem($item));
            }
        }
        $this->getShippingAddress()->setCollectShippingRates(true);
        $this->getShippingAddress()->setShippingMethod($order->getShippingMethod());
        $this->getPayment()->importOrderPayment($order->getPayment());
        $this->setCouponCode($order->getCouponeCode());
        return $this;
    }

    /**
     * Loading quote data by customer
     *
     * @return mixed
     */
    public function loadByCustomer($customer)
    {
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $customerId = $customer->getId();
        }
        else {
            $customerId = (int) $customer;
        }
        $this->_getResource()->loadByCustomerId($this, $customerId);
        return $this;
    }

    public function addMessage($message, $index='error')
    {
        $messages = $this->getData('messages');
        if (is_null($messages)) {
            $messages = array();
        }

        if (isset($messages[$index])) {
            return $this;
        }

        if (is_string($message)) {
            $message = Mage::getSingleton('core/message')->error($message);
        }

        $messages[$index] = $message;
        $this->setData('messages', $messages);
        return $this;
    }

    public function getMessages()
    {
        $messages = $this->getData('messages');
        if (is_null($messages)) {
            $messages = array();
            $this->setData('messages', $messages);
        }
        return $messages;
    }

    /*********************** QUOTE ***************************/
    protected function _beforeDelete()
    {
        parent::_beforeDelete();

        $this->getAddressesCollection()->walk('delete');
        $this->getItemsCollection()->walk('delete');
        $this->getPaymentsCollection()->walk('delete');
    }
}