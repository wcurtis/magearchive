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
 * Adminhtml order items grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Sales_Order_View_Items extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('order_items_grid');
        $this->setDefaultSort('entity_id', 'asc');
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    /**
     * Retrieve order model object
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        return Mage::registry('sales_order');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/order_item_collection')
            ->addAttributeToSelect('*')
            ->setOrderFilter($this->_getOrder()->getId());
        $collection->getEntity()->setStore($this->_getOrder()->getStoreId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $currencyCode = $this->_getOrder()->getOrderCurrencyCode();
        /*$this->addColumn('product_id', array(
            'header'=> Mage::helper('sales')->__('Product ID'),
            'align' => 'center',
            'index' => 'product_id',
            'width' => '70px',
        ));*/

        $this->addColumn('sku', array(
            'header'=> Mage::helper('sales')->__('SKU'),
            'index' => 'sku',
            'width' => '100px'
        ));

        $this->addColumn('name', array(
            'header'=> Mage::helper('sales')->__('Product Name'),
            'index' => 'name',
            'renderer' => 'adminhtml/sales_order_view_items_grid_renderer_name',
            'column_css_class' => 'giftmessage-single-item'
        ));

        $this->addColumn('price', array(
            'header'=> Mage::helper('sales')->__('Price'),
            'index' => 'price',
            'width' => '120px',
            'type'  => 'currency',
            'currency_code' => $currencyCode,
        ));

        $this->addColumn('qty_ordered', array(
            'header'=> Mage::helper('sales')->__('Qty Ordered'),
            'index' => 'qty_ordered',
            'type'  => 'number',
            'width' => '85px',
        ));

        $this->addColumn('qty_canceled', array(
            'header'=> Mage::helper('sales')->__('Qty Cancelled'),
            'index' => 'qty_canceled',
            'type'  => 'number',
            'width' => '85px',
            'default'=> '-',
        ));

        $this->addColumn('status', array(
            'header'=> Mage::helper('sales')->__('Item Status'),
            'getter'=> 'getStatus',
            'width' => '85px',
        ));

        $this->addColumn('discount_amount', array(
            'header'=> Mage::helper('sales')->__('Discount'),
            'index' => 'discount_amount',
            'default'=> '-',
            'width' => '85px',
            'type'  => 'currency',
            'currency_code' => $currencyCode,
        ));

        $this->addColumn('tax_amount', array(
            'header' => Mage::helper('sales')->__('Tax Amount'),
            'index' => 'tax_amount',
            'default'=> '-',
            'width' => '100px',
            'type'  => 'currency',
            'currency_code' => $currencyCode,
        ));

        $this->addColumn('row_total', array(
            'header' => Mage::helper('sales')->__('Subtotal'),
            'index' => 'row_total',
            'type'  => 'currency',
            'currency_code' => Mage::registry('sales_order')->getOrderCurrencyCode(),
            'width' => '125px',
        ));


        return parent::_prepareColumns();
    }

}
