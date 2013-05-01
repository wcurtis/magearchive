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
 * Adminhtml sales orders grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('real_order_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'align' => 'center',
            'index' => 'increment_id',
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
            'type' => 'datetime',
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
            //'rate_field' => 'store_to_order_rate'
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('customer')->__('Action'),
                'width'     => '60px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('customer')->__('View'),
                        'url'     => array('base'=>'*/*/view'),
                        'field'   => 'order_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');

        $this->getMassactionBlock()->addItem('cancel_order', array(
             'label'=> Mage::helper('sales')->__('Cancel'),
             'url'  => $this->getUrl('*/*/massCancel'),
        ));

        $this->getMassactionBlock()->addItem('hold_order', array(
             'label'=> Mage::helper('sales')->__('Hold'),
             'url'  => $this->getUrl('*/*/massHold'),
        ));

        $this->getMassactionBlock()->addItem('unhold_order', array(
             'label'=> Mage::helper('sales')->__('Unhold'),
             'url'  => $this->getUrl('*/*/massUnhold'),
        ));

//        $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
//        array_unshift($statuses, array('value'=>'', 'label'=>''));
//        $this->getMassactionBlock()->addItem('change_status', array(
//             'label'=> Mage::helper('sales')->__('Change Status'),
//             'url'  => $this->getUrl('*/*/massStatus'),
//             'additional' => array(
//                    'visibility' => array(
//                             'name' => 'status',
//                             'type' => 'select',
//                             'class' => 'required-entry',
//                             'label' => Mage::helper('sales')->__('New Status'),
//                             'values' => $statuses
//                         )
//             )
//        ));

//        $prints = array(
//            'empty'     => array('value'=>'', 'label'=>''),
//            'order'    => Mage::helper('sales')->__('Orders'),
//            'invoice'  => Mage::helper('sales')->__('Invoices'),
//            'shipment' => Mage::helper('sales')->__('Shipments'),
//        );
//        $this->getMassactionBlock()->addItem('print', array(
//             'label'=> Mage::helper('sales')->__('Print'),
//             'url'  => $this->getUrl('*/*/massPrint'),
//             'additional' => array(
//                    'visibility' => array(
//                             'name' => 'document',
//                             'type' => 'select',
//                             'class' => 'required-entry',
//                             'label' => Mage::helper('sales')->__('Document'),
//                             'values' => $prints
//                         )
//             )
//        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('order_id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}
