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
 * Adminhtml dashboard tab bar abstract
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
 abstract class Mage_Adminhtml_Block_Dashboard_Tab_Bar_Abstract extends Mage_Adminhtml_Block_Widget
 {
    protected $_tabs;

    protected $_dataHelperName = null;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('dashboard/tab/bar.phtml');
    }

    /**
     * Add new tab to tab bar
     *
     * @param string $tabId
     * @param string $type
     * @param array $options
     * @return Mage_Adminhtml_Block_Dashboard_Tab_Abstract
     */
    public function addTab($tabId, $type, array $options=array())
    {
        $tab = $this->getTabByType($type);
        $tab->addData($options);
        $tab->setType($type);
        $tab->setId($tabId);
        $this->_tabs[] = $tab;
        $this->setChild($tabId, $tab);

        return $tab;
    }

    /**
     * Return tab with specified id
     *
     * @param string $tabId
     * @return Mage_Adminhtml_Block_Dashboard_Tab_Abstract
     */

    public function getTab($tabId)
    {
        return $this->getChild($tabId);
    }

    /**
     * Returns all tabs
     *
     * @return array
     */
    public function getTabs()
    {
        return $this->_tabs;
    }

    /**
     * Protected method for preparing of Data for tab bar
     *
     * @return Mage_Adminhtml_Block_Dashboard_Tab_Bar_Abstract
     */
    protected function _prepareData()
    {
        return $this;
    }

    /**
     * Protected method for configuration of tabs
     *
     * @return Mage_Adminhtml_Block_Dashboard_Tab_Bar_Abstract
     */
    protected function _configureTabs()
    {
        if($this->getDataHelperName()) {
            foreach ($this->getTabs() as $tab) {
                if(!$tab->getDataHelperName()) {
                    $tab->setDataHelperName($this->getDataHelperName());
                }
            }
        }

        return $this;
    }

    /**
     * Retun data helper name
     *
     * @return string
     */
    public  function getDataHelperName()
    {
           return $this->_dataHelperName;
    }

    /**
     * Set data helper name
     *
     * @param string $dataHelperName
     * @return Mage_Adminhtml_Block_Dashboard_Tab_Bar_Abstract
     */
    public  function setDataHelperName($dataHelperName)
    {
           $this->_dataHelperName = $dataHelperName;
           return $this;
    }

    /**
     * Protected method for initalization of tabs.
     *
     * @return Mage_Adminhtml_Block_Dashboard_Tab_Bar_Abstract
     */
    protected function _initTabs()
    {
        return $this;
    }

    /**
     * Layout initalization
     *
     * @return Mage_Adminhtml_Block_Dashboard_Tab_Bar_Abstract
     */
    protected function _prepareLayout()
    {
        $this->_prepareData()
            ->_initTabs()
            ->_configureTabs();
        return parent::_prepareLayout();
    }

    /**
     * Return block by type
     *
     * @param string $type
     * @return Mage_Adminhtml_Block_Dashboard_Tab_Abstract
     */
    public function getTabByType($type)
    {
        $block = '';

        switch ($type) {
            case "graph":
                $block = 'adminhtml/dashboard_tab_graph';
                break;

            case "grid":
            default:
                $block = 'adminhtml/dashboard_tab_grid';
                break;
        }

        // if custom tab bar block
        if(strpos($type, '/')!==false) {
            $block = $type;
        }

        return $this->getLayout()->createBlock($block);
    }

    /**
     * Return CSS class name for specified $tab.
     *
     * @param Mage_Adminhtml_Block_Dashboard_Tab_Bar_Abstract $tab
     * @return string
     */
    public function getTabClassName($tab)
    {
        return  $tab->getType()=='graph' ? 'graph' : 'tab';
    }

    /**
     * Return data collection of tab bar
     *
     * @return arra|Varien_Data_Collection|Mage_Core_Model_Entity_Collection_Absatract|Mage_Core_Model_Mysql4_Collection_Abstract
     */
    public function getCollection()
    {
        return $this->getDataHelper()->getCollection();
    }

    /**
     * Return instance of data helper
     *
     * @return Mage_Adminhtml_Helper_Dashboard_Abstract
     */
    public function getDataHelper()
    {
        return $this->helper($this->getDataHelperName());
    }

    /**
     * Return count of tabs
     *
     * @return int
     */
    public function getCountTabs()
    {
        return sizeof($this->_tabs);
    }
 } // Class Mage_Adminhtml_Block_Dashboard_Tab_Bar_Abstract end