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
 * Adminhtml invoice view form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sales_Invoice_View_Form extends Mage_Adminhtml_Block_Widget_Form
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('invoice_form');
        $this->setTitle(Mage::helper('sales')->__('Invoice Information'));
        $this->setTemplate('sales/invoice/view.phtml');
    }

    public function getInvoice()
    {
        return Mage::registry('sales_invoice');
    }

    protected function _prepareLayout()
    {
        $this->setChild('items', $this->getLayout()->createBlock( 'adminhtml/sales_invoice_view_items', 'sales_invoice_view_items'));
        return parent::_prepareLayout();
    }

    public function getItemsHtml()
    {
        return $this->getChildHtml('items');
    }

    public function getInvoiceDateFormatted($format='short')
    {
        return $this->formatDate($this->getInvoice()->getCreatedAt(), $format);
    }

}
