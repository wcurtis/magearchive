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
 * Adminhtml sales orders controller
 *
 */
class Mage_Adminhtml_Sales_ShipmentController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Additional initialization
     *
     */
    protected function _construct()
    {
        $this->setUsedModuleName('Mage_Sales');
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return Mage_Adminhtml_Sales_ShipmentController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Shipments'),$this->__('Shipments'));
        return $this;
    }

    /**
     * Shipments grid
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('adminhtml/sales_shipment'))
            ->renderLayout();
    }

    /**
     * Shipment information page
     */
    public function viewAction()
    {
        if ($shipmentId = $this->getRequest()->getParam('shipment_id')) {
            if ($shipment = Mage::getModel('sales/order_shipment')->load($shipmentId)) {
                Mage::register('current_shipment', $shipment);
                $this->_initAction()
                    ->_addContent($this->getLayout()->createBlock('adminhtml/sales_order_shipment_view'))
                    ->renderLayout();
            }
        } else {
            $this->_forward('noRoute');
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/shipment');
    }

}
