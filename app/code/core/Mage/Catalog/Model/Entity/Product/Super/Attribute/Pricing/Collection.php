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
 * Catalog super product attribute pricing collection
 *
 * @category   Mage
 * @package    Mage_Catalog
 */

class Mage_Catalog_Model_Entity_Product_Super_Attribute_Pricing_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_optionJoined;
    protected function _construct()
    {
        $this->_init('catalog/product_super_attribute_pricing');
    }
    
    public function addLinksFilter(array $links) 
    {
        $condition = array();
        $this->getSelect()->join(
          array('attribute'=>$this->getTable('product_super_attribute')),
            'attribute.product_super_attribute_id = main_table.product_super_attribute_id', 
            array());
        
        foreach ($links as $link) {
            foreach ($link as $attribute) {
                $condition[] = '(' . $this->getConnection()->quoteInto('attribute.attribute_id = ?', $attribute['attribute_id']) 
                             . ' AND ' . $this->getConnection()->quoteInto('value_index = ?', $attribute['value_index']) . ')';
            }
        }
        if(sizeof($condition)==0) {
            $condition[] = '0';
        }
                        
        $this->getSelect()->where(new Zend_Db_Expr('(' . join(' OR ', $condition) . ')'))
            ->group('main_table.value_id');
    }
    
    public function load($printQuery=false, $logQuery=false)
    {
        if (!$this->_optionJoined) {
            $this->getSelect()->join(
               array('option_table'=>$this->getTable('eav/attribute_option')),
               'main_table.value_index=option_table.option_id'
            );
            $this->getSelect()->order('option_table.sort_order asc');
            $this->_optionJoined = true;
        }
        return parent::load($printQuery, $logQuery);
    }
}// Class Mage_Catalog_Model_Entity_Product_Super_Attribute_Pricing_Collection END