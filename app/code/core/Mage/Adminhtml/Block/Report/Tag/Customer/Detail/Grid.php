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
 * Adminhtml tags detail for customer report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Report_Tag_Customer_Detail_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customers_grid');
    }

    protected function _prepareCollection()
    {

        $collection = Mage::getModel('tag/tag')
            ->getEntityCollection()
            ->addCustomerFilter($this->getRequest()->getParam('id'))
            ->addStatusFilter(Mage::getModel('tag/tag')->getApprovedStatus())
            ->setDescOrder('DESC')
            ->addStoresVisibility()
            ->setActiveFilter()
            ->addGroupByTag();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('name', array(
            'header'    =>Mage::helper('reports')->__('Product Name'),
            'sortable'  => false,
            'index'     =>'name'
        ));

        $this->addColumn('tag_name', array(
            'header'    =>Mage::helper('reports')->__('Tag Name'),
            'sortable'  => false,
            'index'     =>'tag_name'
        ));


         // Collection for stores filters
        if(!$collection = Mage::registry('stores_select_collection')) {
            $collection =  Mage::app()->getStore()->getResourceCollection()
                ->load();
            Mage::register('stores_select_collection', $collection);
        }

        $stores = array();
        foreach ($collection as $store) {
            $stores[$store->getId()] = $store->getName();
        }

        $this->addColumn('visible', array(
            'header'    =>Mage::helper('reports')->__('Visible In'),
            'sortable'  => false,
            'index'     =>'stores',
            'renderer'      => 'adminhtml/report_tag_grid_renderer_visible'
        ));

        $this->addColumn('added_in', array(
            'header'    =>Mage::helper('reports')->__('Submitted In'),
            'sortable'  => false,
            'index'     =>'store_id',
            'type'      => 'options',
            'options'    => $stores
        ));


        $this->addColumn('created_at', array(
            'header'    =>Mage::helper('reports')->__('Added'),
            'sortable'  => false,
            'width'     => '140px',
            'index'     =>'created_at'
        ));

        $this->setFilterVisibility(false);

        $this->addExportType('*/*/exportCustomerDetailCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportCustomerDetailXml', Mage::helper('reports')->__('XML'));

        return parent::_prepareColumns();
    }
}
