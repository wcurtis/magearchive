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
class Mage_Page_Block_Html extends Mage_Core_Block_Template
{
    protected $_urls = array();
    protected $_title = '';
    
    public function __construct() 
    {
        parent::__construct();
        $this->_urls = array(
            'base'      => Mage::getBaseUrl(),
            'baseSecure'=> Mage::getBaseUrl(array('_secure'=>true)),
            'current'   => $this->getRequest()->getRequestUri()
        );
    }
    
    public function getBaseUrl()
    {
        return $this->_urls['base'];
    }

    public function getBaseSecureUrl()
    {
        return $this->_urls['baseSecure'];
    }

    public function getCurrentUrl()
    {
        return $this->_urls['current'];
    }
    
    public function setHeaderTitle($title)
    {
        $this->_title = $title;
        return $this;
    }
    
    public function getHeaderTitle()
    {
        return $this->_title;
    }
}
