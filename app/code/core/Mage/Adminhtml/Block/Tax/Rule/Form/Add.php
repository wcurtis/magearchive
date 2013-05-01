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
 * Admin tax rule add form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Tax_Rule_Form_Add extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _prepareForm()
    {
        $ruleId = $this->getRequest()->getParam('rule');
        $ruleObject = Mage::getSingleton('tax/rule')->load($ruleId);

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('tax')->__('Tax Rule Information')));

        $classCustomer = Mage::getResourceModel('tax/class_collection')
                        ->setClassTypeFilter('CUSTOMER')
                        ->load()
                        ->toOptionArray();

        $classProduct = Mage::getResourceModel('tax/class_collection')
                        ->setClassTypeFilter('PRODUCT')
                        ->load()
                        ->toOptionArray();

        $rateTypeCollection = Mage::getResourceModel('tax/rate_type_collection')
                        ->load()
                        ->toOptionArray();

        $fieldset->addField('tax_customer_class_id', 'select',
                            array(
                                'name' => 'tax_customer_class_id',
                                'label' => Mage::helper('tax')->__('Customer Tax Class'),
                                'title' => Mage::helper('tax')->__('Please select Customer Tax Class'),
                                'class' => 'required-entry',
                                'required' => true,
                                'values' => $classCustomer,
                                'value' => $ruleObject->getTaxCustomerClassId()
                            )
        );

        $fieldset->addField('tax_product_class_id', 'select',
                            array(
                                'name' => 'tax_product_class_id',
                                'label' => Mage::helper('tax')->__('Product Tax Class'),
                                'title' => Mage::helper('tax')->__('Please select Product Tax Class'),
                                'class' => 'required-entry',
                                'required' => true,
                                'values' => $classProduct,
                                'value' => $ruleObject->getTaxProductClassId()
                            )
        );

        $fieldset->addField('tax_rate_type_id', 'select',
                            array(
                                'name' => 'tax_rate_type_id',
                                'label' => Mage::helper('tax')->__('Rate'),
                                'title' => Mage::helper('tax')->__('Please select Rate'),
                                'class' => 'required-entry',
                                'values' => $rateTypeCollection,
                                'value' => $ruleObject->getTaxRateTypeId()
                            )
        );

        if( $ruleId > 0 ) {
            $fieldset->addField('tax_rule_id', 'hidden',
                                array(
                                    'name' => 'tax_rule_id',
                                    'value' => $ruleId,
                                    'no_span' => true
                                )
            );
        }

        $form->setAction(Mage::getUrl('*/tax_rule/save'));
        $form->setUseContainer(true);
        $form->setId('rule_form');
        $form->setMethod('POST');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
