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
 * Order history tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Sales_Order_View_Tab_History extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sales/order/view/tab/history.phtml');
    }

    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Retrive full order history
     *
     */
    public function getFullHistory(){
        $order = $this->getOrder();

        $_fullHistory = array();
        foreach ($order->getAllStatusHistory() as $_history){
            $_fullHistory[$_history->getEntityId()] = $_history;
        }

        foreach ($order->getCreditmemosCollection() as $_memo){
            $_fullHistory[$_memo->getEntityId()] = $_memo;
        }

        foreach ($order->getShipmentsCollection() as $_shipment){
            $_fullHistory[$_shipment->getEntityId()] = $_shipment;
        }

        foreach ($order->getInvoiceCollection() as $_invoice){
            $_fullHistory[$_invoice->getEntityId()] = $_invoice;
        }

        foreach ($order->getTracksCollection() as $_track){
            $_fullHistory[$_track->getEntityId()] = $_track;
        }

        ksort($_fullHistory);
        return $_fullHistory;
    }
}