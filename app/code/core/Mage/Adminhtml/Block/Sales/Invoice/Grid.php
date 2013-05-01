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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales invoices grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sales_Invoice_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_invoice_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/invoice_collection')
            ->addAttributeToSelect('*')
             ->joinAttribute('billing_firstname', 'invoice_address/firstname', 'billing_address_id')
             ->joinAttribute('billing_lastname', 'invoice_address/lastname', 'billing_address_id')
             ->joinAttribute('shipping_firstname', 'invoice_address/firstname', 'shipping_address_id')
             ->joinAttribute('shipping_lastname', 'invoice_address/lastname', 'shipping_address_id')
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $types = Mage_Sales_Model_Invoice::getTypes();

        $this->addColumn('type', array(
            'header' => Mage::helper('sales')->__('Type'),
            'align' => 'center',
            'index' => 'invoice_type',
            'type' => 'options',
            'options' => $types,
        ));

        $this->addColumn('increment_id', array(
            'header' => Mage::helper('sales')->__('Doc Number'),
            'align' => 'center',
            'index' => 'increment_id',
        ));

        $this->addColumn('order_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'align' => 'center',
            'index' => 'real_order_id',
        ));

        $stores = Mage::getResourceModel('core/store_collection')->setWithoutDefaultFilter()->load()->toOptionHash();

        $this->addColumn('store_id', array(
            'header' => Mage::helper('sales')->__('Purchased from (store)'),
            'index' => 'store_id',
            'type' => 'options',
            'options' => $stores,
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type'      => 'date',
        ));

        $this->addColumn('billing_firstname', array(
            'header' => Mage::helper('sales')->__('Bill to First name'),
            'index' => 'billing_firstname',
        ));

        $this->addColumn('billing_lastname', array(
            'header' => Mage::helper('sales')->__('Bill to Last name'),
            'index' => 'billing_lastname',
        ));

        $this->addColumn('shipping_firstname', array(
            'header' => Mage::helper('sales')->__('Ship to First name'),
            'index' => 'shipping_firstname',
        ));

        $this->addColumn('shipping_lastname', array(
            'header' => Mage::helper('sales')->__('Ship to Last name'),
            'index' => 'shipping_lastname',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('Grand Total'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $statuses = Mage_Sales_Model_Invoice::getStatuses();

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'invoice_status_id',
            'type'  => 'options',
            'options' => $statuses,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return Mage::getUrl('*/*/view', array('invoice_id' => $row->getId()));
    }

}