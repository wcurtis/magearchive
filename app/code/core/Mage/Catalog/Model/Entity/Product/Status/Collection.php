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
 * Product status collection resource model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */

class Mage_Catalog_Model_Entity_Product_Status_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract 
{
    public function _construct()
    {
        $this->_init('catalog/product_status');
    }

    public function toOptionArray()
    {
        return array(
            array('value' => Mage_Catalog_Model_Product::STATUS_ENABLED, 'label' => Mage::helper('catalog')->__('Enabled') ),
            array('value' => Mage_Catalog_Model_Product::STATUS_DISABLED, 'label' => Mage::helper('catalog')->__('Disabled') ),
        );
        //return $this->_toOptionArray('status_id', 'status_code');
    }

    public function toOptionHash()
    {
        return array(
            Mage_Catalog_Model_Product::STATUS_ENABLED => Mage::helper('catalog')->__('Enabled'),
            Mage_Catalog_Model_Product::STATUS_DISABLED=> Mage::helper('catalog')->__('Disabled'),
        );
        //return $this->_toOptionHash('status_id', 'status_code');
    }

}
