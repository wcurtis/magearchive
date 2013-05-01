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
 * Convert profile edit tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_System_Convert_Gui_Edit_Tab_Wizard extends Mage_Adminhtml_Block_Widget_Container
{

    protected $_stores;
    protected $_attributes;
    protected $_addMapButtonHtml;
    protected $_removeMapButtonHtml;
    protected $_shortDateFormat;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('system/convert/profile/wizard.phtml');
    }

    protected function _prepareLayout()
    {
        if ($this->getLayout()->getBlock('root')) {
            $this->getLayout()->getBlock('root')->setCanLoadCalendarJs(true);
        }
        return $this;
    }

    public function getAttributes($entityType)
    {
        if (!isset($this->_attributes[$entityType])) {
            switch ($entityType) {
                case 'product':
                    $attributes = Mage::getSingleton('catalog/convert_parser_product')
                        ->getExternalAttributes();
                    break;

                case 'customer':
                    $attributes = Mage::getSingleton('customer/convert_parser_customer')
                        ->getExternalAttributes();
                    break;
            }

            array_splice($attributes, 0, 0, array(''=>$this->__('Choose an attribute')));
            $this->_attributes[$entityType] = $attributes;
        }
        return $this->_attributes[$entityType];
    }

    public function getValue($key, $default='')
    {
        $value = $this->getData($key);
        return htmlspecialchars($value ? $value : $default);
    }

    public function getSelected($key, $value)
    {
        return $this->getData($key)==$value ? 'selected' : '';
    }

    public function getChecked($key)
    {
        return $this->getData($key) ? 'checked' : '';
    }

    public function getMappings($entityType)
    {
        $maps = $this->getData('gui_data/map/'.$entityType.'/db');
        return $maps ? $maps : array();
    }

    public function getAddMapButtonHtml()
    {
        if (!$this->_addMapButtonHtml) {
            $this->_addMapButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                ->setClass('add')->setLabel($this->__('Add Field Mapping'))
                ->setOnClick("addFieldMapping()")->toHtml();
        }
        return $this->_addMapButtonHtml;
    }

    public function getRemoveMapButtonHtml()
    {
        if (!$this->_removeMapButtonHtml) {
            $this->_removeMapButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                ->setClass('delete')->setLabel($this->__('Remove'))
                ->setOnClick("removeFieldMapping(this)")->toHtml();
        }
        return $this->_removeMapButtonHtml;
    }

    public function getProductTypeFilterOptions()
    {
        $options = Mage::getResourceModel('catalog/product_type_collection')
            ->load()
            ->toOptionHash();

        array_splice($options, 0, 0, array(''=>$this->__('Any Type')));
        return $options;
    }

    public function getProductAttributeSetFilterOptions()
    {
        $options = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getConfig()->getId())
            ->load()
            ->toOptionHash();

        array_splice($options, 0, 0, array(''=>$this->__('Any Attribute Set')));
        return $options;
    }

    public function getProductVisibilityFilterOptions()
    {
        $options = Mage::getResourceModel('catalog/product_visibility_collection')
            ->load()
            ->toOptionHash();

        array_splice($options, 0, 0, array(''=>$this->__('Any Visibility')));
        return $options;
    }

    public function getProductStatusFilterOptions()
    {
        $options = Mage::getResourceModel('catalog/product_status_collection')
            ->load()
            ->toOptionHash();

        array_splice($options, 0, 0, array(''=>$this->__('Any Status')));
        return $options;
    }

    public function getStoreFilterOptions()
    {
        if (!$this->_filterStores) {
            #$this->_filterStores = array(''=>$this->__('Any Store'));
            $this->_filterStores = array();
            foreach (Mage::getConfig()->getNode('stores')->children() as $storeNode) {
                if ($storeNode->getName()==='default') {
                    //continue;
                }
                $this->_filterStores[$storeNode->getName()] = (string)$storeNode->system->store->name;
            }
        }
        return $this->_filterStores;
    }

    public function getCustomerGroupFilterOptions()
    {
        $options = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=>0))
            ->load()
            ->toOptionHash();

        array_splice($options, 0, 0, array(''=>$this->__('Any Group')));
        return $options;
    }

    public function getCountryFilterOptions()
    {
        $options = Mage::getResourceModel('directory/country_collection')
            ->load()->toOptionArray(false);
        array_unshift($options, array('value'=>'', 'label'=>Mage::helper('adminhtml')->__('All countries')));
        return $options;
    }

    public function getStores()
    {
        if (!$this->_stores) {
            foreach (Mage::getConfig()->getNode('stores')->children() as $storeNode) {
                $storeId = (int)$storeNode->system->store->id;
                if ($storeId==0) {
                    $this->_stores[$storeId] = $this->__('Default Values');
                } else {
                    $this->_stores[$storeId] = (string)$storeNode->system->store->name;
                }
            }
        }
        return $this->_stores;
    }

    public function getShortDateFormat()
    {
        if (!$this->_shortDateFormat) {
            $this->_shortDateFormat = Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        }
        return $this->_shortDateFormat;
    }

}

