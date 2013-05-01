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
 * Adminhtml customer tax class controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Tax_Class_CustomerController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('tax')->__('Customer Tax Classes'), Mage::helper('tax')->__('Customer Tax Classes'))
            ->_addContent(
        		$this->getLayout()->createBlock('adminhtml/tax_class_toolbar_add')
        		->assign('createUrl', Mage::getUrl('*/tax_class_customer/add'))
        		->assign('header', Mage::helper('tax')->__('Customer Tax Classes'))
        	)
            ->_addContent($this->getLayout()->createBlock('adminhtml/tax_class_grid_default'))
            ->renderLayout();
    }

    public function addAction()
    {
        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('tax')->__('Customer Tax Classes'), Mage::helper('tax')->__('Customer Tax Classes'), Mage::getUrl('*/tax_class_customer'))
            ->_addBreadcrumb(Mage::helper('tax')->__('New Customer Tax Class'), Mage::helper('tax')->__('New Customer Tax Class'))
            ->_addContent(
                $this->getLayout()->createBlock('adminhtml/tax_class_toolbar_save')
                    ->assign('header', Mage::helper('tax')->__('New Customer Tax Class'))
                    ->assign('form', $this->getLayout()->createBlock('adminhtml/tax_class_customer_form_add'))
            )
            ->renderLayout();
    }

    /**
     * Initialize action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/tax/tax_classes_customer')
            ->_addBreadcrumb(Mage::helper('tax')->__('Sales'), Mage::helper('tax')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('tax')->__('Tax'), Mage::helper('tax')->__('Tax'))
//            ->_addLeft($this->getLayout()->createBlock('adminhtml/tax_tabs', 'tax_tabs')->setActiveTab('tax_class_customer'))
        ;
        return $this;
    }

    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('sales/tax/classes_customer');
    }

}