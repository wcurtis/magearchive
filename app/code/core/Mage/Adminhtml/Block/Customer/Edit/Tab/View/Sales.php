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
 * Adminhtml customer view wishlist block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_View_Sales extends Mage_Adminhtml_Block_Template
{

    /**
     * Enter description here...
     *
     * @var Mage_Sales_Model_Entity_Sale_Collection
     */
    protected $_collection;

    /**
     * Enter description here...
     *
     * @var Mage_Directory_Model_Currency
     */
    protected $_currency;

    public function __construct()
    {
        parent::__construct();
        $this->setId('customer_view_sales_grid');
        $this->setTemplate('customer/tab/view/sales.phtml');
    }

    public function _beforeToHtml()
    {
        $this->_currency = Mage::getModel('directory/currency')
            ->load(Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE))
        ;

        $this->_collection = Mage::getResourceModel('sales/sale_collection')
            ->setCustomerFilter(Mage::registry('current_customer'))
            ->load()
        ;

        return parent::_beforeToHtml();
    }

    public function getRows()
    {
        return $this->_collection->getItems();
    }

    public function getTotals()
    {
        return $this->_collection->getTotals();
    }

    public function getPriceFormatted($price)
    {
        return $this->_currency->format($price);
    }

}
