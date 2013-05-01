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
 * @package    Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order create address form
 *
 */
class Mage_Adminhtml_Block_Sales_Order_Create_Form_Address extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract
{
    protected $_form;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sales/order/create/form/address.phtml');
    }

    public function getAddressCollection()
    {
        return $this->getCustomer()->getLoadedAddressCollection();
    }

    public function getAddressCollectionJson()
    {
        $data = array();
        foreach ($this->getAddressCollection() as $address) {
        	$data[$address->getId()] = $address->getData();
        }
        return Zend_Json::encode($data);
    }

    public function getForm()
    {
        $this->_prepareForm();
        return $this->_form;
    }

    protected function _prepareForm()
    {
        if (!$this->_form) {
            $this->_form = new Varien_Data_Form();
            $addressModel = Mage::getModel('customer/address');

            foreach ($addressModel->getAttributes() as $attribute) {
                if (!$attribute->getIsVisible()) {
                    continue;
                }
                if ($inputType = $attribute->getFrontend()->getInputType()) {
                    $element = $this->_form->addField($attribute->getAttributeCode(), $inputType,
                        array(
                            'name'  => $attribute->getAttributeCode(),
                            'label' => $attribute->getFrontend()->getLabel(),
                            'class' => $attribute->getFrontend()->getClass(),
                            'required' => $attribute->getIsRequired(),
                        )
                    )
                    ->setEntityAttribute($attribute);

                    if ($inputType == 'select' || $inputType == 'multiselect') {
                        $element->setValues($attribute->getFrontend()->getSelectOptions());
                    }
                }
            }

            if ($regionElement = $this->_form->getElement('region')) {
                $regionElement->setRenderer(
                    $this->getLayout()->createBlock('adminhtml/customer_edit_renderer_region')
                );
            }
            $this->_form->getElement('region_id')->setDefaultHtml('');
            $this->_form->setValues($this->getFormValues());
        }
        return $this;
    }

    public function getFormValues()
    {
        return array();
    }

    public function getAddressId()
    {
        return false;
    }

    public function getAddressAsString($address)
    {
        return $address->toString('{{firstname}} {{lastname}}, {{street}}, {{city}}, {{region}} {{postcode}}');
    }
}