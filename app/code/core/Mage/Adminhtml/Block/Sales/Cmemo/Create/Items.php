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
 * Adminhtml invoice items grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sales_Cmemo_Create_Items extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('invoice_items_grid');
        $this->setTemplate('sales/cmemo/create/items.phtml');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sales/invoice_item_collection')
            ->addAttributeToSelect('*')
            ->setInvoiceFilter(Mage::registry('sales_invoice')->getInvoice()->getId())
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getInvoice()
    {
        return Mage::registry('sales_invoice');
    }

    public function getItemData($invoiceItemId)
    {
        $data = $this->getInvoice()->getData();
        if (isset($data['items']) && isset($data['items'][$invoiceItemId])) {
            return $data['items'][$invoiceItemId];
        }
        return null;
   }

}
