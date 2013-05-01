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


abstract class Mage_Shipping_Model_Carrier_Abstract extends Varien_Object
{
    protected $_rates = null;


    abstract public function collectRates(Mage_Shipping_Model_Rate_Request $request);


    public function checkAvailableShipCountries(Mage_Shipping_Model_Rate_Request $request)
    {
        $speCountriesAllow=Mage::getStoreConfig('carriers/'.$request->getCarrier().'/sallowspecific');
        /*
        * for specific countries, the flag will be 1
        */
        if($speCountriesAllow && $speCountriesAllow==1){
             $availableCountries=explode(',',Mage::getStoreConfig('carriers/'.$request->getCarrier().'/specificcountry'));
             if(!in_array($request->getDestCountryId(), $availableCountries)){
                 if(Mage::getStoreConfig('carriers/'.$request->getCarrier().'/showmethod')){
                   $error = Mage::getModel('shipping/rate_result_error');
                   $error->setCarrier($request->getCarrier());
                   $error->setCarrierTitle(Mage::getStoreConfig('carriers/'.$request->getCarrier().'/title'));
                   $errorMsg=Mage::getStoreConfig('carriers/'.$request->getCarrier().'/specificerrmsg');
                   $error->setErrorMessage($errorMsg?$errorMsg:Mage::helper('shipping')->__('The shipping module is not available for selected delivery country'));
                   return $error;
                 }else{
                    /*
                    * The admin set not to show the shipping module if the devliery country is not within specific countries
                    */
                    return false;
                 }
             }
        }
        return $this;
    }
    public function isTrackingAvailable()
    {
        return false;
    }

    public function getSortOrder()
    {
        return $this->_data['sort_order'];
    }
}