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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml tag all grid item renderer for item visibility
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Report_Tag_Grid_Renderer_Visible extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		return implode(", ", $this->_getStoresNames($row->getStores()));
	}

	protected function _getStoresNames($stores)
	{
		$collection = Mage::registry('stores_select_collection');
		$sharedNames = array();
		foreach($stores as $storeId) {
		    if($storeId != 0) {
			$sharedNames[] = $collection->getItemById($storeId)->getName();
		    }
		}
		return $sharedNames;
	}
}// Class Mage_Adminhtml_Block_Report_Tag_Grid_Renderer_Visible END