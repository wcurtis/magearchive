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
 * @package    Mage_Page
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Html page block
 *
 * @category   Mage
 * @package    Mage_Page
 */
class Mage_Page_Block_Html_Footer extends Mage_Core_Block_Template
{
    protected $_seolinks;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->initSeoLink();
    }
       
    public function setCopyright($copyright)
    {
        $this->_copyright = $copyright;
        return $this;
    }
    
    public function getCopyright()
    {
        if (!$this->_copyright) {
            $this->_copyright = $this->getDesignConfig('page/footer/copyright');
        }
            
        return $this->_copyright;
    }
    
    public function getSeoLink()
    {       
        return $this->_seolinks;   
    }
    
    public function setSeoLink(array $varName)
    {
        $this->_seolinks=$varName;
    } 
    
    public function addSeoLink(array $varName)
    {
        $this->_seolinks[]=$varName;
    } 
    
    public function hasSeoLinks()
    {
        return count($this->_seolinks);
    }  
    
    public function initSeoLink()
    {
        if(Mage::getStoreConfig('catalog/seo/site_map')){
            $seolink['title']=$this->__('Site Map');
            $seolink['url']=$this->helper('catalog/map')->getCategoryUrl();
            $this->_seolinks[]=$seolink;            
        }
        if(Mage::getStoreConfig('catalog/seo/search_terms')){
            $seolink['title']=$this->__('Search Terms');
            $seolink['url']=$this->helper('catalogSearch/data')->getSearchTermUrl();
            $this->_seolinks[]=$seolink;            
        }     
    }
}
