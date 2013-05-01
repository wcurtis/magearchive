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
 * Adminhtml invoice create form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sales_Invoice_Create_Form extends Mage_Adminhtml_Block_Widget_Form
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('invoice_form');
        $this->setTitle(Mage::helper('sales')->__('Invoice Information'));
        $this->setTemplate('sales/invoice/create.phtml');
    }

    public function getInvoice()
    {
        return Mage::registry('sales_invoice');
    }

    public function getOrder()
    {
        return Mage::registry('sales_invoice')->getOrder();
    }

    protected function _prepareLayout()
    {
        $this->setChild('items', $this->getLayout()->createBlock( 'adminhtml/sales_invoice_create_items', 'sales_invoice_create_items'));
        return parent::_prepareLayout();
    }

    public function getItemsHtml()
    {
        return $this->getChildHtml('items');
    }

    public function getOrderDateFormatted($format='short')
    {
        return $this->formatDate($this->getOrder()->getCreatedAt(), $format);
    }

    public function getSaveUrl()
    {
        return Mage::getUrl('*/*/savenew', array('order_id' => $this->getRequest()->getParam('order_id')));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('sales_invoice');

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('sales')->__('General Information')));

        if ($model->getEntityId()) {
            $fieldset->addField('entity_id', 'hidden', array(
                'name' => 'entity_id',
            ));
        } else {
            $fieldset->addField('order_id', 'hidden', array(
                'name' => 'order_id',
            ));
        }

        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}