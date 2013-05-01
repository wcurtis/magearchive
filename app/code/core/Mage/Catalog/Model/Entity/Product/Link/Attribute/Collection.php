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
 * Catalog product link attributes collection
 *
 * @category   Mage
 * @package    Mage_Catalog
 */

class Mage_Catalog_Model_Entity_Product_Link_Attribute_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected $_filterAlias = array();
	
	protected function _construct()
	{
		$this->_init('catalog/product_link_attribute');
	}
	
	public function addLinkTypeData()
	{
		$this->getSelect()->join(array('link_type'=>$this->getTable('product_link_type')), 
    			    			 'main_table.link_type_id = link_type.link_type_id', array('code AS link_type'));
    	$this->_filterAlias['link_type'] = 'link_type.code';
	}
	
	public function getItemByCodeAndLinkType($code, $linkType=null)
	{
		foreach ($this->getItems() as $item) {
			if ($item->getCode() == $code && is_null($linkType)) {
				return $item;
			} elseif ($item->getCode() == $code && $item->getLinkType() == $linkType) {
				return $item;
			}
		}
							
		return false;
	}
	
	public function getAttributeCodes() 
	{
		return $this->getColumnValues('product_link_attribute_code');
	}
	
	public function addFieldToFilter($field, $condition) {
		if(isset($this->_filterAlias[$field])) {
			$field = $this->_filterAlias[$field];
		}
		
		parent::addFieldToFilter($field, $condition);
		
		return $this;
	}
	
}// Class Mage_Catalog_Model_Entity_Product_Link_Attribute_Collection END