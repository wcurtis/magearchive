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
 * Adminhtml sales invoice view plane
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sales_Invoice_View_Plane extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('invoice_plane');
        $this->setTemplate('sales/invoice/view/plane.phtml');
        $this->setTitle(Mage::helper('sales')->__('Invoice Information'));
        $model = Mage::registry('sales_entity');
        if ($model instanceof Mage_Sales_Model_Invoice) {
           Mage::register('sales_order', Mage::getModel('sales/order')->load($model->getOrderId()));
        }
    }

    public function getOrder()
    {
        $model = Mage::registry('sales_entity');
        if ($model instanceof Mage_Sales_Model_Invoice) {
            return Mage::getModel('sales/order')->load($model->getOrderId());
        }
        return $model;
    }

    protected function _prepareLayout()
    {
        $this->setChild( 'items', $this->getLayout()->createBlock( 'adminhtml/sales_order_view_items', 'items.grid' ));
        return parent::_prepareLayout();
    }

    public function getItemsHtml()
    {
        return $this->getChildHtml('items');
    }

    public function getOrderDateFormatted($format='short')
    {
        return $this->formatDate($this->getOrder()->getCreatedAt(), $format);
    }

}
