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
 * @package    Mage_Shipping
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shipping table rates
 *
 * @category   Mage
 * @package    Mage_Shipping
 */

class Mage_Shipping_Model_Mysql4_Carrier_Tablerate
{
    protected $_read;
    protected $_write;
    protected $_shipTable;
    protected $_usaPostcodeTable;
    
    public function __construct()
    {
        $this->_read = Mage::getSingleton('core/resource')->getConnection('shipping_read');
        $this->_write = Mage::getSingleton('core/resource')->getConnection('shipping_write');
        $this->_shipTable = Mage::getSingleton('core/resource')->getTableName('shipping/tablerate');
        $this->_usaPostcodeTable = Mage::getSingleton('core/resource')->getTableName('usa/postcode');
    }
    
    public function getRate(Mage_Shipping_Model_Rate_Request $request)
    {
        $select = $this->_read->select()->from($this->_shipTable);
        if (is_null($request->getDestCountryId()) && is_null($request->getDestRegionId())) {

            // assuming that request is coming from shopping cart
            // for shipping prices pre-estimation...

            // also probably it will be required to move this part to
            // Sales/Model/Quote/Address.php !

            $selectCountry = $this->_read->select()->from($this->_usaPostcodeTable, array('country_id', 'region_id'));
            $selectCountry->where('postcode=?', $request->getDestPostcode());
            $selectCountry->limit(1);
            $countryRegion = $this->_read->fetchRow($selectCountry);
            $region = $this->_read->quote($countryRegion['region_id']);
            $country = $this->_read->quote($countryRegion['country_id']);
        } else {
            $region = $this->_read->quote($request->getDestRegionId());
            $country = $this->_read->quote($request->getDestCountryId());
        }
        $zip = $this->_read->quote($request->getDestPostcode());
        $select->where("(dest_zip=$zip)
                     OR (dest_region_id=$region AND dest_zip='')
                     OR (dest_country_id=$country AND dest_region_id='0' AND dest_zip='')
                     OR (dest_country_id='0' AND dest_region_id='0' AND dest_zip='')");
        if (is_array($request->getConditionName())) {
            $i = 0;
            foreach ($request->getConditionName() as $conditionName) {
                if ($i == 0) {
                    $select->where('condition_name=?', $conditionName);
                } else {
                    $select->orWhere('condition_name=?', $conditionName);
                }
                $select->where('condition_value<=?', $request->getData($conditionName));
                $i++;
            }
        } else {
            $select->where('condition_name=?', $request->getConditionName());
            $select->where('condition_value<=?', $request->getData($request->getConditionName()));
        }
        $select->where('website_id=?', $request->getWebsiteId());
        $select->order('condition_value DESC')->limit(1);
        $row = $this->_read->fetchRow($select);
        return $row;
    }
}
