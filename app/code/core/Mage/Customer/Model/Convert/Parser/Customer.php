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
 * @package    Mage_Customer
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Customer_Model_Convert_Parser_Customer extends Mage_Eav_Model_Convert_Parser_Abstract
{
    public function parse()
    {
		return $this;
    }

    public function unparse()
    {
        $systemFields = array('store_id', 'attribute_set_id', 'entity_type_id', 'parent_id', 'created_at', 'updated_at', 'type_id','group_id');
        $collections = $this->getData();
        if ($collections instanceof Mage_Eav_Model_Entity_Collection_Abstract) {
            $collections = array($collections->getEntity()->getStoreId()=>$collections);
        } elseif (!is_array($collections)) {
            $this->addException(Mage::helper('customer')->__("Array of Entity collections is expected"), Varien_Convert_Exception::FATAL);
        }

        foreach ($collections as $storeId=>$collection) {
            if (!$collection instanceof Mage_Eav_Model_Entity_Collection_Abstract) {
                $this->addException(Mage::helper('customer')->__("Entity collection is expected"), Varien_Convert_Exception::FATAL);
            }

            $data = array();
            foreach ($collection->getIterator() as $i=>$model) {
                $this->setPosition('Line: '.($i+1).', SKU: '.$model->getSku());
                $row = array(
                    'store'=>$this->getStoreCode($this->getVar('store') ? $this->getVar('store') : $storeId),
                );
                foreach ($model->getData() as $field=>$value) {
                    if (in_array($field, $systemFields)) {
                        continue;
                    }
                    $attribute = $model->getResource()->getAttribute($field);
                    if (!$attribute) {
                        continue;
                    }

                    if ($attribute->usesSource()) {
                        $option = $attribute->getSource()->getOptionText($value);
                        if (false===$option) {
                            $this->addException(Mage::helper('customer')->__("Invalid option id specified for %s (%s), skipping the record", $field, $value), Varien_Convert_Exception::ERROR);
                            continue;
                        }
                        if (is_array($option)) {
                            $value = $option['label'];
                        } else {
                            $value = $option;
                        }
                    }
                    $row[$field] = $value;
                    $billingAddress = $model->getDefaultBillingAddress();
                    if($billingAddress instanceof Mage_Customer_Model_Address){
                        $billingAddress->explodeStreetAddress();
                        $row['billing_street1']=$billingAddress->getStreet1();
                        $row['billing_street2']=$billingAddress->getStreet2();
                        $row['billing_city']=$billingAddress->getCity();
                        $row['billing_region']=$billingAddress->getRegion();
                        $row['billing_country']=$billingAddress->getCountry();
                        $row['billing_postcode']=$billingAddress->getPostcode();
                    }
                    $shippingAddress = $model->getDefaultShippingAddress();
                    if($shippingAddress instanceof Mage_Customer_Model_Address){
                        $shippingAddress->explodeStreetAddress();
                        $row['shipping_street1']=$shippingAddress->getStreet1();
                        $row['shipping_street2']=$shippingAddress->getStreet2();
                        $row['shipping_city']=$shippingAddress->getCity();
                        $row['shipping_region']=$shippingAddress->getRegion();
                        $row['shipping_country']=$shippingAddress->getCountry();
                        $row['shipping_postcode']=$shippingAddress->getPostcode();
                    }

                    if($model->getGroupId()){
                        $group = Mage::getResourceModel('customer/group_collection')
                        ->addFilter('customer_group_id',$model->getGroupId())
                        ->load();
                        $row['group']=$group->getFirstItem()->getData('customer_group_code');
                    }
                }
                if(!isset($row['created_in'])){
                    $row['created_in'] = 'Admin';
                }
                $data[] = $row;

            }
        }
        $this->setData($data);
        return $this;
    }

    public function getExternalAttributes()
    {
        $internal = array('store_id', 'created_in', 'default_billing', 'default_shipping', 'country_id');

        $entityTypeId = Mage::getSingleton('eav/config')->getEntityType('customer')->getId();
        $customerAttributes = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($entityTypeId)
            ->load()->getIterator();

        $entityTypeId = Mage::getSingleton('eav/config')->getEntityType('customer_address')->getId();
        $addressAttributes = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($entityTypeId)
            ->load()->getIterator();

        $attributes = array(
            'store'=>'store',
            'entity_id'=>'entity_id',
            'group'=>'group',
        );

        foreach ($customerAttributes as $attr) {
            $code = $attr->getAttributeCode();
            if (in_array($code, $internal) || $attr->getFrontendInput()=='hidden') {
                continue;
            }
            $attributes[$code] = $code;
        }
        $attributes['password_hash'] = 'password_hash';

        foreach ($addressAttributes as $attr) {
            $code = $attr->getAttributeCode();
            if (in_array($code, $internal) || $attr->getFrontendInput()=='hidden') {
                continue;
            }
            $attributes['billing_'.$code] = 'billing_'.$code;
        }
        $attributes['billing_country'] = 'billing_country';

        foreach ($addressAttributes as $attr) {
            $code = $attr->getAttributeCode();
            if (in_array($code, $internal) || $attr->getFrontendInput()=='hidden') {
                continue;
            }
            $attributes['shipping_'.$code] = 'shipping_'.$code;
        }
        $attributes['shipping_country'] = 'shipping_country';

        return $attributes;
    }
}