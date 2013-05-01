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


class Mage_Customer_Model_Convert_Adapter_Customer extends Mage_Eav_Model_Convert_Adapter_Entity
{
    public function __construct()
    {
        $this->setVar('entity_type', 'customer/customer');
    }

    public function load()
    {
        $addressType = $this->getVar('filter/adressType');
        if($addressType=='both'){
           $addressType = array('default_billing','default_shipping');
        }
        $attrFilterArray = array();
        $attrFilterArray ['firstname'] = 'like';
        $attrFilterArray ['lastname'] = 'like';
        $attrFilterArray ['email'] = 'like';
        $attrFilterArray ['group'] = 'eq';
        $attrFilterArray ['customer_address/telephone'] = array('type'=>'like','bind'=>$addressType);
        $attrFilterArray ['customer_address/postcode'] = array('type'=>'like','bind'=>$addressType);
        $attrFilterArray ['customer_address/country'] = array('type'=>'eq','bind'=>$addressType);
        $attrFilterArray ['customer_address/region'] = array('type'=>'like','bind'=>$addressType);
        $attrFilterArray ['created_at'] = 'dateFromTo';
        
        $attrToDb = array(
            'group'=>'group_id',
            'customer_address/country'=>'customer_address/country_id',
         );
        
        parent::setFilter($attrFilterArray,$attrToDb);
        parent::load();
    }
}