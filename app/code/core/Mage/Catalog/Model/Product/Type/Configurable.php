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
 * Configurable product type implementation
 *
 * This type builds in product attributes and existing simple products
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Product_Type_Configurable extends Mage_Catalog_Model_Product_Type_Abstract
{
    /**
     * Attributes which used for configurable product
     *
     * @var array
     */
    protected $_usedProductAttributeIds = null;
    protected $_usedProductAttributes   = null;
    protected $_configurableAttributes  = null;
    protected $_usedProductIds  = null;
    protected $_usedProducts    = null;

    /**
     * Retrieve product type attributes
     *
     * @return array
     */
    public function getEditableAttributes()
    {
        if (is_null($this->_editableAttributes)) {
            $this->_editableAttributes = parent::getEditableAttributes();
            foreach ($this->_editableAttributes as $index => $attribute) {
                if ($this->getUsedProductAttributeIds()
                    && in_array($attribute->getAttributeId(), $this->getUsedProductAttributeIds())) {
                    unset($this->_editableAttributes[$index]);
                }
            }
        }
        return $this->_editableAttributes;
    }

    /**
     * Checkin attribute availability for create superproduct
     *
     * @param   Mage_Eav_Model_Entity_Attribute $attribute
     * @return  bool
     */
    public function canUseAttribute(Mage_Eav_Model_Entity_Attribute $attribute)
    {
        $allow = $attribute->getIsGlobal()
            && $attribute->getIsVisible()
            && $attribute->getIsConfigurable()
            && $attribute->usesSource();

        return $allow;
    }

    /**
     * Declare attribute identifiers used for asign subproducts
     *
     * @param   array $ids
     * @return  Mage_Catalog_Model_Product_Type_Configurable
     */
    public function setUsedProductAttributeIds($ids)
    {
        $this->_usedProductAttributes = array();
        $this->_configurableAttributes= array();

        foreach ($ids as $attributeId) {
            $this->_usedProductAttributes[] = $this->getAttributeById($attributeId);
            $this->_configurableAttributes[]= Mage::getModel('catalog/product_type_configurable_attribute')
                ->setProductAttribute($this->getAttributeById($attributeId));
        }
        $this->_usedProductAttributeIds = $ids;
        return $this;
    }

    /**
     * Retrieve identifiers of used product attributes
     *
     * @return array
     */
    public function getUsedProductAttributeIds()
    {
        if (is_null($this->_usedProductAttributeIds)) {
            $this->_usedProductAttributeIds = array();
            foreach ($this->getUsedProductAttributes() as $attribute) {
            	$this->_usedProductAttributeIds[] = $attribute->getId();
            }
        }
        return $this->_usedProductAttributeIds;
    }

    /**
     * Retrieve used product attributes
     *
     * @return array
     */
    public function getUsedProductAttributes()
    {
        if (is_null($this->_usedProductAttributes)) {
            $this->_usedProductAttributes = array();
            foreach ($this->getConfigurableAttributes() as $attribute) {
            	$this->_usedProductAttributes[] = $attribute->getProductAttribute();
            }
        }
        return $this->_usedProductAttributes;
    }

    /**
     * Retrieve configurable attrbutes data
     *
     * @return array
     */
    public function getConfigurableAttributes()
    {
        if (is_null($this->_configurableAttributes)) {
            $this->_configurableAttributes = array();
            $collection = $this->getConfigurableAttributeCollection()
                ->orderByPosition();
            foreach ($collection as $attribute) {
                $attribute->setProductAttribute($this->getAttributeById($attribute->getAttributeId()));
            	$this->_configurableAttributes[] = $attribute;
            }
        }
        return $this->_configurableAttributes;
    }

    public function getConfigurableAttributesAsArray()
    {
        $res = array();
        foreach ($this->getConfigurableAttributes() as $attribute) {
            $label = $attribute->getLabel() ? $attribute->getLabel() : $attribute->getProductAttribute()->getFrontendLabel();
        	$res[] = array(
        	   'id'            => $attribute->getId(),
        	   'label'         => $label,
        	   'position'      => $attribute->getPosition(),
        	   'values'        => $attribute->getPrices() ? $attribute->getPrices() : array(),
        	   'attribute_id'  => $attribute->getProductAttribute()->getId(),
        	   'attribute_code'=> $attribute->getProductAttribute()->getAttributeCode(),
        	   'frontend_label'=> $attribute->getProductAttribute()->getFrontendLabel(),
        	);
        }
        return $res;
    }

    public function getConfigurableAttributeCollection()
    {
        return Mage::getResourceModel('catalog/product_type_configurable_attribute_collection')
            ->setProductFilter($this->getProduct());
    }


    /**
     * Retrieve subproducts identifiers
     *
     * @return array
     */
    public function getUsedProductIds()
    {
        if (is_null($this->_usedProductIds)) {
            $this->_usedProductIds = array();
            foreach ($this->getUsedProducts() as $product) {
            	$this->_usedProductIds[] = $product->getId();
            }
        }
        return $this->_usedProductIds;
    }

    /**
     * Retrieve array of "subproducts"
     *
     * @return array
     */
    public function getUsedProducts($store=null)
    {
        if (is_null($this->_usedProducts)) {
            $this->_usedProducts = array();
            $collection = $this->getUsedProductCollection($store)
                ->addAttributeToSelect('*');
            foreach ($collection as $product) {
                $configurableSetings = array();
                foreach ($this->getUsedProductAttributes() as $attribute) {
                    $configurableSetings[] = array(
                        'attribute_id'  => $attribute->getId(),
                        'value_index'   => $product->getData($attribute->getAttributeCode()),
                        'label'         => $attribute->getFrontend()->getLabel()
                    );
                }
                $product->setConfigurableSettings($configurableSetings);
            	$this->_usedProducts[] = $product;
            }
        }
        return $this->_usedProducts;
    }

    /**
     * Retrieve related products collection
     *
     * @return unknown
     */
    public function getUsedProductCollection($store=null)
    {
        $collection = Mage::getResourceModel('catalog/product_type_configurable_product_collection')
            ->setProductFilter($this->getProduct());
        if ($store) {
        	$collection->addStoreFilter($store);
        }
        foreach ($this->getUsedProductAttributes() as $attribute) {
        	$collection->addAttributeToSelect($attribute->getId());
        }
        return $collection;
    }

    /**
     * Save configurable product depended data
     *
     * @return Mage_Catalog_Model_Product_Type_Configurable
     */
    public function save()
    {
        parent::save();
        /**
         * Save Attributes Information
         */
        if ($data = $this->getProduct()->getConfigurableAttributesData()) {
            foreach ($data as $attributeData) {
                $id = isset($attributeData['id']) ? $attributeData['id'] : null;
            	$attribute = Mage::getModel('catalog/product_type_configurable_attribute')
            	   ->setData($attributeData)
            	   ->setId($id)
            	   ->setStoreId($this->getProduct()->getStoreId())
            	   ->setProductId($this->getProduct()->getId())
            	   ->save();
            }
        }

        /**
         * Save product relations
         */
        if ($data = $this->getProduct()->getConfigurableProductsData()) {
            $productIds = array_keys($data);
            Mage::getResourceModel('catalog/product_type_configurable')
                ->saveProducts($this->getProduct()->getId(), $productIds);
        }
        return $this;
    }

    /**
     * Check is product available for sale
     *
     * @return bool
     */
    public function isSalable()
    {
        $salable = $this->getProduct()->getIsSalable();
        if (!is_null($salable)) {
            return $salable;
        }

        $salable = false;
        foreach ($this->getUsedProducts() as $product) {
        	$salable = $salable || $product->isSalable();
        }
        return $salable;
    }

    /**
     * Retrieve used product by attribute values
     *  $attrbutesInfo = array(
     *      $attributeId => $attributeValue
     *  )
     * @param   array $attrbutesInfo
     * @return
     */
    public function getProductByAttributes($attributesInfo)
    {
        foreach ($this->getUsedProducts() as $product) {
            $checkRes = true;
            foreach ($attributesInfo as $attributeId => $attributeValue) {
                $code = $this->getAttributeById($attributeId)->getAttributeCode();
                if ($product->getData($code) != $attributeValue) {
                    $checkRes = false;
                }
                if ($checkRes) {
                    return $product;
                }
            }
        }
        return null;
    }
}
