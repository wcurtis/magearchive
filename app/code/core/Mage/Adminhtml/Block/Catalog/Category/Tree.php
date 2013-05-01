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
 * Categories tree block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Category_Tree extends Mage_Adminhtml_Block_Template
{
    protected $_withProductCount;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/category/tree.phtml');
        $this->_withProductCount = true;
    }

    protected function _prepareLayout()
    {
        $url = $this->getUrl('*/*/add', array(
            '_current'=>true,
            'parent'=>$this->getCategoryId(),
            'id'=>null,
        ));
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Add New'),
                    'onclick'   => "setLocation('".$url."')",
                    'class' => 'add'
                ))
        );

        $this->setChild('store_switcher',
            $this->getLayout()->createBlock('adminhtml/store_switcher')
                ->setSwitchUrl($this->getUrl('*/*/*', array('store'=>null)))
        );
        return parent::_prepareLayout();
    }

    protected function _getDefaultStoreId()
    {
        return 0;
    }

    public function getCategoryCollection($storeId=null)
    {
        if (is_null($storeId)) {
            $storeId = $this->_getDefaultStoreId();
        }

        $collection = $this->getData('category_collection_'.$storeId);
        if (is_null($collection)) {
            $collection = Mage::getResourceModel('catalog/category_collection')
                ->addAttributeToSelect('name')
                ->setLoadProductCount($this->_withProductCount)
                ->setProductStoreId($this->getRequest()->getParam('store', $this->_getDefaultStoreId()));
            $collection->getEntity()
                ->setStore($storeId);
            $this->setData('category_collection_'.$storeId, $collection);
        }
        return $collection;
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

    public function getCategory()
    {
        return Mage::registry('category');
    }

    public function getCategoryId()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getId();
        }
        return 1;
    }

    public function getNodesUrl()
    {
        return $this->getUrl('*/catalog_category/jsonTree');
    }

    public function getEditUrl()
    {
        return $this->getUrl('*/catalog_category/edit', array('_current'=>true, 'id'=>null, 'parent'=>null));
    }

    public function getMoveUrl()
    {
        return $this->getUrl('*/catalog_category/move', array('store'=>$this->getRequest()->getParam('store')));
    }

    public function getRoot()
    {
        $root = $this->getData('root');
        if (is_null($root)) {
            $storeId = (int) $this->getRequest()->getParam('store');

            if ($storeId) {
                $store = Mage::app()->getStore($storeId);
                $rootId = $store->getRootCategoryId();
            }
            else {
                $rootId = 1;
            }

            $tree = Mage::getResourceSingleton('catalog/category_tree')
                ->load();

            $root = $tree->getNodeById($rootId);
            if ($root && $rootId != 1) {
                $root->setIsVisible(true);
            }
            elseif($root && $root->getId() == 1) {
                $root->setName(Mage::helper('catalog')->__('Root'));
            }

            $this->_addCategoryInfo($root);
            $this->setData('root', $root);
        }

        return $root;
    }

    public function getTreeJson()
    {
        $rootArray = $this->_getNodeJson($this->getRoot());
        $json = Zend_Json::encode(isset($rootArray['children']) ? $rootArray['children'] : array());
        return $json;
    }

    protected function _addCategoryInfo($node)
    {
        if ($node) {
            $children = $node->getAllChildNodes();

            $children[$node->getId()] = $node;

            $collection = $this->getCategoryCollection()
                ->addIdFilter(array_keys($children))
                ->load();
            foreach ($collection as $category) {
                $children[$category->getId()]->addData($category->getData());
            }
        }

        return $this;
    }

    protected function _getNodeJson($node, $level=0)
    {
        $item = array();
        $item['text']= $node->getName();
        if ($this->_withProductCount) {
             $item['text'].= ' ('.$node->getProductCount().')';
        }

        $rootForStores = Mage::getModel('core/store')->getCollection()->loadByCategoryIds(array($node->getEntityId()));

        $item['id']  = $node->getId();
        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        //$item['allowDrop'] = ($level<3) ? true : false;
        $item['allowDrop'] = true;
        // disallow drag if it's first level and category is root of a store
        $item['allowDrag'] = ($node->getLevel()==1 && $rootForStores) ? false : true;
        if ($node->hasChildren()) {
            $item['children'] = array();
            foreach ($node->getChildren() as $child) {
                $item['children'][] = $this->_getNodeJson($child, $level+1);
            }
        }
        return $item;
    }
}
