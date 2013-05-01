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
 * Dashboard Analitycs Block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Dashboard_Tab_Bar_Visitor extends Mage_Adminhtml_Block_Dashboard_Tab_Bar_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('dashboard_visitor');
    }

    protected function _prepareData()
    {
        $this->setDataHelperName('adminhtml/dashboard_visitor');
        $this->getDataHelper()->setParams($this->helper('adminhtml/dashboard_data')->getSectionData('visitor'));

        return parent::_prepareData();
    }

    protected function _initTabs()
    {
        // visits graph
        $this->addTab('visitors', 'graph', array('horizontal_axis'=>'time','veritical_axis'=>'liniar','title'=>$this->__('Site Visits')))
            ->addSeries('customers', array('x_field'=>'add_date', 'y_field'=>'customer_count'))
            ->addSeries('visitors', array('x_field'=>'add_date', 'y_field'=>'visitor_count'));

        // whos online
        $this->addTab('whos_online', 'adminhtml/dashboard_tab_visitor_totals', array('title'=>$this->__('Who\'s online')));
        return parent::_initTabs();
    }

    protected function _configureTabs()
    {
        // configuration of axises for visits graph
        $this->getTab('visitors')->getVerticalAxis()->setTitle($this->__('Visits'));
        $this->getTab('visitors')->getHorizontalAxis()->setTitle($this->__('Timeline'));
        $this->getTab('visitors')->getHorizontalAxis()->setFormatType($this->getDataHelper()->getParam('range'));

        return parent::_configureTabs();
    }

}
 // Class Mage_Adminhtml_Block_Dashboard_Tab_Bar_Visitor end