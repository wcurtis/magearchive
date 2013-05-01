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
 * description
 *
 * @category    Mage
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('current_promo_quote_rule');

        //$form = new Varien_Data_Form(array('id' => 'edit_form1', 'action' => $this->getData('action'), 'method' => 'post'));
        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('salesrule')->__('General Information')));

        if ($model->getId()) {
        	$fieldset->addField('rule_id', 'hidden', array(
                'name' => 'rule_id',
            ));
        }

    	$fieldset->addField('product_ids', 'hidden', array(
            'name' => 'product_ids',
        ));

    	$fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('salesrule')->__('Rule Name'),
            'title' => Mage::helper('salesrule')->__('Rule Name'),
            'required' => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => Mage::helper('salesrule')->__('Description'),
            'title' => Mage::helper('salesrule')->__('Description'),
            'style' => 'width: 98%; height: 100px;',
        ));

    	$fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('salesrule')->__('Status'),
            'title'     => Mage::helper('salesrule')->__('Status'),
            'name'      => 'is_active',
            'required' => true,
            'options'    => array(
                '1' => Mage::helper('salesrule')->__('Enabled'),
                '0' => Mage::helper('salesrule')->__('Disabled'),
            ),
        ));

        $stores = Mage::getResourceModel('core/store_collection')
            ->addFieldToFilter('store_id', array('neq'=>0))
            ->load()->toOptionArray();

    	$fieldset->addField('store_ids', 'multiselect', array(
            'name'      => 'store_ids[]',
            'label'     => Mage::helper('salesrule')->__('Store Views'),
            'title'     => Mage::helper('salesrule')->__('Store Views'),
            'required'  => true,
            'values'    => $stores,
        ));

        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->load()->toOptionArray();

        $found = false;
        foreach ($customerGroups as $group) {
        	if ($group['value']==0) {
        		$found = true;
        	}
        }
        if (!$found) {
        	array_unshift($customerGroups, array('value'=>0, 'label'=>Mage::helper('salesrule')->__('NOT LOGGED IN')));
        }

    	$fieldset->addField('customer_group_ids', 'multiselect', array(
            'name'      => 'customer_group_ids[]',
            'label'     => Mage::helper('salesrule')->__('Customer Groups'),
            'title'     => Mage::helper('salesrule')->__('Customer Groups'),
            'required'  => true,
            'values'    => $customerGroups,
        ));

        $fieldset->addField('coupon_code', 'text', array(
            'name' => 'coupon_code',
            'label' => Mage::helper('salesrule')->__('Coupon code'),
        ));

        $fieldset->addField('uses_per_coupon', 'text', array(
            'name' => 'uses_per_coupon',
            'label' => Mage::helper('salesrule')->__('Uses per coupon'),
        ));

        $fieldset->addField('uses_per_customer', 'text', array(
            'name' => 'uses_per_customer',
            'label' => Mage::helper('salesrule')->__('Uses per customer'),
        ));

    	$fieldset->addField('from_date', 'date', array(
            'name' => 'from_date',
            'label' => Mage::helper('salesrule')->__('From Date'),
            'title' => Mage::helper('salesrule')->__('From Date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
        ));

    	$fieldset->addField('to_date', 'date', array(
            'name' => 'to_date',
            'label' => Mage::helper('salesrule')->__('To Date'),
            'title' => Mage::helper('salesrule')->__('To Date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name' => 'sort_order',
            'label' => Mage::helper('salesrule')->__('Priority'),
        ));


        $form->setValues($model->getData());

        //$form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}