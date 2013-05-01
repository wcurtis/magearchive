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

class Mage_Adminhtml_Block_Sitemap_New_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));

        $fieldset = $form->addFieldset('add_sitemap_form', array('legend' => Mage::helper('adminhtml')->__('Sitemap')));

        $fieldset->addField('sitemap_id', 'hidden', array(
	        'name' => 'sitemap_id'
        ));

//
//        $fieldset->addField('sitemap_type', 'text', array(
//            'label' => Mage::helper('adminhtml')->__('Search Engine'),
//            'name' => 'sitemap_type',
//             )
//        );

        $fieldset->addField('sitemap_filename', 'text', array(
            'label' => Mage::helper('adminhtml')->__('Filename'),
            'name' => 'sitemap_filename',
            'required' 		=> true,
            )
        );

        $fieldset->addField('sitemap_path', 'text', array(
            'label' => Mage::helper('adminhtml')->__('Path'),
            'name' => 'sitemap_path',
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



        $form->setUseContainer(true);
        $form->setAction( $form->getAction() . 'ret/' . $this->getRequest()->getParam('ret') );
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
