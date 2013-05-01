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
 * @package    Mage_CatalogSearch
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product search result block
 *
 * @category   Mage
 * @package    Mage_CatalogSearch
 * @module     Catalog
 */
class Mage_CatalogSearch_Block_Result extends Mage_Core_Block_Template
{
    protected $_productCollection;

    protected function _getQuery()
    {
        return $this->helper('catalogSearch')->getQuery();
    }

    protected function _prepareLayout()
    {
        // add Home breadcrumb
        $this->getLayout()->getBlock('breadcrumbs')
            ->addCrumb('home',
                array('label'=>Mage::helper('catalogsearch')->__('Home'),
                    'title'=>Mage::helper('catalogsearch')->__('Go to Home Page'),
                    'link'=>Mage::getBaseUrl())
                );

        $title = Mage::helper('catalogsearch')->__("Search results for: '%s'", $this->helper('catalogSearch')->getEscapedQueryText());
        $this->getLayout()->getBlock('head')->setTitle($title);

        $resultBlock = $this->getLayout()->createBlock('catalog/product_list', 'product_list')
            ->setAvailableOrders(array('name'=>Mage::helper('catalogsearch')->__('Name'), 'price'=>Mage::helper('catalogsearch')->__('Price')))
            ->setModes(array('grid'=>Mage::helper('catalogsearch')->__('Grid'), 'list' => Mage::helper('catalogsearch')->__('List')))
            ->setCollection($this->_getProductCollection());

        $this->setChild('search_result_list', $resultBlock);
        return parent::_prepareLayout();
    }

    public function getProductListHtml()
    {
        return $this->getChildHtml('search_result_list');
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $this->_productCollection = $this->_getQuery()->getResultCollection()
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image');

            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($this->_productCollection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($this->_productCollection);
        }

        return $this->_productCollection;
    }

    public function getResultCount()
    {
        if (!$this->getData('result_count')) {
            $size = $this->_getProductCollection()->getSize();
            $this->_getQuery()->setNumResults($size);
            $this->setResultCount($size);
        }
        return $this->getData('result_count');
    }
}
