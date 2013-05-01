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
 * @package    Mage_Core
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Websites collection
 *
 * @category   Mage
 * @package    Mage_Core
 */
class Mage_Core_Model_Mysql4_Website_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract 
{
	protected $_loadDefault = false;
    
    protected function _construct() 
    {
        $this->_init('core/website');
    }
    
    public function setLoadDefault($loadDefault)
    {
    	$this->_loadDefault = $loadDefault;
    	return $this;
    }
    
    public function getLoadDefault()
    {
    	return $this->_loadDefault;
    }
    
    public function toOptionArray()
    {
        return $this->_toOptionArray('website_id', 'name');
    }
    
    public function load($printQuery = false, $logQuery = false)
    {
    	if (!$this->getLoadDefault()) {
    		$this->getSelect()->where($this->getConnection()->quoteInto('main_table.website_id>?', 0));
    	}
    	parent::load($printQuery, $logQuery);
    	return $this;
    }
}