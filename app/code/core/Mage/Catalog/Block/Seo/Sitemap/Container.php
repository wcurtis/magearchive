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
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Site Map category block
 *
 * @category   Mage
 * @package    Mage_Catalog 
 */

class Mage_Catalog_Block_Seo_Sitemap_Container extends Mage_Core_Block_Template 
{
    protected $_activeTab;
    protected $_availableTabs;
    
    public function __construct()
    {
        parent::__construct();
        $this->_availableTabs=array(
            'product' =>  array(
                'title' =>  $this->__('Products'),
                'block' =>  'catalog/seo_sitemap_product',    
                'linktitle' => $this->__('Products Sitemap'),
                'linkurl' =>  Mage::helper('catalog/map')->getProductUrl(),                   
            ),
            'category'    =>  array(
                'title' =>  $this->__('Categories'),
                'block' =>  'catalog/seo_sitemap_category',  
                'linktitle' => $this->__('Categories Sitemap'),
                'linkurl' => Mage::helper('catalog/map')->getCategoryUrl(),   
            ),
        );
    }
    
    public function setAvailableTab(array $tabs)
    {
        $this->_availableTabs = $tabs;
    }  
    
    
    public function getAvailableTab()
    {
        return $this->_availableTabs;
    }
    
    public function setActiveTab($varName)
    {
        $this->_activeTab=$varName;
    }
    
    public function getActiveTab()
    {
        return $this->_activeTab;
    }
    
    public function getSeoActiveTabHtml()
    {
        $tabName = $this->getActiveTab();
        if (isset($this->_availableTabs[$tabName])) {    
            $tabBlock = $this->getLayout()->createBlock($this->_availableTabs[$tabName]['block'])->setTitle($this->getActiveTabTitle());
            $this->setChild('sitemap_active_tab', $tabBlock);
        }
        return $this->getChildHtml('sitemap_active_tab');
    }  
    
    public function getActiveTabTitle()
    {
         $tabName = $this->getActiveTab();
         return $this->_availableTabs[$tabName]['title'];         
    }
    
    
}