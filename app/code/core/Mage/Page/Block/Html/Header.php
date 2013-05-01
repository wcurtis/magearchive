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
class Mage_Page_Block_Html_Header extends Mage_Core_Block_Template
{
    public function __construct() 
    {
        parent::__construct();
        $this->setTemplate('page/html/header.phtml');
    }

    public function setLogo($logo_src, $logo_alt)
    {
        $this->_logo_src = $logo_src;
        $this->_logo_alt = $logo_alt;
        return $this;
    }
    
    public function getLogoSrc()
    {
        if (!$this->_logo_src) {
            $this->_logo_src = Mage::getStoreConfig('design/header/logo_src');
        }
        return $this->getSkinUrl($this->_logo_src);
    }

    public function getLogoAlt()
    {
        if (!$this->_logo_alt) {
            $this->_logo_alt = Mage::getStoreConfig('design/header/logo_alt');
        }
        return $this->_logo_alt;
    }
    
    public function setWelcome($welcome)
    {
        $this->_welcome = $welcome;
        return $this;
    }
    
    public function getWelcome()
    {
        if (Mage::app()->isInstalled() && !$this->_welcome && Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_welcome = $this->__('Welcome, %s!', Mage::getSingleton('customer/session')->getCustomer()->getName());
        }
        elseif (!$this->_welcome) {
            $this->_welcome = Mage::getStoreConfig('design/header/welcome');
        }
            
        return $this->_welcome;
    }

}
