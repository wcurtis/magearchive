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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Sales_Model_Invoice_Shipment extends Mage_Core_Model_Abstract
{

    // TOFIX - what statuses should we have ?
    const STATUS_SENT = 1;
    const STATUS_SHIPPED = 2;
    const STATUS_RETURNED = 3;

    protected static $_statuses = null;

    protected $_invoice;

    function _construct()
    {
        $this->_init('sales/invoice_shipment');
    }

    public function setInvoice(Mage_Sales_Model_Invoice $invoice)
    {
        $this->_invoice = $invoice;
        return $this;
    }

    public function getInvoice()
    {
        return $this->_invoice;
    }


    public static function getStatuses()
    {
        if (is_null(self::$_statuses)) {
            self::$_statuses = array(
                // TOFIX - what statuses should we have ?
                self::STATUS_SENT => Mage::helper('sales')->__('Sent'),
                self::STATUS_SHIPPED => Mage::helper('sales')->__('Shipped'),
                self::STATUS_RETURNED => Mage::helper('sales')->__('Returned'),
            );
        }
        return self::$_statuses;
    }

    public static function getStatusName($statusId)
    {
        if (is_null(self::$_statuses)) {
            self::getStatuses();
        }
        if (isset(self::$_statuses[$statusId])) {
            return self::$_statuses[$statusId];
        }
        return Mage::helper('sales')->__('Unknown Status');
    }

}
