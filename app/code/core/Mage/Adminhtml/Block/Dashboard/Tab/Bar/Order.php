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
 * Dashboard orders block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Dashboard_Tab_Bar_Order extends Mage_Adminhtml_Block_Dashboard_Tab_Bar_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('dashboard_order');

    }

    protected function _prepareData()
    {
        $this->setDataHelperName('adminhtml/dashboard_order');
        $this->getDataHelper()->setParams($this->helper('adminhtml/dashboard_data')->getSectionData('order'));

        return parent::_prepareData();
    }

    protected function _initTabs()
    {
        // number of orders graph
        $this->addTab('orders_total', 'graph', array('horizontal_axis'=>'time','veritical_axis'=>'liniar','title'=>$this->__('Number Of Orders')))
            ->addSeries('total', array('x_field'=>'range', 'y_field'=>'amouth'));
        // total income graph
        $this->addTab('orders_income', 'graph', array('horizontal_axis'=>'time','veritical_axis'=>'liniar','title'=>$this->__('Total Income')))
            ->addSeries('revenue', array('x_field'=>'range', 'y_field'=>'revenue'));

        // orders summary grid
        $this->addTab('orders_summary',  'grid', array('title'=>$this->__('Summary')));

        return parent::_initTabs();
    }

    protected function _configureTabs()
    {
        // orders income axises configuration
        $store = Mage::app()->getStore($this->getDataHelper()->getParam('store'));
        $this->getTab('orders_income')->getVerticalAxis()->setTitle($this->__('Income'));
        $this->getTab('orders_income')->getVerticalAxis()->setCurrencyCode($store->getBaseCurrencyCode());
        $this->getTab('orders_income')->getHorizontalAxis()->setTitle($this->__('Timeline'));
        $this->getTab('orders_income')->getHorizontalAxis()->setFormatType($this->getDataHelper()->getParam('range'));


        // number of orders axises configuration
        $this->getTab('orders_total')->getVerticalAxis()->setTitle($this->__('Qty'));
        $this->getTab('orders_total')->getHorizontalAxis()->setTitle($this->__('Timeline'));
        $this->getTab('orders_total')->getHorizontalAxis()->setFormatType($this->getDataHelper()->getParam('range'));



        // init columns for orders summary grid
        $this->getTab('orders_summary')
            ->addColumn('range', array(
                'header'    =>  $this->__('Date'),
                'index'     =>  'range',
                'type'      =>  'datetime',
                'format'    =>  $this->getTab('orders_income')->getHorizontalAxis()->getFormat(true),
                'timezone'  =>  'GMT',
                'locale'    =>  Mage::app()->getLocale()->getLocaleCode()
            ))
            ->addColumn('revenue', array(
                'header'    =>  $this->__('Total Income'),
                'index'     =>  'revenue',
                'type'      =>  'currency',
                'currency_code' => $store->getBaseCurrencyCode()
            ))
            ->addColumn('qty', array(
                'width'     =>  154,
                'header'    =>  $this->__('Number Of Orders'),
                'index'     =>  'amouth'
            ));



        // Totals for summary
        $this->getTab('orders_summary')->addTotal('qty', $this->__('Number Of Orders'));
        $this->getTab('orders_summary')->addTotal('revenue', $this->__('Total Income'));
        $this->getTab('orders_summary')->addTotal('qty', $this->__('Average Number Of Orders'), true, true);
        $this->getTab('orders_summary')->addTotal('revenue', $this->__('Average Income'), true);

        return parent::_configureTabs();
    }

} // Class Mage_Adminhtml_Block_Dashboard_Tab_Bar_Order End