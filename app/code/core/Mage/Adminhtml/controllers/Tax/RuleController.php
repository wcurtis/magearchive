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
 * Tax rule controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Tax_RuleController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('tax')->__('Tax Rules'), Mage::helper('tax')->__('Tax Rules'))
            ->_addContent(
                $this->getLayout()->createBlock('adminhtml/tax_rule_toolbar_add', 'tax_rule_toolbar')
                ->assign('createUrl', Mage::getUrl('*/tax_rule/add'))
                ->assign('header', Mage::helper('tax')->__('Tax Rules'))
            )
            ->_addContent($this->getLayout()->createBlock('adminhtml/tax_rule_grid', 'tax_rule_grid'))
            ->renderLayout();
    }

    public function addAction()
    {
        $this->_initAction()
            ->_addBreadcrumb($this->_getHelper()->__('Tax Rules'), Mage::helper('tax')->__('Tax Rules'), Mage::getUrl('*/tax_rules'))
            ->_addBreadcrumb(Mage::helper('tax')->__('New Tax Rule'), Mage::helper('tax')->__('New Tax Rule'))
            ->_addContent(
                $this->getLayout()->createBlock('adminhtml/tax_rule_toolbar_save')
                ->assign('header', Mage::helper('tax')->__('New Tax Rule'))
                ->assign('form', $this->getLayout()->createBlock('adminhtml/tax_rule_form_add'))
            )
            ->renderLayout();
    }

    public function saveAction()
    {
        if( $postData = $this->getRequest()->getPost() ) {
            $ruleModel = Mage::getSingleton('tax/rule');
            $ruleModel->setData($postData);
            try {
                $ruleModel->save();
                $this->getResponse()->setRedirect(Mage::getUrl("*/*/"));
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('tax')->__('Tax rule was successfully saved'));
                $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
            } catch (Exception $e) {
                #Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tax')->__('Error while saving this tax rule. Please try again later.'));
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirectReferer();
            }
        }
    }

    public function editAction()
    {
        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('tax')->__('Tax Rules'), Mage::helper('tax')->__('Tax Rules'), Mage::getUrl('*/tax_rule'))
            ->_addBreadcrumb(Mage::helper('tax')->__('Edit Tax Rule'), Mage::helper('tax')->__('Edit Tax Rule'))
            ->_addContent(
                $this->getLayout()->createBlock('adminhtml/tax_rule_toolbar_save')
                    ->assign('header', Mage::helper('tax')->__('Edit Tax Rule'))
                    ->assign('form', $this->getLayout()->createBlock('adminhtml/tax_rule_form_add'))
            )
            ->renderLayout();
    }

    public function deleteAction()
    {
        try {
            $ruleModel = Mage::getSingleton('tax/rule');
            $ruleModel->setTaxRuleId($this->getRequest()->getParam('rule'));
            $ruleModel->delete();
            $this->getResponse()->setRedirect(Mage::getUrl("*/*/"));
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('tax')->__('Tax rule was successfully deleted'));
            $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tax')->__('Error while deleting this tax rule. Please try again later.'));
            $this->_redirectReferer();
        }
    }

    /**
     * Initialize action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/tax/tax_rules')
            ->_addBreadcrumb(Mage::helper('tax')->__('Sales'), Mage::helper('tax')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('tax')->__('Tax'), Mage::helper('tax')->__('Tax'))
//            ->_addLeft($this->getLayout()->createBlock('adminhtml/tax_tabs', 'tax_tabs')->setActiveTab('tax_rule'))
        ;
        return $this;
    }

    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('sales/tax/rules');
    }
}
