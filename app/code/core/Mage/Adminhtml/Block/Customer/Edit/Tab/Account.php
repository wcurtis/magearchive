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
 * Customer account form block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_Account extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
    }

    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_account');
        $form->setFieldNameSuffix('account');

        $customer = Mage::registry('current_customer');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            array('legend'=>Mage::helper('customer')->__('Account Information'))
        );

        $this->_setFieldset($customer->getAttributes(), $fieldset);

        if ($customer->getId()) {
            $form->getElement('created_in')->setDisabled(true);
            $fieldset->removeField('store_id');
        } else {
            $fieldset->removeField('created_in');
        }

        if ($balanceElement = $form->getElement('store_balance')) {
            $balanceElement->setValueFilter(new Varien_Filter_Sprintf('%s', 2, '.', ''));
        }

        if ($customer->getId()) {
            $newFieldset = $form->addFieldset(
                'password_fieldset',
                array('legend'=>Mage::helper('customer')->__('Password Management'))
            );
            // New customer password
            $field = $newFieldset->addField('new_password', 'text',
                array(
                    'label' => Mage::helper('customer')->__('New Password'),
                    'name'  => 'new_password',
                    'class' => 'validate-new-password'
                )
            );
            $field->setRenderer($this->getLayout()->createBlock('adminhtml/customer_edit_renderer_newpass'));
        }
        else {
            $newFieldset = $form->addFieldset(
                'password_fieldset',
                array('legend'=>Mage::helper('customer')->__('Password Management'))
            );
            $field = $newFieldset->addField('password', 'text',
                array(
                    'label' => Mage::helper('customer')->__('Password'),
                    'class' => 'input-text required-entry validate-password',
                    'name'  => 'password',
                    'required' => true
                )
            );
            /*$field = $newFieldset->addField('password_confirm', 'password',
                array(
                    'label' => Mage::helper('customer')->__('Password Confirmation'),
                    'class' => 'input-text required-entry validate-cpassword',
                    'name'  => 'password_confirm',
                    'required' => true
                )
            );*/
            $field->setRenderer($this->getLayout()->createBlock('adminhtml/customer_edit_renderer_newpass'));

            // send welcome email checkbox
            $sendEmail = new Varien_Data_Form_Element_Checkbox();
            $sendEmail->setLabel(Mage::helper('customer')->__('Send welcome email'))
                ->setName('sendemail')
                ->setId('sendemail');
            $fieldset->addElement($sendEmail);
        }


        $form->setValues($customer->getData());

        $this->setForm($form);

        return $this;
    }
}
