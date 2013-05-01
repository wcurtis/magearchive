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
 * @package    Mage_Tag
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * List of tagged products
 *
 * @category   Mage
 * @package    Mage_Tag
 */

class Mage_Tag_Block_Product_Result extends Mage_Core_Block_Template
{
    protected $_collection;

    public function __construct()
    {
        #$this->setTemplate('tag/product/result.phtml');

        $this->setTemplate('catalogsearch/result.phtml');
        $this->setTagId(Mage::registry('tagId'));
    }

    public function getTagInfo()
    {
        return Mage::getModel('tag/tag')->load($this->getTagId());
    }

    protected function _prepareLayout()
    {
        $title = $this->getHeaderText();
        $this->getLayout()->getBlock('head')->setTitle($title);
        $this->getLayout()->getBlock('root')->setHeaderTitle($title);

        $resultBlock = $this->getLayout()->createBlock('catalog/product_list', 'product_list')
            ->setAvailableOrders(array('name'=>Mage::helper('tag')->__('Name'), 'price'=>Mage::helper('tag')->__('Price')))
            ->setModes(array('list' => Mage::helper('tag')->__('List'), 'grid' => Mage::helper('tag')->__('Grid')))
            ->setCollection($this->_getCollection());
        $this->setChild('search_result_list', $resultBlock);
        return parent::_prepareLayout();
    }

    public function getProductListHtml()
    {
        return $this->getChildHtml('search_result_list');
    }

    protected function _getCollection()
    {
        if( !$this->_collection ) {
            $tagModel = Mage::getModel('tag/tag');
            $this->_collection = $tagModel->getEntityCollection()
                ->addTagFilter($this->getTagId())
                ->addStoreFilter(Mage::app()->getStore()->getId());
        }
        return $this->_collection;
    }

    public function _getProductCollection()
    {
        return $this->_getCollection();
    }

    protected function _beforeToHtml()
    {
        Mage::getModel('review/review')->appendSummary($this->_getCollection());
        return parent::_beforeToHtml();
    }

    public function getResultCount()
    {
        return $this->_getProductCollection()->getSize();
    }

    public function getHeaderText()
    {
        if( $this->getTagInfo()->getName() ) {
            return Mage::helper('tag')->__("Products tagged with '%s'", $this->getTagInfo()->getName());
        } else {
            return false;
        }
    }

    public function getSubheaderText()
    {
        return false;
    }

    public function getNoResultText()
    {
        return Mage::helper('tag')->__('No matches found.');
    }
}
