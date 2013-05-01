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
 * Catalog navigation
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Block_Navigation extends Mage_Core_Block_Template
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
        $parent = Mage::getStoreConfig('catalog/category/root_id');
        return $this->_getChildCategories($parent, $maxChildLevel);
    }

    /**
     * Retrieve child categories of current category
     *
     * @return Varien_Data_Tree_Node_Collection
     */
    public function getCurrentChildCategories()
    {
        $layer = Mage::getSingleton('catalog/layer');
        $category   = $layer->getCurrentCategory();
        $collection = Mage::getResourceModel('catalog/category_collection')
			->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor')
            ->addIdFilter($category->getChildren())
            ->load();

        $productCollection = Mage::getResourceModel('catalog/product_collection');
        $layer->prepareProductCollection($productCollection);
        $productCollection->addCountToCategories($collection);
        return $collection;
        /*$parent = $this->getRequest()->getParam('id');
        return $this->_getChildCategories($parent, 1);*/
    }

    /**
     * Checkin activity of category
     *
     * @param   Varien_Object $category
     * @return  bool
     */
    public function isCategoryActive($category)
    {
        return false;
    }

	public function getCategoryUrl($category)
	{
        if ($category instanceof Mage_Catalog_Model_Category) {
            $url = $category->getCategoryUrl();
        } else {
            $url = Mage::getModel('catalog/category')
                ->setData($category->getData())
			    ->getCategoryUrl();
        }
        return $url;
	}

    public function drawItem($category, $level=0, $last=false)
    {
        $html = '';
        if (!$category->getIsActive()) {

            return $html;
        }

        $children = $category->getChildren();
        $hasChildren = $children && $children->count();
        $html.= '<li';
        if ($hasChildren) {
             $html.= ' onmouseover="toggleMenu(this,1)" onmouseout="toggleMenu(this,0)"';
        }

        $html.= ' class="level'.$level;
        if ($this->isCategoryActive($category)) {
            $html.= ' active';
        }
        if ($last) {
            $html .= ' last';
        }
        if ($hasChildren) {
            $cnt = 0;
            foreach ($children as $child) {
                if ($child->getIsActive()) {
                    $cnt++;
                }
            }
        	$html .= ' parent';
        }
        $html.= '">'."\n";
        $html.= '<a href="'.$this->getCategoryUrl($category).'"><span>'.$category->getName().'</span></a>'."\n";
        //$html.= '<span>'.$level.'</span>';

        if ($hasChildren){

            $j = 0;
            $htmlChildren = '';
            foreach ($children as $child) {
            	$htmlChildren.= $this->drawItem($child, $level+1, ++$j >= $cnt);
            }

            if (!empty($htmlChildren)) {
            	$html.= '<ul class="level' . $level . '">'."\n"
            	        .$htmlChildren
            	        .'</ul>';
            }

        }
        $html.= '</li>'."\n";
        return $html;
    }
}
