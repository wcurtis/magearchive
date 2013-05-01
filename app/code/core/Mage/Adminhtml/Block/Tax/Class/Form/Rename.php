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
 * Admin tax class rname form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Tax_Class_Form_Rename extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $classId = $this->getRequest()->getParam('classId', null);
        $classType = $this->getRequest()->getParam('classType', null);

        $classObject = Mage::getSingleton('tax/class')->load($classId);
        $className = $classObject->getClassName();

        if( Mage::getSingleton('adminhtml/session')->getCustomerClassData() ) {
            $className = Mage::getSingleton('adminhtml/session')->getCustomerClassData()->getClassName();
            Mage::getSingleton('adminhtml/session')->setCustomerClassData(null);
        }

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('tax')->__('Class Details')));

        $fieldset->addField('class_name', 'text',
                            array(
                                'name' => 'class_name',
                                'label' => Mage::helper('tax')->__('Class Name'),
                                'class' => 'required-entry',
                                'required' => true,
                                'value' => $className,
                                'no_span' => true
                            )
        );

        $fieldset->addField('class_id', 'hidden',
                            array(
                                'name' => 'class_id',
                                'value' => $classId,
                                'no_span' => true
                            )
        );

        $fieldset->addField('class_type', 'hidden',
                            array(
                                'name' => 'class_type',
                                'value' => $classType,
                                'no_span' => true
                            )
        );

        $form->setAction(Mage::getUrl('*/tax_class/save'));
        $form->setUseContainer(true);
        $form->setId('class_rename_form');
        $form->setMethod('POST');
        $this->setForm($form);

        return parent::_prepareForm();
    }
}