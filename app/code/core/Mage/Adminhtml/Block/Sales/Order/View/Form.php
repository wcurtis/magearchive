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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales order view plane
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sales_Order_View_Form extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_plane');
        $this->setTemplate('sales/order/view/form.phtml');
        $this->setTitle(Mage::helper('sales')->__('Order Information'));
    }

    public function getOrder()
    {
        return Mage::registry('sales_order');
    }

    protected function _prepareLayout()
    {
        $paymentInfoBlock = Mage::helper('payment')->getInfoBlock($this->getOrder()->getPayment());
        if ($this->getOrder()->getPayment()->getMethod() == 'ccsave') {
            $paymentInfoBlock->setTemplate('payment/info/ccsave.phtml');
        }

        $this->setChild(
            'messages',
            $this->getLayout()->createBlock('adminhtml/sales_order_view_messages')
        );

        $this->setChild(
            'items',
            $this->getLayout()->createBlock('adminhtml/sales_order_view_items')
        );

        $this->setChild('payment_info', $paymentInfoBlock);

        $this->setChild(
            'history',
            $this->getLayout()->createBlock('adminhtml/sales_order_view_history')
        );

        $this->setChild(
            'giftmessage',
            $this->getLayout()->createBlock('adminhtml/sales_order_view_giftmessage')
                ->setEntity($this->getOrder())
        );

        $this->setChild(
            'tracking',
            $this->getLayout()->createBlock('adminhtml/sales_order_view_tracking')
        );

        return parent::_prepareLayout();
    }

    public function getTrackingHtml()
    {
        return $this->getChildHtml('tracking');
    }

    public function getItemsHtml()
    {
        return $this->getChildHtml('items');
    }

    /**
     * Retrive giftmessage block html
     *
     * @return string
     */
    public function getGiftmessageHtml()
    {
        return $this->getChildHtml('giftmessage');
    }

    public function getOrderStoreName()
    {
        return Mage::getModel('core/store')->load($this->getOrder()->getStoreId())->getName();
    }

    public function getCustomerGroupName()
    {
        return Mage::getModel('customer/group')->load($this->getOrder()->getCustomerGroupId())->getCode();
    }

    public function getPaymentHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    public function getViewUrl($orderId)
    {
        return $this->getUrl('*/*/*', array('order_id'=>$orderId));
    }
}
