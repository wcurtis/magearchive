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


class Mage_Customer_Model_Convert_Parser_Customer
    extends Mage_Eav_Model_Convert_Parser_Abstract
{
    protected $_resource;

    /**
     * Product collections per store
     *
     * @var array
     */
    protected $_collections;

    /**
     * @return Mage_Catalog_Model_Mysql4_Convert
     */
    public function getResource()
    {
        if (!$this->_resource) {
            $this->_resource = Mage::getResourceSingleton('catalog_entity/convert');
                #->loadStores()
                #->loadProducts()
                #->loadAttributeSets()
                #->loadAttributeOptions();
        }
        return $this->_resource;
    }

    public function getCollection($storeId)
    {
        if (!isset($this->_collections[$storeId])) {
            $this->_collections[$storeId] = Mage::getResourceModel('customer/customer_collection');
            $this->_collections[$storeId]->getEntity()->setStore($storeId);
        }
        return $this->_collections[$storeId];
    }

    public function parse()
    {
        $data = $this->getData();

        $entityTypeId = Mage::getSingleton('eav/config')->getEntityType('customer')->getId();
        $result = array();
        foreach ($data as $i=>$row) {
            $this->setPosition('Line: '.($i+1));
            try {

                // validate SKU
                if (empty($row['email'])) {
                    $this->addException(Mage::helper('customer')->__('Missing email, skipping the record'), Varien_Convert_Exception::ERROR);
                    continue;
                }
                $this->setPosition('Line: '.($i+1).', email: '.$row['email']);

                // try to get entity_id by sku if not set
                /*
                if (empty($row['entity_id'])) {
                    $row['entity_id'] = $this->getResource()->getProductIdBySku($row['email']);
                }
                */

                // if attribute_set not set use default
                if (empty($row['attribute_set'])) {
                    $row['attribute_set'] = 'Default';
                }

                // get attribute_set_id, if not throw error
                $row['attribute_set_id'] = $this->getAttributeSetId($entityTypeId, $row['attribute_set']);
                if (!$row['attribute_set_id']) {
                    $this->addException(Mage::helper('customer')->__("Invalid attribute set specified, skipping the record"), Varien_Convert_Exception::ERROR);
                    continue;
                }

                if (empty($row['group'])) {
                    $row['group'] = 'General';
                }

                if (empty($row['firstname'])) {
                    $this->addException(Mage::helper('customer')->__('Missing firstname, skipping the record'), Varien_Convert_Exception::ERROR);
                    continue;
                }
                //$this->setPosition('Line: '.($i+1).', Firstname: '.$row['firstname']);

                if (empty($row['lastname'])) {
                    $this->addException(Mage::helper('customer')->__('Missing lastname, skipping the record'), Varien_Convert_Exception::ERROR);
                    continue;
                }
                //$this->setPosition('Line: '.($i+1).', Lastname: '.$row['lastname']);

                /*
                // get product type_id, if not throw error
                $row['type_id'] = $this->getProductTypeId($row['type']);
                if (!$row['type_id']) {
                    $this->addException(Mage::helper('catalog')->__("Invalid product type specified, skipping the record"), Varien_Convert_Exception::ERROR);
                    continue;
                }
                */

                // get store ids
                $storeIds = $this->getStoreIds(isset($row['store']) ? $row['store'] : $this->getVar('store'));
                if (!$storeIds) {
                    $this->addException(Mage::helper('customer')->__("Invalid store specified, skipping the record"), Varien_Convert_Exception::ERROR);
                    continue;
                }

                // import data
                $rowError = false;
                foreach ($storeIds as $storeId) {
                    $collection = $this->getCollection($storeId);
                    //print_r($collection);
                    $entity = $collection->getEntity();

                    $model = Mage::getModel('customer/customer');
                    $model->setStoreId($storeId);
                    if (!empty($row['entity_id'])) {
                        $model->load($row['entity_id']);
                    }
                    foreach ($row as $field=>$value) {
                        $attribute = $entity->getAttribute($field);
                        if (!$attribute) {
                            continue;
                            #$this->addException(Mage::helper('catalog')->__("Unknown attribute: %s", $field), Varien_Convert_Exception::ERROR);

                        }

                        if ($attribute->usesSource()) {
                            $source = $attribute->getSource();
                            $optionId = $this->getSourceOptionId($source, $value);
                            if (is_null($optionId)) {
                                $rowError = true;
                                $this->addException(Mage::helper('customer')->__("Invalid attribute option specified for attribute %s (%s), skipping the record", $field, $value), Varien_Convert_Exception::ERROR);
                                continue;
                            }
                            $value = $optionId;
                        }
                        $model->setData($field, $value);

                    }//foreach ($row as $field=>$value)


                    $billingAddress = $model->getPrimaryBillingAddress();
                    $customer = Mage::getModel('customer/customer')->load($model->getId());


                    if (!$billingAddress  instanceof Mage_Customer_Model_Address) {
                        $billingAddress = new Mage_Customer_Model_Address();
                        if ($customer->getId() && $customer->getDefaultBilling()) {
                            $billingAddress->setId($customer->getDefaultBilling());
                        }
                    }

                    $regions = Mage::getResourceModel('directory/region_collection')->addRegionNameFilter($row['billing_region'])->load();
                    if ($regions) foreach($regions as $region) {
                       $regionId = $region->getId();
                    }

                    $billingAddress->setFirstname($row['firstname']);
                    $billingAddress->setLastname($row['lastname']);
                    $billingAddress->setCity($row['billing_city']);
                    $billingAddress->setRegion($row['billing_region']);
                    $billingAddress->setRegionId($regionId);
                    $billingAddress->setCountryId($row['billing_country']);
                    $billingAddress->setPostcode($row['billing_postcode']);
                    $billingAddress->setStreet(array($row['billing_street1'],$row['billing_street2']));
                    if (!empty($row['billing_telephone'])) {
                        $billingAddress->setTelephone($row['billing_telephone']);
                    }

                    if (!$model->getDefaultBilling()) {
                        $billingAddress->setCustomerId($model->getId());
                        $billingAddress->setIsDefaultBilling(true);
                        $billingAddress->save();
                        $model->setDefaultBilling($billingAddress->getId());
                        $model->addAddress($billingAddress);
                        if ($customer->getDefaultBilling()) {
                            $model->setDefaultBilling($customer->getDefaultBilling());
                        } else {
                            $shippingAddress->save();
                            $model->setDefaultShipping($billingAddress->getId());
                            $model->addAddress($billingAddress);

                        }
                    }

                    $shippingAddress = $model->getPrimaryShippingAddress();
                    if (!$shippingAddress instanceof Mage_Customer_Model_Address) {
                        $shippingAddress = new Mage_Customer_Model_Address();
                        if ($customer->getId() && $customer->getDefaultShipping()) {
                            $shippingAddress->setId($customer->getDefaultShipping());
                        }
                    }

                    $regions = Mage::getResourceModel('directory/region_collection')->addRegionNameFilter($row['shipping_region'])->load();
                    if ($regions) foreach($regions as $region) {
                       $regionId = $region->getId();
                    }

                    $shippingAddress->setFirstname($row['firstname']);
                    $shippingAddress->setLastname($row['lastname']);
                    $shippingAddress->setCity($row['shipping_city']);
                    $shippingAddress->setRegion($row['shipping_region']);
                    $shippingAddress->setRegionId($regionId);
                    $shippingAddress->setCountryId($row['shipping_country']);
                    $shippingAddress->setPostcode($row['shipping_postcode']);
                    $shippingAddress->setStreet(array($row['shipping_street1'], $row['shipping_street2']));
                    $shippingAddress->setCustomerId($model->getId());
                    if (!empty($row['shipping_telephone'])) {
                        $shippingAddress->setTelephone($row['shipping_telephone']);
                    }

                    if (!$model->getDefaultShipping()) {
                        if ($customer->getDefaultShipping()) {
                            $model->setDefaultShipping($customer->getDefaultShipping());
                        } else {
                            $shippingAddress->save();
                            $model->setDefaultShipping($shippingAddress->getId());
                            $model->addAddress($shippingAddress);

                        }
                        $shippingAddress->setIsDefaultShipping(true);
                    }

                    if (!$rowError) {
                        $collection->addItem($model);
                    }

                } //foreach ($storeIds as $storeId)

            } catch (Exception $e) {
                if (!$e instanceof Mage_Dataflow_Model_Convert_Exception) {
                    $this->addException(Mage::helper('customer')->__("Error during retrieval of option value: %s", $e->getMessage()), Mage_Dataflow_Model_Convert_Exception::FATAL);
                }
            }
        }
        $this->setData($this->_collections);
		return $this;
    }

    public function unparse()
    {
        $systemFields = array('store_id', 'attribute_set_id', 'entity_type_id', 'parent_id', 'created_at', 'updated_at', 'type_id','group_id', 'website_id', 'default_billing', 'default_shipping');
        $collections = $this->getData();
//        if ($collections instanceof Mage_Eav_Model_Entity_Collection_Abstract) {
//            $collections = array($collections->getEntity()->getStoreId()=>$collections);
//        } elseif (!is_array($collections)) {
//            $this->addException(Mage::helper('customer')->__("Array of Entity collections is expected"), Varien_Convert_Exception::FATAL);
//        }

//        foreach ($collections as $storeId=>$collection) {
//           if (!$collection instanceof Mage_Eav_Model_Entity_Collection_Abstract) {
//               $this->addException(Mage::helper('customer')->__("Entity collection is expected"), Varien_Convert_Exception::FATAL);
//            }

            $data = array();

            foreach ($collections->getIterator() as $i=>$model) {
                $this->setPosition('Line: '.($i+1).', Email: '.$model->getEmail());

                // Will be removed after confirmation from Dima or Moshe
                $row = array(
                    'store_view'=>$this->getStoreCode($this->getVar('store') ? $this->getVar('store') : $storeId),
                ); // End

                foreach ($model->getData() as $field=>$value) {
                    // set website_id
                    if ($field == 'website_id') {
                      $row['website_code'] = Mage::getModel('core/website')->load($value)->getCode();
                      continue;
                    } // end

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
                            $this->addException(Mage::helper('customer')->__("Invalid option id specified for %s (%s), skipping the record", $field, $value), Mage_Dataflow_Model_Convert_Exception::ERROR);
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
                        $row['billing_street1']     = $billingAddress->getStreet1();
                        $row['billing_street2']     = $billingAddress->getStreet2();
                        $row['billing_city']        = $billingAddress->getCity();
                        $row['billing_region']      = $billingAddress->getRegion();
                        $row['billing_country']     = $billingAddress->getCountry();
                        $row['billing_postcode']    = $billingAddress->getPostcode();
                        $row['billing_telephone']   = $billingAddress->getTelephone();
                    }

                    $shippingAddress = $model->getDefaultShippingAddress();
                    if($shippingAddress instanceof Mage_Customer_Model_Address){
                        $shippingAddress->explodeStreetAddress();
                        $row['shipping_street1']    = $shippingAddress->getStreet1();
                        $row['shipping_street2']    = $shippingAddress->getStreet2();
                        $row['shipping_city']       = $shippingAddress->getCity();
                        $row['shipping_region']     = $shippingAddress->getRegion();
                        $row['shipping_country']    = $shippingAddress->getCountry();
                        $row['shipping_postcode']   = $shippingAddress->getPostcode();
                        $row['shipping_telephone']  = $shippingAddress->getTelephone();
                    }

                    if($model->getGroupId()){
                        $group = Mage::getResourceModel('customer/group_collection')
                        ->addFilter('customer_group_id',$model->getGroupId())
                        ->load();
                        $row['group']=$group->getFirstItem()->getData('customer_group_code');
                    }
                }
                $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($model);
                if ($subscriber->getId()) {
                    $row['is_subscribed'] = $subscriber->getSubscriberStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED ? Mage_Customer_Model_Customer::SUBSCRIBED_YES : Mage_Customer_Model_Customer::SUBSCRIBED_NO;
                }
                if(!isset($row['created_in'])){
                    $row['created_in'] = 'Admin';
                }
                $data[] = $row;

            }
//       }
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