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
 * Catalog product link model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */

class Mage_Catalog_Model_Product_Link extends Mage_Core_Model_Abstract
{
	protected $_attributeCollection = null;
	
	protected function _construct()
	{
		$this->_init('catalog/product_link');
	}
	
	public function getDataForSave() 
	{
		$data = array();
		$data['product_id'] = $this->getProductId();
		$data['linked_product_id'] = $this->getLinkedProductId();
		$data['link_type_id'] = $this->getLinkTypeId();
		return $data;
	}
	
	public function getAttributeCollection()
	{
		if(is_null($this->_attributeCollection))
		{
			$this->setAttributeCollection(
				Mage::getResourceModel('catalog/product_link_attribute_collection')
					->addFieldToFilter('link_type_id', $this->getLinkTypeId())
					->load()
			);
		}
		
		return $this->_attributeCollection;
	}
	
	public function setAttributeCollection($collection)
	{
		$this->_attributeCollection = $collection;
		return $this;
	}
	
	public function addLinkData($linkTypeId, $product, $linkedProductId) 
	{
		$this->setLinkTypeId($linkTypeId)
			->setProductId($product->getId())
			->setLinkedProductId($linkedProductId);
		
		return $this;
	}
		
}// Class Mage_Catalog_Model_Product_Link END
