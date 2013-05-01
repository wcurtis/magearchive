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
 * Product attributes tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Attributes extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setShowGlobalIcon(true);
    }

    protected function _prepareForm()
    {
        if ($group = $this->getGroup()) {
            $form = new Varien_Data_Form();
            $fieldset = $form->addFieldset('group_fields'.$group->getId(), array('legend'=>Mage::helper('catalog')->__($group->getAttributeGroupName())));
            $attributes = $this->getGroupAttributes();

            $this->_setFieldset($attributes, $fieldset);

            if ($tierPrice = $form->getElement('tier_price')) {
                $tierPrice->setRenderer(
                    $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_price_tier')
                );
            }

            if ($gallery = $form->getElement('gallery')) {
                $gallery->setRenderer(
                    $this->getLayout()->createBlock('adminhtml/widget_form_element_gallery')
                );
            }

            $form->addValues(Mage::registry('product')->getData());
            $form->setFieldNameSuffix('product');
            $this->setForm($form);
        }
    }

    protected function _getAdditionalElementTypes()
    {
        return array(
            'price' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_price'),
            'image' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_image'),
            'boolean' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_helper_form_boolean')
        );
    }
}
