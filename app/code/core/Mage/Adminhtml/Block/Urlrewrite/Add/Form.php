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
 * Adminhtml add product urlrewrite form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Urlrewrite_Add_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));

        $fieldset = $form->addFieldset('add_urlrewrite_form', array('legend' => Mage::helper('adminhtml')->__('General Information')));

        $fieldset->addField('product_id', 'hidden', array(
	        'name' => 'product_id'
        ));

        $fieldset->addField('category_id', 'hidden', array(
	        'name' => 'category_id'
        ));

        $fieldset->addField('product_name', 'note', array(
            'label' => Mage::helper('adminhtml')->__('Product'),
            'text' => 'product_name',
             )
        );

        $fieldset->addField('category_name', 'note', array(
            'label' => Mage::helper('adminhtml')->__('Category'),
            'text' => 'category_name',
            )
        );

		$stores = Mage::getResourceModel('core/store_collection')->setWithoutDefaultFilter()->load()->toOptionHash();
        $fieldset->addField('store_id', 'select', array(
	        'label' 		=> Mage::helper('adminhtml')->__('Store'),
	        'title' 		=> Mage::helper('adminhtml')->__('Store'),
	        'name' 			=> 'store_id',
	        'required' 		=> true,
	        'options'		=> $stores
        ));

        $fieldset->addField('id_path', 'text', array(
	        'label' 		=> Mage::helper('adminhtml')->__('ID Path'),
	        'title' 		=> Mage::helper('adminhtml')->__('ID Path'),
	        'name' 			=> 'id_path',
	        'required' 		=> true,
        ));

    	$fieldset->addField('request_path', 'text', array(
            'label' 		=> Mage::helper('adminhtml')->__('Request Path'),
            'title' 		=> Mage::helper('adminhtml')->__('Request Path'),
            'name' 	        => 'request_path',
            'required' 		=> true,
        ));

		$fieldset->addField('target_path', 'text', array(
            'label'			=> Mage::helper('adminhtml')->__('Target Path'),
            'title'			=> Mage::helper('adminhtml')->__('Target Path'),
            'name'			=> 'target_path',
            'required'		=> true,
        ));

    	$fieldset->addField('options', 'select', array(
            'label' 	=> Mage::helper('adminhtml')->__('Redirect'),
            'title' 	=> Mage::helper('adminhtml')->__('Redirect'),
            'name' 		=> 'options',
            'options'	=> array(
            	''  => Mage::helper('adminhtml')->__('No'),
                'R' => Mage::helper('adminhtml')->__('Yes'),
            ),

        ));

    	$fieldset->addField('description', 'textarea', array(
            'label' 		=> Mage::helper('adminhtml')->__('Description'),
            'title' 		=> Mage::helper('adminhtml')->__('Description'),
            'name' 			=> 'description',
            'cols'			=> 20,
            'rows'			=> 5,
            'wrap'			=> 'soft'
        ));

        $gridFieldset = $form->addFieldset('add_urlrewrite_grid', array('legend' => Mage::helper('adminhtml')->__('Please select a product')));
        $gridFieldset->addField('products_grid', 'note', array(
            'text' => $this->getLayout()->createBlock('adminhtml/urlrewrite_product_grid')->toHtml(),
        ));

        $gridFieldset = $form->addFieldset('add_urlrewrite_category', array('legend' => Mage::helper('adminhtml')->__('Please select a category')));
        $gridFieldset->addField('category_tree', 'note', array(
            'text' => $this->getLayout()->createBlock('adminhtml/urlrewrite_category_tree')->toHtml(),
        ));

        $gridFieldset = $form->addFieldset('add_urlrewrite_type', array('legend' => Mage::helper('adminhtml')->__('Please select a type')));
        $gridFieldset->addField('type', 'select', array(
	        'label' 	=> Mage::helper('adminhtml')->__('Type'),
	        'title' 	=> Mage::helper('adminhtml')->__('Type'),
	        'name' 		=> 'type',
	        'required' 	=> true,
	        'options'	=> array('' => '',
    	       1 => Mage::helper('adminhtml')->__('Category'),
	           2 => Mage::helper('adminhtml')->__('Product'),
	           3 => Mage::helper('adminhtml')->__('Custom')
	        )
        ));

        $form->setUseContainer(true);
        $form->setAction( $form->getAction() . 'ret/' . $this->getRequest()->getParam('ret') );
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
