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
 * Catalog super product configurable part block
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
 class Mage_Catalog_Block_Product_View_Super_Config extends Mage_Core_Block_Template
 {
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/view/super/config.phtml');
    }

    public function getAllowAttributes()
    {
        $collection = $this->getProduct()->getSuperLinkCollection();
        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
        $collection->setStoreFilterByProduct($this->getProduct());
        return $this->getProduct()->getSuperAttributes(false, true);
    }

    public function getAllowProducts()
    {
        return $this->getProduct()->getSuperLinks();
    }

    public function getJsonConfig()
    {
        $attributes = array();
        $options = array();
        $store = Mage::app()->getStore();

        foreach ($this->getAllowProducts() as $productId => $productAttributes) {
        	foreach ($productAttributes as $attribute) {
        	    if (!isset($options[$attribute['attribute_id']])) {
        	        $options[$attribute['attribute_id']] = array();
        	    }

        	    if (!isset($options[$attribute['attribute_id']][$attribute['value_index']])) {
        	        $options[$attribute['attribute_id']][$attribute['value_index']] = array();
        	    }
        	    $options[$attribute['attribute_id']][$attribute['value_index']][] = $productId;
        	}
        }

        foreach ($this->getAllowAttributes() as $attribute) {
            $attributeId = $attribute['attribute_id'];
        	$info = array(
        	   'id'        => $attributeId,
        	   'code'      => $attribute['attribute_code'],
        	   'label'     => $attribute['label'],
        	   'options'   => array()
        	);

        	foreach ($attribute['values'] as $value) {
        		//$info['options'][$value['value_index']] = array(

        		if(!$this->_validateAttributeValue($attributeId, $value, $options)) {
        		    continue;
        		}

        		$info['options'][] = array(
        		    'id'    => $value['value_index'],
                    'label' => $value['label'],
                    'price' => $this->_preparePrice($value['pricing_value'], $value['is_percent']),
                    'products'   => isset($options[$attributeId][$value['value_index']]) ? $options[$attributeId][$value['value_index']] : array(),
        		);
        	}

        	if($this->_validateAttributeInfo($info)) {
        	   $attributes[$attribute['attribute_id']] = $info;
        	}
        }

        $config = array(
            'attributes'=> $attributes,
            'template'  => str_replace('%s', '#{price}', $store->getCurrentCurrency()->getOutputFormat()),
            'basePrice' => $this->_preparePrice($this->getProduct()->getFinalPrice()),
            'productId' => $this->getProduct()->getId(),
            'chooseText'=> Mage::helper('catalog')->__('Choose option...'),
        );

        return Zend_Json::encode($config);
    }

    /**
     * Validating of super product option value
     *
     * @param array $attribute
     * @param array $value
     * @param array $options
     * @return boolean
     */
    protected function _validateAttributeValue($attributeId, &$value, &$options)
    {
        if(isset($options[$attributeId][$value['value_index']])) {
            return true;
        }

        return false;
    }

    /**
     * Validation of super product option
     *
     * @param array $info
     * @return boolean
     */
    protected function _validateAttributeInfo(&$info)
    {
        if(count($info['options']) > 0) {
            return true;
        }

        return false;
    }

    protected function _preparePrice($price, $isPercent=false)
    {
        if ($isPercent) {
            $price = $this->getProduct()->getFinalPrice()*$price/100;
        }
        $price = Mage::app()->getStore()->convertPrice($price);
        $price = Zend_Locale_Format::toNumber($price, array('number_format'=>'##0.00'));
        return str_replace(',', '.', $price);
    }

    /**
     * REtrieve current product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }

 	/*public function getAttributes()
 	{
 		if($this->getRequest()->getParam('super_attribute') && is_array($this->getRequest()->getParam('super_attribute'))) {
 			foreach ($this->getRequest()->getParam('super_attribute') as $attributeId=>$attributeValue) {
 				if(!empty($attributeValue) && $attribute = Mage::registry('product')->getResource()->getAttribute($attributeId)) {
 					Mage::registry('product')->getSuperLinkCollection()
 						->addFieldToFilter($attribute->getAttributeCode(), $attributeValue);
 				}
 			}
 		}

 		return Mage::registry('product')->getSuperAttributes(false, true);
 	}*/

 	public function canDisplayContainer()
 	{
 		return !(bool)$this->getRequest()->getParam('ajax', false);
 	}

 	public function getPricingValue($value)
    {
    	$value = Mage::registry('product')->getPricingValue($value);
    	$numberSign = $value >= 0 ? '+' : '-';
    	return ' ' . $numberSign . ' ' . Mage::app()->getStore()->formatPrice(abs($value));
    }

    public function isSelectedOption($value, $attribute)
    {
    	$selected = $this->getRequest()->getParam('super_attribute', array());
    	if(is_array($selected) && isset($selected[$attribute['attribute_id']]) && $selected[$attribute['attribute_id']]==$value['value_index']) {
    		return true;
    	}

    	return false;
    }

    public function getUpdateUrl()
    {
    	return $this->getUrl('*/*/superConfig', array('_current'=>true));
    }

    public function getUpdatePriceUrl()
    {
    	return $this->getUrl('*/*/price', array('_current'=>true));
    }
 } // Class Mage_Catalog_Block_Product_View_Super_Config end