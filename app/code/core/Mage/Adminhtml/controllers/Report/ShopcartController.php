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
 * Shopping Cart reports admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Report_ShopcartController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('reports')->__('Reports'), Mage::helper('reports')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('reports')->__('Shopping Cart'), Mage::helper('reports')->__('Shopping Cart'));
        return $this;
    }

    public function customerAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/shopcart/customer')
            ->_addBreadcrumb(Mage::helper('reports')->__('Customers Report'), Mage::helper('reports')->__('Customers Report'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_shopcart_customer'))
            ->renderLayout();
    }

    /**
     * Export shopcart customer report to CSV format
     */
    public function exportCustomerCsvAction()
    {
        $fileName   = 'shopcart_customer.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_shopcart_customer_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export shopcart customer report to XML format
     */
    public function exportCustomerXmlAction()
    {
        $fileName   = 'shopcart_customer.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_shopcart_customer_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function productAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/shopcart/product')
            ->_addBreadcrumb(Mage::helper('reports')->__('Products Report'), Mage::helper('reports')->__('Products Report'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_shopcart_product'))
            ->renderLayout();
    }

    /**
     * Export products report grid to CSV format
     */
    public function exportProductCsvAction()
    {
        $fileName   = 'shopcart_product.csv';
        $content    = $this->getLayout()->createBlock('adminhtml/report_shopcart_product_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export products report to XML format
     */
    public function exportProductXmlAction()
    {
        $fileName   = 'shopcart_product.xml';
        $content    = $this->getLayout()->createBlock('adminhtml/report_shopcart_product_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'customer':
                return Mage::getSingleton('admin/session')->isAllowed('report/shopcart/customer');
                break;
            case 'product':
                return Mage::getSingleton('admin/session')->isAllowed('report/shopcart/product');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('report/shopcart');
                break;
        }
    }

    protected function _sendUploadResponse($fileName, $content)
    {
        header('HTTP/1.1 200 OK');
        header('Content-Disposition: attachment; filename='.$fileName);
        header('Last-Modified: '.date('r'));
        header("Accept-Ranges: bytes");
        header("Content-Length: ".sizeof($content));
        header("Content-type: application/octet-stream");
        echo $content;
        exit;
    }
}