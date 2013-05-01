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
 * Catalog category helper
 *
 */
class Mage_Catalog_Helper_Category extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve category children nodes
     *
     * @param   int $parent
     * @param   int $maxChildLevel
     * @return  Varien_Data_Tree_Node_Collection
     */
    protected function _getChildCategories($parent, $maxChildLevel=1)
    {
        $collection = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('is_active');

        $tree = Mage::getResourceModel('catalog/category_tree');

        $nodes = $tree->loadNode($parent)
            ->loadChildren($maxChildLevel-1)
            ->getChildren();
        $tree->addCollectionData($collection);

        return $nodes;
    }

    /**
     * Retrieve current store categories
     *
     * @param   int $maxChildLevel
     * @return  Varien_Data_Tree_Node_Collection
     */
    public function getStoreCategories($maxChildLevel=1)
    {
        $parent = Mage::app()->getStore()->getConfig('catalog/category/root_id');
        return $this->_getChildCategories($parent, $maxChildLevel);
    }
    
    /**
     * Retrieve category url
     *
     * @param   Mage_Catalog_Model_Category $category
     * @return  string
     */
    public function getCategoryUrl($category)
    {
        return Mage::getModel('catalog/category')
			->setData($category->getData())
			->getCategoryUrl();
    }
}