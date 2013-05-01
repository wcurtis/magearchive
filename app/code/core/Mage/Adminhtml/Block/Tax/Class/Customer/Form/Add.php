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
 * Admin customer tax class add form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Tax_Class_Customer_Form_Add extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->setUseContainer(true);
        $form->setId('class_form');
        $form->setMethod('POST');

        $classId = $this->getRequest()->getParam('classId', null);
        $classType = $this->getRequest()->getParam('classType', null);
        $className = null;
        $sessionData = Mage::getSingleton('adminhtml/session')->getClassData();

        if( is_array($sessionData) && array_key_exists('class_name', $sessionData) && ($sessionData['class_name'] != '') ) {
            $className = $sessionData['class_name'];
            Mage::getSingleton('adminhtml/session')->setClassData(null);
        }

        if( intval($classId) <= 0 ) {
            $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('tax')->__('Customer Tax Class Information')));
            $fieldset->addField('class_name', 'text',
                                array(
                                    'name' => 'class_name',
                                    'label' => Mage::helper('tax')->__('Class Name'),
                                    'class' => 'required-entry',
                                    'value' => $className,
                                    'required' => true,
                                )
                        );

            $fieldset->addField('class_type', 'hidden',
                                array(
                                    'name' => 'class_type',
                                    'value' => 'CUSTOMER',
                                    'no_span' => true
                                )
                        );
        } else {
            $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('tax')->__('Add New Customer Group')));
        }

        if( intval($classId) > 0 ) {
            $fieldset->addField('submit', 'submit',
                                array(
                                    'name' => 'submit',
                                    'value' => Mage::helper('tax')->__('Add'),
                                    'no_span' => true
                                )
            );

            $fieldset->addField('class_parent_id', 'hidden',
                                array(
                                    'name' => 'class_parent_id',
                                    'value' => $classId,
                                    'no_span' => true
                                )
                        );

            $form->setAction(Mage::getUrl("*/tax_class/saveGroup/classId/{$classId}/classType/{$classType}"));
        } else {
            $form->setAction(Mage::getUrl('*/tax_class/save'));
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
