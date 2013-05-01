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
 * Customer edit block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/edit.phtml');
        $this->setId('product_edit');
    }

    protected function _prepareLayout()
    {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Back'),
                    'onclick'   => 'setLocation(\''.$this->getUrl('*/*/', array('store'=>$this->getRequest()->getParam('store', 0))).'\')',
                    'class' => 'back'
                ))
        );

        $this->setChild('reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Reset'),
                    'onclick'   => 'setLocation(\''.$this->getUrl('*/*/*', array('_current'=>true)).'\')'
                ))
        );

        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Save'),
                    'onclick'   => 'productForm.submit()',
                    'class' => 'save'
                ))
        );


        $this->setChild('save_and_edit_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Save And Continue Edit'),
                    'onclick'   => 'saveAndContinueEdit()',
                    'class' => 'save'
                ))
        );

        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Delete'),
                    'onclick'   => 'confirmSetLocation(\''.Mage::helper('catalog')->__('Are you sure?').'\', \''.$this->getDeleteUrl().'\')',
                    'class'  => 'delete'
                ))
        );

        $this->setChild('duplicate_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Duplicate'),
                    'onclick'   => 'setLocation(\''.$this->getDuplicateUrl().'\')',
                    'class'  => 'add'
                ))
        );
        return parent::_prepareLayout();
    }

    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getSaveAndEditButtonHtml()
    {
        return $this->getChildHtml('save_and_edit_button');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getDuplicateButtonHtml()
    {
        return $this->getChildHtml('duplicate_button');
    }

    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current'=>true));
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true));
    }

    public function getProductId()
    {
        return Mage::registry('product')->getId();
    }

    public function getProductSetId()
    {
        $setId = false;
        if (!($setId = Mage::registry('product')->getAttributeSetId()) && $this->getRequest()) {
            $setId = $this->getRequest()->getParam('set', null);
        }
        return $setId;
    }

    public function getRelatedProductsJSON()
    {
        $result = array();

        foreach (Mage::registry('product')->getRelatedProductsLoaded() as $product) {
            $result[$product->getEntityId()] = $product->toArray(
                $product->getAttributeCollection()->getAttributeCodes()
            );
        }

        if(!empty($result)) {
            return Zend_Json_Encoder::encode($result);
        }

        return '{}';
    }


    public function getUpSellProductsJSON()
    {
        $result = array();

        foreach (Mage::registry('product')->getUpSellProductsLoaded() as $product) {
            $result[$product->getEntityId()] = $product->toArray(
                $product->getAttributeCollection()->getAttributeCodes()
            );
        }

        if(!empty($result)) {
            return Zend_Json_Encoder::encode($result);
        }

        return '{}';
    }

    public function getCrossSellProductsJSON()
    {
        $result = array();

        foreach (Mage::registry('product')->getCrossSellProductsLoaded() as $product) {
            $result[$product->getEntityId()] = $product->toArray(
                $product->getAttributeCollection()->getAttributeCodes()
            );
        }

        if(!empty($result)) {
            return Zend_Json_Encoder::encode($result);
        }

        return '{}';
    }

    public function getSuperGroupProductJSON()
    {
        $result = array();

        foreach (Mage::registry('product')->getSuperGroupProductsLoaded() as $product) {
            $result[$product->getEntityId()] = $product->toArray(
                $product->getAttributeCollection()->getAttributeCodes()
            );
        }

        if(!empty($result)) {
            return Zend_Json_Encoder::encode($result);
        }

        return '{}';
    }

    public function getProduct()
    {
         return Mage::registry('product');
    }

    public function getIsSuperGroup()
    {
        return Mage::registry('product')->isSuperGroup();
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('_current'=>true));
    }

    public function getDuplicateUrl()
    {
        return $this->getUrl('*/*/duplicate', array('_current'=>true));
    }

    public function getHeader()
    {
        $header = '';
        if (Mage::registry('product')->getId()) {
            $header = Mage::registry('product')->getName();
        }
        else {
            $header = Mage::helper('catalog')->__('New Product');
        }
        if ($setName = $this->getAttributeSetName()) {
            $header.= ' (' . $setName . ')';
        }
        return $header;
    }

    public function getAttributeSetName()
    {
        if ($setId = Mage::registry('product')->getAttributeSetId()) {
            $set = Mage::getModel('eav/entity_attribute_set')
                ->load($setId);
            return $set->getAttributeSetName();
        }
        return '';
    }

    public function getIsConfigured()
    {
        if (!($superAttributes = Mage::registry('product')->getSuperAttributesIds())) {
            $superAttributes = false;
        }

        return !Mage::registry('product')->isSuperConfig() || $superAttributes !== false;
    }

    public function getSelectedTabId()
    {
        return addslashes(htmlspecialchars($this->getRequest()->getParam('tab')));
    }
}
