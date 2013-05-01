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


class Mage_Customer_Model_Entity_Setup extends Mage_Eav_Model_Entity_Setup
{
    public function getDefaultEntities()
    {
        return array(
            'customer'=>array(
                'table'=>'customer/entity',
                'increment_model'=>'eav/entity_increment_numeric',
                'increment_per_store'=>false,
                'attributes' => array(
                    'store_id' => array(
                        'type'=>'static',
                        'label'=>'Create In',
                        'input'=>'select',
                        'source'=>'customer_entity/customer_attribute_source_store',
                        'backend'=>'customer_entity/customer_attribute_backend_store',
                        'sort_order'=>1,
                    ),
                    'created_in' => array(
                        'type'=>'int',
                        'label'=>'Created From',
                        'input'=>'select',
                        'source'=>'customer_entity/customer_attribute_source_store',
                        'sort_order'=>2,
                    ),
                    'firstname' => array(
                        'label'=>'First Name',
                        'sort_order'=>3,
                    ),
                    'lastname' => array(
                        'label'=>'Last Name',
                        'sort_order'=>4,
                    ),
                    'email' => array(
                        'label'=>'Email',
                        'class'=>'validate-email',
                        'sort_order'=>5,
                    ),
                    'password_hash' => array(
                        'input'=>'hidden', 
                        'backend'=>'customer_entity/customer_attribute_backend_password', 
                        'required'=>false,
                    ),
                    'group_id' => array(
                        'type'=>'int', 
                        'input'=>'select', 
                        'label'=>'Customer Group',
                        'source'=>'customer_entity/customer_attribute_source_group',
                        'sort_order'=>6,
                    ),
                    'default_billing' => array(
                        'type'=>'int', 
                        'visible'=>false, 
                        'required'=>false, 
                        'backend'=>'customer_entity/customer_attribute_backend_billing',
                    ),
                    'default_shipping' => array(
                        'type'=>'int', 
                        'visible'=>false, 
                        'required'=>false, 
                        'backend'=>'customer_entity/customer_attribute_backend_shipping',
                    ),
                ),
            ),
            
            'customer_address'=>array(
                'table'=>'customer/address_entity',
                'attributes' => array(
                    'firstname' => array(
                        'label'=>'First Name',
                        'sort_order'=>1,
                    ),
                    'lastname' => array(
                        'label'=>'Last Name',
                        'sort_order'=>2,
                    ),
                    'company' => array(
                        'label'=>'Company',
                        'required'=>false,
                        'sort_order'=>3,
                    ),
                    'street' => array(
                        'type'=>'text', 
                        'backend'=>'customer_entity/address_attribute_backend_street', 
                        'input'=>'multiline', 
                        'label'=>'Street Address',
                        'sort_order'=>4,
                    ),
                    'city' => array(
                        'label'=>'City',
                        'sort_order'=>5,
                    ),
                    'country_id' => array(
                        'type'=>'varchar', 
                        'input'=>'select', 
                        'label'=>'Country',
                        'class'=>'countries input-text', 
                        'source'=>'customer_entity/address_attribute_source_country',
                        'sort_order'=>6,
                    ),
                    'region' => array(
                        'backend'=>'customer_entity/address_attribute_backend_region', 
                        'label'=>'State/Province',
                        'class'=>'regions',
                        'sort_order'=>7,
                    ),
                    'region_id' => array(
                        'type'=>'int', 
                        'input'=>'hidden', 
                        'source'=>'customer_entity/address_attribute_source_region', 
                        'required'=>'false',
                        'sort_order'=>8,
                    ),
                    'postcode' => array(
                        'label'=>'Zip/Postal Code',
                        'sort_order'=>9,
                    ),
                    'telephone' => array(
                        'label'=>'Telephone',
                        'sort_order'=>10,
                    ),
                    'fax' => array(
                        'label'=>'Fax',
                        'required'=>false,
                        'sort_order'=>11,
                    ),
                ),
            ),
            
            'customer_payment'=>array(
                'table'=>'customer/entity',
                'attributes' => array(
                    'method_type'=>array('type'=>'int', 'input'=>'select', 'label'=>'Payment Method'),
                ),
            ),
        );
    }
}
