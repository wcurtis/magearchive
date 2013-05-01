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
 * Adminhtml sales invoice view
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sales_Cmemo_View extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'invoice_id';
        $this->_controller = 'sales_cmemo';

        $this->_mode = 'view';

        parent::__construct();


        $this->_removeButton('delete');
        $this->_removeButton('save');
        $this->_removeButton('reset');

        $this->_addButton('edit', array(
            'label' => Mage::helper('sales')->__('Edit Invoice'),
            'onclick'   => 'window.location.href=\'' . $this->getEditUrl() . '\'',
        ));

        $this->_addButton('credit_memo', array(
            'label' => Mage::helper('sales')->__('Create Credit Memo'),
            'onclick'   => 'window.location.href=\'' . $this->getCreateMemoUrl() . '\'',
            'class' => 'add',
        ));

        $this->setId('sales_invoice_view');
    }

    public function getHeaderText()
    {
        return Mage::helper('sales')->__('Invoice #') . Mage::registry('sales_invoice')->getIncrementId();
    }

    public function getCreateMemoUrl()
    {
        return Mage::getUrl('*/sales_invoice/cmemo', array('invoice_id' => $this->getRequest()->getParam('invoice_id')));
    }

    public function getEditUrl()
    {
        return Mage::getUrl('*/sales_invoice/edit', array('invoice_id' => $this->getRequest()->getParam('invoice_id')));
    }

}
