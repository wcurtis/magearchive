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

class Mage_Adminhtml_Block_Sales_Cmemo_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'invoice_id';
        $this->_controller = 'sales_cmemo';
        $this->_mode = 'create';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('sales')->__('Submit Credit Memo'));
        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
        return Mage::helper('sales')->__('New Credit Memo for Invoice #') . Mage::registry('sales_invoice')->getIncrementId();
    }

    public function getBackUrl()
    {
        return Mage::getUrl('*/sales_order/invoice', array('invoice_id' => $this->getRequest()->getParam('invoice_id')));
    }

    public function getSaveUrl()
    {
        return Mage::getUrl('*/sales_invoice/savecmnew', array('invoice_id' => $this->getRequest()->getParam('invoice_id')));
    }

    public function getInvoice()
    {
        return Mage::registry('sales_invoice');
    }

    public function getInvoiceDateFormatted()
    {
        return $this->getInvoice()->getCreatedAt();
        return $date;
    }

}
