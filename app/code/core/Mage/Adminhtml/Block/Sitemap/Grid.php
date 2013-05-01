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
 * Adminhtml footer block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Sitemap_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sitemapId');
        $this->setDefaultSort('id');

    }


    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('sitemap/sitemap_collection')
            ->load();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    =>Mage::helper('sitemap')->__('ID'),
            'width'     =>'50px',
            'index'     =>'sitemap_id'
        ));
//        $this->addColumn('type', array(
//            'header'    =>Mage::helper('sitemap')->__('Type'),
//            'index'     =>'sitemap_type'
//        ));

        $this->addColumn('file', array(
            'header'    =>Mage::helper('sitemap')->__('Filename'),
            'index'     =>'sitemap_filename'
        ));

        $this->addColumn('path', array(
            'header'    =>Mage::helper('sitemap')->__('Path'),
            'index'     =>'sitemap_path'
        ));

        $this->addColumn('link', array(
            'header'    =>Mage::helper('sitemap')->__('Link for Google'),
            'index'     =>'concat(sitemap_path, sitemap_filename)',
            'renderer'  => 'adminhtml/sitemap_grid_renderer_link',
        ));

        $this->addColumn('time', array(
            'header'    =>Mage::helper('sitemap')->__('Last Time Generated'),
            'width'     =>'150px',
            'index'     =>'sitemap_time'
        ));


        $stores = Mage::getResourceModel('core/store_collection')->load()->toOptionHash();


        $this->addColumn('store_id', array(
            'header'=>Mage::helper('cms')->__('Store'),
            'index'=>'store_id',
            'type' => 'options',
            'options' => $stores,
        ));
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('sitemap')->__('Action'),
                'width'     => '100px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sitemap')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));
        $this->addExportType('*/*/exportCsv', Mage::helper('sitemap')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('sitemap')->__('XML'));
        return parent::_prepareColumns();
    }

}