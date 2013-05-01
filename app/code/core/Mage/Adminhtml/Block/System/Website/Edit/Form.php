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
 * Adminhtml tag edit form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_System_Website_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('website_form');
        $this->setTitle(Mage::helper('adminhtml')->__('Website Information'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('admin_current_website');

        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('adminhtml')->__('General Information')));

        if ($model->getWebsiteId()) {
            $fieldset->addField('website_id', 'hidden', array(
                'name' => 'website_id',
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('adminhtml')->__('Website Name'),
            'title' => Mage::helper('adminhtml')->__('Website Name'),
            'required' => true,
        ));

        $fieldset->addField('code', 'text', array(
            'name' => 'code',
            'label' => Mage::helper('adminhtml')->__('Website Code'),
            'title' => Mage::helper('adminhtml')->__('Website Code'),
            'required' => true,
            'class' => 'validate-code',
        ));

        $fieldset->addField('is_active', 'select', array(
            'label' => Mage::helper('adminhtml')->__('Status'),
            'title' => Mage::helper('adminhtml')->__('Status'),
            'name' => 'is_active',
            'required' => true,
            'options' => array(
                0=>Mage::helper('adminhtml')->__('Disabled'),
                1=>Mage::helper('adminhtml')->__('Enabled'),
            ),
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name' => 'sort_order',
            'label' => Mage::helper('adminhtml')->__('Sort order'),
            'title' => Mage::helper('adminhtml')->__('Sort order'),
        ));
        $form->setValues($model->getData());

        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
