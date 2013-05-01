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
 * Product categories tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Urlrewrite_Category_Tree extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    protected $_categoryIds;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('urlrewrite/product/categories.phtml');
    }

    protected function getCategoryIds()
    {
        if (is_null($this->_categoryIds)) {
            $this->_categoryIds = array();
            $product = Mage::registry('product');
            if ($product) {

	            $collection = $product->getCategoryCollection()
	                ->load();
	            foreach ($collection as $category) {
	            	$this->_categoryIds[] = $category->getId();
	            }

            }

        }
        return $this->_categoryIds;
    }

    public function getRootNode()
    {
        $root = parent::getRoot();
        if ($root && in_array($root->getId(), $this->getCategoryIds())) {
            $root->setChecked(true);
        }
        return $root;
    }

    protected function _getNodeJson($node, $level=1)
    {
        $item = parent::_getNodeJson($node, $level);
        if (in_array($node->getId(), $this->getCategoryIds()) || preg_match("/root/i",$item['text'])) {
            $item['checked'] = true;
        }
        return $item;
    }
}
