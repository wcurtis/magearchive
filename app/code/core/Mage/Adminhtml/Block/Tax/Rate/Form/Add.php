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
 * Admin product tax class add form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Tax_Rate_Form_Add extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('rate_form');
    }

    protected function _prepareForm()
    {
        $rateId = (int)$this->getRequest()->getParam('rate');
        $rateObject = new Varien_Object();
        $rateModel  = Mage::getSingleton('tax/rate');
        $rateObject->setData($rateModel->getData());

        $form = new Varien_Data_Form();

        $regions = Mage::getModel('directory/region')
            ->getCollection()
            ->addCountryCodeFilter('USA')
            ->load()
            ->toOptionArray();

        if( isset($regions) && count($regions) > 0 ) {
            $regions[0]['value'] = '';
        }

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('tax')->__('Tax Rate Information')));

        if( $rateObject->getTaxRateId() > 0 ) {
            $fieldset->addField('tax_rate_id', 'hidden',
                                array(
                                    'name' => "tax_rate_id",
                                    'value' => $rateObject->getTaxRateId()
                                )
            );
        }

        $fieldset->addField('tax_region_id', 'select',
                            array(
                                'name' => 'tax_region_id',
                                'label' => Mage::helper('tax')->__('State'),
                                'title' => Mage::helper('tax')->__('Please select State'),
                                'class' => 'required-entry',
                                'required' => true,
                                'values' => $regions,
                                'value' => $rateObject->getTaxRegionId()
                            )
        );

        /* FIXME!!! {*/
        $fieldset->addField('tax_county_id', 'select',
                            array(
                                'name' => 'tax_county_id',
                                'label' => Mage::helper('tax')->__('County'),
                                'title' => Mage::helper('tax')->__('Please select County'),
                                'values' => array(
                                    array(
                                        'label' => '*',
                                        'value' => ''
                                    )
                                ),
                                'value' => $rateObject->getTaxCountyId()
                            )
        );
        /*} */

        $fieldset->addField('tax_postcode', 'text',
                            array(
                                'name' => 'tax_postcode',
                                'label' => Mage::helper('tax')->__('Zip/Post Code'),
                                'value' => $rateObject->getTaxPostcode()
                            )
        );

        $rateTypeCollection = Mage::getModel('tax/rate_type')->getCollection()
            ->load();

        foreach ($rateTypeCollection as $rateType) {
            if ($rateModel->getId()) {
                $value = 1*$rateModel->getRateDataCollection()->getItemByRateAndType($rateModel->getId(), $rateType->getTypeId())->getRateValue();
            }
            else {
                $value = '0.0000';
            }
            $value = number_format($value, 4);
            $fieldset->addField('rate_data_'.$rateType->getTypeId(), 'text',
                array(
                    'name' => "rate_data[{$rateType->getTypeId()}]",
                    'label' => $rateType->getTypeName(),
                    'title' => $rateType->getTypeName(),
                    'value' => $value,
                    'class' => 'validate-not-negative-number'
                )
            );
        }

        $form->setAction($this->getUrl('*/tax_rate/save'));
        $form->setUseContainer(true);
        $form->setId('rate_form');
        $form->setMethod('post');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
