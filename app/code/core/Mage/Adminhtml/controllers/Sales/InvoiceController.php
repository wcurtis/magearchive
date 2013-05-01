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
 * Adminhtml sales invoices controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Sales_InvoiceController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Additional initialization
     *
     */
    protected function _construct()
    {
        $this->setUsedModuleName('Mage_Sales');
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/invoice')
            ->_addBreadcrumb(__('Sales'), __('Sales'))
            ->_addBreadcrumb(__('Invoices'), __('Invoices'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('adminhtml/sales_invoice'))
            ->renderLayout();
    }

    public function newAction()
    {
        if ($orderId = $this->getRequest()->getParam('order_id')) {
            $order = Mage::getModel('sales/order');

            $order->load($orderId);
            if (! $order->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(__('This order no longer exists'));
                $this->_redirect('*/*/');
                return;
            }

            $model = Mage::getModel('sales/invoice');
            $model->createFromOrder($order);

            // set entered data if was error when we do save
            $data = Mage::getSingleton('adminhtml/session')->getInvoiceData(true);
            if (! empty($data)) {
                $model->setData($data);
            }

            Mage::register('sales_invoice', $model);

            $this->_initAction()
                ->_addBreadcrumb(('Create New Invoice'), __('Create New Invoice'))
                ->_addContent($this->getLayout()->createBlock('adminhtml/sales_invoice_create'))
                ->renderLayout();
        } else {
            $this->_redirect('*/sales_order/');
        }
    }

    public function cmemoAction()
    {
        if ($invoiceId = $this->getRequest()->getParam('invoice_id')) {
            $invoice = Mage::getModel('sales/invoice');
            /* @var $invoice Mage_Sales_Model_Invoice */

            if ($invoiceId) {
                $invoice->load($invoiceId);
                if (! $invoice->getId()) {
                    Mage::getSingleton('adminhtml/session')->addError(__('This invoice no longer exists'));
                    $this->_redirect('*/*/');
                    return;
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(__('This invoice no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
            Mage::register('sales_invoice', $invoice);

            $model = Mage::getModel('sales/invoice');
            /* @var $model Mage_Sales_Model_Invoice */
            $model->createFromInvoice($invoice);

            // set entered data if was error when we do save
            $data = Mage::getSingleton('adminhtml/session')->getInvoiceData(true);
            if (! empty($data)) {
                $model->setData($data);
            }

            $this->_initAction()
                ->_addBreadcrumb(('Create New Credit Memo'), __('Create New Credit Memo'))
                ->_addContent($this->getLayout()->createBlock('adminhtml/sales_cmemo_create'))
                ->renderLayout();
        } else {
            $this->_redirect('*/sales_invoice/');
        }
    }


    public function savenewAction()
    {
        if (($orderId = $this->getRequest()->getParam('order_id')) && ($data = $this->getRequest()->getPost())) {

            $invoice = Mage::getModel('sales/invoice');
            $invoice->setData($data);

            $order = Mage::getModel('sales/order')->load($orderId);
            if (! $order->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(__('The order you are trying to create invoice for no longer exists'));
                Mage::getSingleton('adminhtml/session')->setInvoiceData($data);
                $this->_redirect('*/sales_invoice/new/', array('order_id' => $orderId));
                return;
            }
            $invoice->createFromOrder($order);

            try {
                $invoice->setData('items', $data['items']);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(__('Invoice was not saved'));
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setInvoiceData($data);
                $this->_redirect('*/sales_invoice/new/', array('order_id' => $orderId));
                return;
            }

            try {
                $invoice->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(__('Invoice was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setInvoiceData(false);
                if (Mage_Sales_Model_Invoice::STATUS_OPEN == $invoice->getInvoiceStatusId()) {
                    try {
                        $invoice->processPayment();
                        // TODO - redirect to print form
                        Mage::getSingleton('adminhtml/session')->addSuccess(__('Invoice was successfully charged'));
                        $this->_redirect('*/sales_invoice/edit/', array('invoice_id' => $invoice->getId()));
                        return;
                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError(__('Invoice was not charged'));
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        $this->_redirect('*/sales_invoice/edit/', array('invoice_id' => $invoice->getId()));
                        return;
                    }
                }
                // TODO - redirect to print form
                $this->_redirect('*/sales_invoice/edit/', array('invoice_id' => $invoice->getId()));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(__('Invoice was not saved'));
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setInvoiceData($data);
                $this->_redirect('*/sales_invoice/new/', array('order_id' => $orderId));
                return;
            }
        } else {
            $this->_redirect('*/sales_order/');
        }
    }

    public function savecmnewAction()
    {
        if (($invoiceId = $this->getRequest()->getParam('invoice_id')) && ($data = $this->getRequest()->getPost())) {

            $cmemo = Mage::getModel('sales/invoice');
            /* @var $cmemo Mage_Sales_Model_Invoice */
            $cmemo->setData($data);

            $invoice = Mage::getModel('sales/invoice')->load($invoiceId);
            if (! $invoice->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(__('The invoice you are trying to create credit memo for no longer exists'));
                Mage::getSingleton('adminhtml/session')->setInvoiceData($data);
                $this->_redirect('*/sales_invoice/cmemo/', array('invoice_id' => $invoiceId));
                return;
            }

            $cmemo->createFromInvoice($invoice);

            try {
                $cmemo->setData('items', $data['items']);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(__('Credit Memo was not saved'));
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setInvoiceData($data);
                $this->_redirect('*/sales_invoice/cmemo/', array('invoice_id' => $invoice->getId()));
                return;
            }

            try {
                $cmemo->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(__('Credit Memo was saved succesfully'));
                Mage::getSingleton('adminhtml/session')->setInvoiceData(false);
                // TODO - redirect to print form
                $this->_redirect('*/sales_invoice/edit/', array('invoice_id' => $cmemo->getId()));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(__('Credit Memo was not saved'));
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setInvoiceData($data);
                $this->_redirect('*/sales_invoice/cmemo/', array('invoice_id' => $invoice->getId()));
                return;
            }
        } else {
            $this->_redirect('*/sales_invoice/');
        }
    }

    public function saveAction()
    {
        if (($invoiceId = $this->getRequest()->getParam('invoice_id')) && ($data = $this->getRequest()->getPost())) {
            $invoice = Mage::getModel('sales/invoice');
            $invoice->setData($data);
            try {
                $invoice->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(__('Invoice was updated succesfully'));
                Mage::getSingleton('adminhtml/session')->setInvoiceData(false);
                if (Mage_Sales_Model_Invoice::STATUS_OPEN == $invoice->getInvoiceStatusId()) {
                    try {
                        // TODO
//                        $invoice->charge();
                        // TODO - redirect to print form
                        Mage::getSingleton('adminhtml/session')->addSuccess(__('Invoice was charged succesfully'));
                        $this->_redirect('*/sales_invoice/view/', array('invoice_id' => $invoiceId));
                        return;
                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError(__('Invoice was not charged'));
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        $this->_redirect('*/sales_invoice/edit/', array('invoice_id' => $invoiceId));
                        return;
                    }
                }
                // TODO - redirect to print form
                $this->_redirect('*/sales_invoice/edit/', array('invoice_id' => $invoiceId));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(__('Invoice was not saved'));
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setInvoiceData($data);
                $this->_redirect('*/sales_invoice/edit/', array('invoice_id' => $invoiceId));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function editAction()
    {
        if ($id = $this->getRequest()->getParam('invoice_id')) {
            $model = Mage::getModel('sales/invoice');

            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(__('This invoice no longer exists'));
                $this->_redirect('*/*/');
                return;
            }

            // set entered data if was error when we do save
            $data = Mage::getSingleton('adminhtml/session')->getInvoiceData(true);
            if (! empty($data)) {
                $model->setData($data);
            }

            Mage::register('sales_invoice', $model);

            $this->_initAction()
                ->_addBreadcrumb(('Edit Invoice'), __('Edit Invoice'))
                ->_addContent($this->getLayout()->createBlock('adminhtml/sales_invoice_edit'))
                ->renderLayout();
        } else {
            $this->_redirect('*/*/');
        }
    }

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('invoice_id');
        $model = Mage::getModel('sales/invoice');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(__('This invoice no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(__('This invoice no longer exists'));
            $this->_redirect('*/*/');
            return;
        }

        Mage::register('sales_invoice', $model);

        if (Mage_Sales_Model_Invoice::TYPE_CMEMO == $model->getInvoiceType()) {
            $type = 'cmemo';
        } else {
            $type = 'invoice';
        }

        $this->_initAction()
            ->_addBreadcrumb(__('View Invoice'), __('View Invoice'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/sales_' . $type . '_view'))
            ->renderLayout();
    }

    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('sales/invoice');
    }
}
