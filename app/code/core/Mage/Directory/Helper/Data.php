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
 * @package    Mage_Directory
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
/**
 * Directory data helper
 *
 */
class Mage_Directory_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_countryCollection;
    protected $_regionCollection;
    protected $_regionJson;
    
    public function getRegionCollection()
    {
        if (!$this->_regionCollection) {
            $this->_regionCollection = Mage::getModel('directory/region')->getResourceCollection()
                ->addCountryFilter($this->getAddress()->getCountryId())
                ->load();
        }
        return $this->_regionCollection;
    }
    
    public function getCountryCollection()
    {
        if (!$this->_countryCollection) {
            $this->_countryCollection = Mage::getModel('directory/country')->getResourceCollection()
                ->loadByStore();
        }
        return $this->_countryCollection;
    }
    
    /**
     * Retrieve regions data json
     *
     * @return string
     */
    public function getRegionJson()
    {
    	if (!$this->_regionJson) {
	    	$countryIds = array();
	    	foreach ($this->getCountryCollection() as $country) {
	    		$countryIds[] = $country->getCountryId();
	    	}
    		$collection = Mage::getModel('directory/region')->getResourceCollection()
    			->addCountryFilter($countryIds)
    			->load();
	    	$regions = array();
	    	foreach ($collection as $region) {
	    		if (!$region->getRegionId()) {
	    			continue;
	    		}
	    		$regions[$region->getCountryId()][$region->getRegionId()] = array(
	    			'code'=>$region->getCode(),
	    			'name'=>$region->getName()
	    		);
	    	}
	    	$this->_regionJson = Zend_Json::encode($regions);
    	}
    	return $this->_regionJson;
    }
}
