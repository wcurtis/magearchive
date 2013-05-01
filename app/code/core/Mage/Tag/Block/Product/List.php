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

class Mage_Tag_Block_Product_List extends Mage_Core_Block_Template
{
	protected $_collection;

    public function __construct()
    {
        parent::__construct();
    }

    public function getCount()
    {
        return count($this->getTags());
    }

    public function getTags()
    {
        return $this->_getCollection()->getItems();
    }

    public function getFormAction()
    {
        return Mage::getUrl('tag/index/save', $this->_getPathArray());
    }

    protected function _getCollection()
    {
        if( !$this->_collection && $this->getProductId() ) {

            $model = Mage::getModel('tag/tag');
            $this->_collection = $model->getResourceCollection()
                ->addPopularity()
                ->addStatusFilter($model->getApprovedStatus())
                ->addProductFilter($this->getProductId())
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->setActiveFilter()
                ->load();
        }
        return $this->_collection;
    }

    protected function _checkPath()
    {
        if (!$this->getProductId()) {
            $currentProduct = Mage::registry('current_product');
            if ($currentProduct instanceof Mage_Catalog_Model_Product){
                $this->setProductId($currentProduct->getId());
            }
        }

        if (!$this->getCategoryId()) {
            $currentCategory = Mage::registry('current_category');
            if ($currentCategory instanceof Mage_Catalog_Model_Category){
                $this->setCategoryId($currentCategory->getId());
            }
        }
    }

    protected function _getPathArray()
    {
        $pathArray = array();

        if ($this->getProductId()) {
            $pathArray['product'] = $this->getProductId();
        }

        if ($this->getCategoryId()) {
            $pathArray['category'] = $this->getCategoryId();
        }

        return $pathArray;
    }

    protected function _beforeToHtml()
    {
        $this->_checkPath();

        if (!$this->getProductId()) {
            return false;
        }

        return parent::_beforeToHtml();
    }
}