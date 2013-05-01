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
 * Adminhtml invoice create
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sales_Invoice_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'sales_invoice';
        $this->_mode = 'create';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('sales')->__('Submit Invoice'));
        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
        return Mage::helper('sales')->__('New Invoice for Order #%s', Mage::registry('sales_invoice')->getOrder()->getRealOrderId());
    }

    public function getBackUrl()
    {
        if (Mage_Sales_Model_Invoice::TYPE_INVOICE == Mage::registry('sales_invoice')->getInvoiceType()) {
            return Mage::getUrl('*/sales_order/view', array('order_id' => $this->getRequest()->getParam('order_id')));
        }
        return Mage::getUrl('*/sales_order/cmemo', array('invoice_id' => $this->getRequest()->getParam('invoice_id')));
    }

}
