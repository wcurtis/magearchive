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
 * Create super product settings tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Settings extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {
        $this->setChild('continue_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Continue'),
                    'onclick'   => "setSuperSettings('".$this->getContinueUrl()."','attribute-checkbox', 'attributes')",
                    'class'     => 'save'
                    ))
                );

        $backButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => Mage::helper('catalog')->__('Back'),
                'onclick'   => "setLocation('".$this->getBackUrl()."')",
                'class'     => 'back'
            ));
        $this->setChild('back_button', $backButton);
        return parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('settings', array('legend'=>Mage::helper('catalog')->__('Select Configurable Attributes ')));

        $entityType = Mage::registry('product')->getResource()->getConfig();

        $product = Mage::registry('product');
        $attributes = $product->getAttributes();

        $hasAttributes = false;
        foreach($attributes as $attribute) {
            if($product->canUseAttributeForSuperProduct($attribute)) {
                $hasAttributes = true;
                $fieldset->addField('attribute_'.$attribute->getAttributeId(), 'checkbox', array(
                    'label' => $attribute->getFrontend()->getLabel(),
                    'title' => $attribute->getFrontend()->getLabel(),
                    'name'  => 'attribute',
                    'class' => 'attribute-checkbox',
                    'value' => $attribute->getAttributeId()
                ));
            }
        }

        if ($hasAttributes) {
            $fieldset->addField('attributes', 'hidden', array(
                        'name'  => 'attribute_validate',
                        'value' => '',
                        'class' => 'validate-super-product-attributes'
                    ));

            $fieldset->addField('continue_button', 'note', array(
                'text' => $this->getChildHtml('continue_button'),
            ));
        }
        else {
            $fieldset->addField('note_text', 'note', array(
                'text' => $this->__('This attribute set do not have attributes which we can use for configurable product')
            ));
            $fieldset->addField('back_button', 'note', array(
                'text' => $this->getChildHtml('back_button'),
            ));
        }


        $this->setForm($form);
    }

    public function getContinueUrl()
    {
        return $this->getUrl('*/*/new', array('_current'=>true));
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/*/new', array('set'=>null, 'type'=>null));
    }
}
