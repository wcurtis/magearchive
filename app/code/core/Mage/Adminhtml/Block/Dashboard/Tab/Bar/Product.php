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
 * Dashboard products tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Dashboard_Tab_Bar_Product extends Mage_Adminhtml_Block_Dashboard_Tab_Bar_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('dashboard_product');

    }

    protected function _prepareData()
    {
        $this->setDataHelperName('adminhtml/dashboard_product');
        $this->getDataHelper()->setParams($this->helper('adminhtml/dashboard_data')->getSectionData('product'));

        return parent::_prepareData();
    }

    protected function _initTabs()
    {
        $this->addTab('products_avarage', 'graph', array('horizontal_axis'=>'category','veritical_axis'=>'liniar','title'=>$this->__('Average')))
            ->addSeries('average', array('x_field'=>'name', 'y_field'=>'salary'))
            ->addSeries('salary', array('x_field'=>'name', 'y_field'=>'avarage'));


        $this->getTab('products_avarage')->getVerticalAxis()->setTitle($this->__('Avarage'));
        $this->getTab('products_avarage')->getHorizontalAxis()->setTitle($this->__('Products'));

        $this->addTab('products_avarage_grid',  'grid', array('title'=>$this->__('Table')));


        // init columns for product tab
        $this->getTab('products_avarage_grid')
            ->addColumn('name', array(
                'header'=>$this->__('Name'),
                'index'=>'name'
            ))
            ->addColumn('average', array(
                'header'=>$this->__('Average'),
                'index'=>'avarage'
            ))
            ->addColumn('salary', array(
                'width'=>154,
                'header'=>$this->__('Salary'),
                'index'=>'salary',
                'type' => 'currency'
            ));

        $this->getTab('products_avarage_grid')->addTotal('average', 'Total Avarage');
        $this->getTab('products_avarage_grid')->addTotal('salary', 'Total Salary');

        return parent::_initTabs();
    }
} // Class Mage_Adminhtml_Block_Dashboard_Tab_Bar_Product end