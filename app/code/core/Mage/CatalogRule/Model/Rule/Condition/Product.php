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
 * @package    Mage_CatalogRule
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_CatalogRule_Model_Rule_Condition_Product extends Mage_Rule_Model_Condition_Abstract
{
    public function loadAttributeOptions()
    {
        $productAttributes = Mage::getResourceSingleton('catalog/product')
            ->loadAllAttributes()->getAttributesByCode();

        $attributes = array();
        foreach ($productAttributes as $attr) {
            if (!$attr->getIsVisible()) {
                continue;
            }
            $attributes[$attr->getAttributeCode()] = $attr->getFrontend()->getLabel();
        }

        $attributes['attribute_set_id'] = Mage::helper('catalogrule')->__('Attribute Set');

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    public function collectValidatedAttributes($productCollection)
    {
        $productCollection->addAttributeToSelect($this->getAttribute());
        return $this;
    }
}
