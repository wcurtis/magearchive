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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layer attribute filter
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Abstract 
{
    const OPTIONS_ONLY_WITH_RESULTS = 1;
    
    public function __construct()
    {
        parent::__construct();
        $this->_requestVar = 'attribute';
    }
    
    public function setAttributeModel($attribute)
    {
        $this->setRequestVar($attribute->getAttributeCode());
        $this->setData('attribute_model', $attribute);
        return $this;
    }
    
    public function getAttributeModel()
    {
        $attribute = $this->getData('attribute_model');
        if (is_null($attribute)) {
            Mage::throwException(Mage::helper('catalog')->__('Attribute model not defined'));
        }
        return $attribute;
    }
    
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock) 
    {
        $filter = $request->getParam($this->_requestVar);
        $text = $this->_getOptionText($filter);
        if ($filter && $text) {
            Mage::getSingleton('catalog/layer')->getProductCollection()
                ->addFieldToFilter($this->getAttributeModel()->getAttributeCode(), $filter);
                
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($text, $filter)
            );
            $this->_items = array();
        }
        return $this;
    }
    
    protected  function _getOptionText($optionId)
    {
        return $this->getAttributeModel()->getFrontend()->getOption($optionId);
    }
    
    public function getName()
    {
        return Mage::helper('catalog')->__($this->getAttributeModel()->getFrontend()->getLabel());
    }
    
    protected function _initItems()
    {
        $attribute = $this->getAttributeModel();
        $options = $attribute->getFrontend()->getSelectOptions();
        
        $optionsCount = Mage::getSingleton('catalog/layer')->getProductCollection()
            ->getAttributeValueCount($attribute);
            
        $this->_requestVar = $attribute->getAttributeCode();
        
        $items=array();
        
        foreach ($options as $option) {
            if (strlen($option['value'])) {
                // Check filter type
                if ($attribute->getIsFilterable() == self::OPTIONS_ONLY_WITH_RESULTS) {
                    if (!empty($optionsCount[$option['value']])) {
                        $items[] = Mage::getModel('catalog/layer_filter_item')
                            ->setFilter($this)
                            ->setLabel($option['label'])
                            ->setValue($option['value'])
                            ->setCount($optionsCount[$option['value']]);
                    }
                }
                else {
                    $items[] = Mage::getModel('catalog/layer_filter_item')
                        ->setFilter($this)
                        ->setLabel($option['label'])
                        ->setValue($option['value'])
                        ->setCount(isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0);
                }
            }
        }

        
        $this->_items = $items;
        return $this;
    }
}
