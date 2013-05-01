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
 * sales admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_ReportController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Reports'), Mage::helper('adminhtml')->__('Reports'));
        return $this;
    }

    public function salesAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Sales Report'), Mage::helper('adminhtml')->__('Sales Report'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_sales'))
            ->renderLayout();
    }

    /**
     * Export sales report grid to CSV format
     */
    public function exportSalesCsvAction()
    {
        $fileName   = 'sales.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_sales_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export sales report grid to Excel XML format
     */
    public function exportSalesExcelAction()
    {
        $fileName   = 'sales.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_sales_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function taxAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/tax')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Tax'), Mage::helper('adminhtml')->__('Tax'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_tax'))
            ->renderLayout();
    }

    /**
     * Export tax report grid to CSV format
     */
    public function exportTaxCsvAction()
    {
        $fileName   = 'tax.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tax_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export tax report grid to Excel XML format
     */
    public function exportTaxExcelAction()
    {
        $fileName   = 'tax.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_tax_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function invoicedAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/invoiced')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Total Invoiced'), Mage::helper('adminhtml')->__('Total Invoiced'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_invoiced'))
            ->renderLayout();
    }

    /**
     * Export invoiced report grid to CSV format
     */
    public function exportInvoicedCsvAction()
    {
        $fileName   = 'invoiced.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_invoiced_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export invoiced report grid to Excel XML format
     */
    public function exportInvoicedExcelAction()
    {
        $fileName   = 'invoiced.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_invoiced_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function refundedAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/refunded')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Total Refunded'), Mage::helper('adminhtml')->__('Total Refunded'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_refunded'))
            ->renderLayout();
    }

    /**
     * Export refunded report grid to CSV format
     */
    public function exportRefundedCsvAction()
    {
        $fileName   = 'refunded.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_refunded_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export refunded report grid to Excel XML format
     */
    public function exportRefundedExcelAction()
    {
        $fileName   = 'refunded.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_refunded_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function couponsAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/coupons')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Coupons'), Mage::helper('adminhtml')->__('Coupons'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_coupons'))
            ->renderLayout();
    }

    /**
     * Export coupons report grid to CSV format
     */
    public function exportCouponsCsvAction()
    {
        $fileName   = 'coupons.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_coupons_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export coupons report grid to Excel XML format
     */
    public function exportCouponsExcelAction()
    {
        $fileName   = 'coupons.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_coupons_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function wishlistAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/wishlist')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Wishlist Report'), Mage::helper('adminhtml')->__('Wishlist Report'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_wishlist'))
            ->renderLayout();
    }

    /**
     * Export wishlist report grid to CSV format
     */
    public function exportWishlistCsvAction()
    {
        $fileName   = 'wishlist.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_wishlist_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export wishlist report to Excel XML format
     */
    public function exportWishlistExcelAction()
    {
        $fileName   = 'wishlist.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_wishlist_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function searchAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/search')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Search Terms'), Mage::helper('adminhtml')->__('Search Terms'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_search'))
            ->renderLayout();
    }

    /**
     * Export search report grid to CSV format
     */
    public function exportSearchCsvAction()
    {
        $fileName   = 'search.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_search_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export search report to Excel XML format
     */
    public function exportSearchExcelAction()
    {
        $fileName   = 'search.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_search_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }
/*
    public function customersAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/customers')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Best Customers'), Mage::helper('adminhtml')->__('Best Customers'))
            ->renderLayout();
    }
*/
    public function ordersAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/orders')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Recent Orders'), Mage::helper('adminhtml')->__('Recent Orders'))
            ->renderLayout();
    }

    public function totalsAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/totals')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Order Totals'), Mage::helper('adminhtml')->__('Order Totals'))
            ->renderLayout();
    }

    public function accountsAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/accounts')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('New Accounts'), Mage::helper('adminhtml')->__('New Accounts'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_accounts'))
            ->renderLayout();
    }

    /**
     * Export new accounts report grid to CSV format
     */
    public function exportAccountsCsvAction()
    {
        $fileName   = 'new_accounts.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_accounts_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export new accounts report grid to Excel XML format
     */
    public function exportAccountsExcelAction()
    {
        $fileName   = 'accounts.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_accounts_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function shippingAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/shipping')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Shipping'), Mage::helper('adminhtml')->__('Shipping'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_shipping'))
            ->renderLayout();
    }

    /**
     * Export shipping report grid to CSV format
     */
    public function exportShippingCsvAction()
    {
        $fileName   = 'shipping.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_shipping_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export shipping report grid to Excel XML format
     */
    public function exportShippingExcelAction()
    {
        $fileName   = 'shipping.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_shipping_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed()
    {
	    switch ($this->getRequest()->getActionName()) {
            case 'sales':
                return Mage::getSingleton('admin/session')->isAllowed('report/sales');
                break;
            case 'tax':
                return Mage::getSingleton('admin/session')->isAllowed('report/tax');
                break;
            case 'invoiced':
                return Mage::getSingleton('admin/session')->isAllowed('report/invoiced');
                break;
            case 'refunded':
                return Mage::getSingleton('admin/session')->isAllowed('report/refunded');
                break;
            case 'products':
                return Mage::getSingleton('admin/session')->isAllowed('report/products');
                break;
            case 'coupons':
                return Mage::getSingleton('admin/session')->isAllowed('report/coupons');
                break;
            case 'wishlist':
                return Mage::getSingleton('admin/session')->isAllowed('report/wishlist');
                break;
            case 'search':
                return Mage::getSingleton('admin/session')->isAllowed('report/search');
                break;
            case 'accounts':
                return Mage::getSingleton('admin/session')->isAllowed('report/accounts');
                break;
            case 'shipping':
                return Mage::getSingleton('admin/session')->isAllowed('report/shipping');
                break;
            /*
            case 'customers':
                return Mage::getSingleton('admin/session')->isAllowed('report/shopcart');
                break;
            */
            case 'orders':
                return Mage::getSingleton('admin/session')->isAllowed('report/orders');
                break;
            case 'totals':
                return Mage::getSingleton('admin/session')->isAllowed('report/totals');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('report');
                break;
        }
    }
}