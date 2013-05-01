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
 * Product attribute add/edit form main tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('entity_attribute');

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('catalog')->__('Attribute Properties')));

        $yesno = array(
            array(
                'value' => 0,
                'label' => Mage::helper('catalog')->__('No')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('catalog')->__('Yes')
            ));

        $fieldset->addField('attribute_code', 'text', array(
            'name'  => 'attribute_code',
            'label' => Mage::helper('catalog')->__('Attribute Identifier<br/>(For internal use. Must be unique with no spaces)'),
            'title' => Mage::helper('catalog')->__('Attribute Identifier'),
            'class' => 'validate-code',
            'required' => true,
        ));


        $fieldset->addField('frontend_input', 'select', array(
            'name' => 'frontend_input',
            'label' => Mage::helper('catalog')->__('Catalog Input Type for Store Owner'),
            'title' => Mage::helper('catalog')->__('Catalog Input Type for Store Owner'),
            'value' => 'text',
            'values'=>  array(
                array(
                    'value' => 'text',
                    'label' => Mage::helper('catalog')->__('Text Field')
                ),
                array(
                    'value' => 'textarea',
                    'label' => Mage::helper('catalog')->__('Text Area')
                ),
                array(
                    'value' => 'date',
                    'label' => Mage::helper('catalog')->__('Date')
                ),
                array(
                    'value' => 'boolean',
                    'label' => Mage::helper('catalog')->__('Yes/No')
                ),
                array(
                    'value' => 'multiselect',
                    'label' => Mage::helper('catalog')->__('Multiple Select')
                ),
                array(
                    'value' => 'select',
                    'label' => Mage::helper('catalog')->__('Dropdown')
                ),
                array(
                    'value' => 'price',
                    'label' => Mage::helper('catalog')->__('Price')
                ),
                array(
                    'value' => 'image',
                    'label' => Mage::helper('catalog')->__('Image')
                ),
            )
        ));

        $fieldset->addField('is_unique', 'select', array(
            'name' => 'is_unique',
            'label' => Mage::helper('catalog')->__('Unique Value (not shared with other products)'),
            'title' => Mage::helper('catalog')->__('Unique Value (not shared with other products)'),
            'values' => $yesno,
        ));

        $fieldset->addField('is_required', 'select', array(
            'name' => 'is_required',
            'label' => Mage::helper('catalog')->__('Values Required'),
            'title' => Mage::helper('catalog')->__('Values Required'),
            'values' => $yesno,
        ));

        $fieldset->addField('frontend_class', 'select', array(
            'name'  => 'frontend_class',
            'label' => Mage::helper('catalog')->__('Input Validation for Store Owner'),
            'title' => Mage::helper('catalog')->__('Input Validation for Store Owner'),
            'values'=>  array(
                array(
                    'value' => '',
                    'label' => Mage::helper('catalog')->__('None')
                ),
                array(
                    'value' => 'validate-number',
                    'label' => Mage::helper('catalog')->__('Decimal Number')
                ),
                array(
                    'value' => 'validate-digits',
                    'label' => Mage::helper('catalog')->__('Integer Number')
                ),
                array(
                    'value' => 'validate-email',
                    'label' => Mage::helper('catalog')->__('Email')
                ),
                array(
                    'value' => 'validate-url',
                    'label' => Mage::helper('catalog')->__('Url')
                ),
                array(
                    'value' => 'validate-alpha',
                    'label' => Mage::helper('catalog')->__('Letters')
                ),
                array(
                    'value' => 'validate-alphanum',
                    'label' => Mage::helper('catalog')->__('Letters(a-z) or Numbers(0-9)')
                ),
            )
        ));

        /*$fieldset->addField('apply_to', 'select', array(
            'name' => 'apply_to',
            'label' => Mage::helper('catalog')->__('Apply To'),
            'title' => Mage::helper('catalog')->__('Apply To'),
            'values' => array(
                array('value' => '0', 'label' => Mage::helper('catalog')->__('All Products')),
                array('value' => '1', 'label' => Mage::helper('catalog')->__('Physical Products')),
                array('value' => '2', 'label' => Mage::helper('catalog')->__('Virtual Products')),
            ),
        ));*/

        $fieldset->addField('use_in_super_product', 'select', array(
            'name' => 'use_in_super_product',
            'label' => Mage::helper('catalog')->__('Apply To Configurable/Grouped Product'),
            'values' => $yesno,
        ));


        if ($model->getId()) {
            $form->getElement('attribute_code')->setDisabled(1);
        }
        if (!$model->getIsUserDefined() && $model->getId()) {
            $form->getElement('is_unique')->setDisabled(1);
        }

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
