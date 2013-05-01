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
 * @category    Mage
 * @package     Mage_Adminhtml
 */
class Mage_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Controller_Action
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
     * @return Mage_Adminhtml_Sales_OrderController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Orders'),$this->_getHelper()-> __('Orders'));
        return $this;
    }

    /**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        return $order;
    }

    /**
     * Orders grid
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('adminhtml/sales_order'))
            ->renderLayout();
    }

    /**
     * View order detale
     */
    public function viewAction()
    {
        if ($order = $this->_initOrder()) {
            $this->_initAction()
                ->_addBreadcrumb($this->__('View Order'), $this->__('View Order'))
                ->_addContent($this->getLayout()->createBlock('adminhtml/sales_order_view'))
                ->renderLayout();
        }
    }

    /**
     * Cancel order
     */
    public function cancelAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $order->cancel()
                    ->save();
                $this->_getSession()->addSuccess(
                    $this->__('Order was successfully cancelled')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('Order was not cancelled'));
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    /**
     * Add order comment action
     */
    public function addCommentAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $data = $this->getRequest()->getPost('history');
                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $order->addStatusToHistory($data['status'], $data['comment'], $notify);
                $comment = trim(strip_tags($data['comment']));

                if ($notify && $comment) {
                    Mage::getDesign()->setStore($order->getStoreId());
                    Mage::getDesign()->setArea('frontend');
                    $order->sendOrderUpdateEmail($comment);
                }
                $order->save();
            }
            catch (Exception $e) {

            }
            Mage::getDesign()->setArea('adminhtml');
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/sales_order_view_history')->toHtml()
            );
        }
    }

    /**
     * Add tracking number
     */
    public function addTrackingAction()
    {
        if ($order = $this->_initOrder()) {
            if ($number = $this->getRequest()->getPost('tracking_number')) {
                $order->addTrackingNumber($number);
                $order->save();
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/sales_order_view_tracking')->toHtml()
            );
        }
    }

    /**
     * Remove tracking number
     */
    public function removeTrackingAction()
    {
        if ($order = $this->_initOrder()) {
            if ($number = $this->getRequest()->getParam('tracking_number')) {
                $order->removeTrackingNumber($number);
                $order->save();
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/sales_order_view_tracking')->toHtml()
            );
        }
    }

    public function viewTrackingAction()
    {
        if ($order = $this->_initOrder()) {
            $number = $this->getRequest()->getParam('tracking_number');
            if ($carrier = $order->getShippingCarrier()) {
            	$carrier->getTracking(array($number));
            	$this->getResponse()->setBody($carrier->getResponse().'<br />');
            }
        }

    }

    /**
     * Edit order status
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $model = Mage::getModel('sales/order')->load($id);

        if ($model->getId()) {
            Mage::register('sales_order', $model);

            $this->_initAction()
                ->_addBreadcrumb(__('Edit Order'), __('Edit Order'))
                ->_addContent($this->getLayout()->createBlock('adminhtml/sales_order_edit'))
                ->renderLayout();
        }
        else {
            $this->_getSession()->addError($this->__('This order no longer exists'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Save order
     */
    public function saveAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);

        if ($order->getId()) {
            if ($newStatus = $this->getRequest()->getParam('new_status')) {
                $notifyCustomer = $this->getRequest()->getParam('notify_customer', false);
                $comment = $this->getRequest()->getParam('comments', '');

                $order->addStatus($newStatus, $comment, $notifyCustomer);

                try {
                    $order->save();
                    if ($notifyCustomer) {
                        $order->sendOrderUpdateEmail($comment);
                    }
                    $this->_getSession()->addSuccess($this->__('Order status was successfully changed'));
                }
                catch (Mage_Core_Exception $e){
                    $this->_getSession()->addError($e->getMessage());
                }
                catch (Exception $e) {
                    $this->_getSession()->addError($this->__('Order was not changed'));
                }
            }

            $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError(__('This order no longer exists'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Random orders generation
     */
    /*public function generateAction()
    {
        $count = (int) $this->getRequest()->getParam('count', 10);
        if ($count && $count>100) {
            $count = 100;
        }

        for ($i=0; $i<$count; $i++){
            $randomOrder = Mage::getModel('adminhtml/sales_order_random')
                ->render()
                ->save();
        }
    }*/

    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('sales/order');
    }
}
