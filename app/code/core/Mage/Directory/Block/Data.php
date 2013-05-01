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
 * Directory data block
 *
 * @category   Mage
 * @package    Mage_Directory
 */
class Mage_Directory_Block_Data extends Mage_Core_Block_Template
{
    public function getLoadrRegionUrl()
    {
        return $this->getUrl('directory/json/childRegion');
    }

    public function getCountryCollection()
    {
        $collection = $this->getData('country_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('directory/country')->getResourceCollection()
                ->loadByStore();
            $this->setData('country_collection', $collection);
        }
        return $collection;
    }

    public function getCountryHtmlSelect($defValue=null, $name='country_id', $id='country', $title='Country')
    {
		if (is_null($defValue)) {
			$defValue = $this->getCountryId();
		}
        $html = $this->getLayout()->createBlock('core/html_select')
            ->setName($name)
            ->setId($id)
            ->setTitle(Mage::helper('directory')->__($title))
            ->setClass('validate-select')
            ->setValue($defValue)
            ->setOptions($this->getCountryCollection()->toOptionArray())
            ->getHtml();

        return $html;
    }

    public function getRegionCollection()
    {
        $collection = $this->getData('region_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('directory/region')->getResourceCollection()
                ->addCountryFilter($this->getCountryId())
                ->load();

            $this->setData('region_collection', $collection);
        }
        return $collection;
    }


    public function getRegionHtmlSelect()
    {
        return $this->getLayout()->createBlock('core/html_select')
            ->setName('region')
            ->setTitle(Mage::helper('directory')->__('State/Province'))
            ->setId('state')
            ->setClass('required-entry validate-state')
            ->setValue($this->getRegionId())
            ->setOptions($this->getRegionCollection()->toOptionArray())
            ->getHtml();
    }

    public function getCountryId()
    {
        $countryId = $this->getData('country_id');
        if (is_null($countryId)) {
            $countryId = Mage::getStoreConfig('general/country/default');
        }
        return $countryId;
    }

    public function getRegionsJs()
    {
    	$regionsJs = $this->getData('regions_js');
    	if (!$regionsJs) {
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
	    	$regionsJs = Zend_Json::encode($regions);
    	}
    	return $regionsJs;
    }
}
