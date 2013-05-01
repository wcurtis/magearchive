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


class Mage_Shipping_Model_Shipping
{
    /**
     * Default shipping orig for requests
     *
     * @var array
     */
    protected $_orig = null;

    /**
     * Cached result
     *
     * @var Mage_Sales_Model_Shipping_Method_Result
     */
    protected $_result = null;


    public function getResult()
    {
        if (empty($this->_result)) {
            $this->_result = Mage::getModel('shipping/rate_result');
        }
        return $this->_result;
    }

    /**
     * Set shipping orig data
     */
    public function setOrigData($data)
    {
        $this->_orig = $data;
    }

    /**
     * Reset cached result
     */
    public function resetResult()
    {
        $this->getResult()->reset();
        return $this;
    }

    /**
     * Retrieve configuration model
     *
     * @return Mage_Shipping_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('shipping/config');
    }

    /**
     * Retrieve all methods for supplied shipping data
     *
     * @todo make it ordered
     * @param Mage_Shipping_Model_Shipping_Method_Request $data
     * @return Mage_Shipping_Model_Shipping
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$request->getOrig()) {
            $request
                ->setCountryId(Mage::getStoreConfig('shipping/origin/country_id'))
                ->setRegionId(Mage::getStoreConfig('shipping/origin/region_id'))
                ->setPostcode(Mage::getStoreConfig('shipping/origin/postcode'))
            ;
        }

        if (!$request->getLimitCarrier()) {
            $carriers = Mage::getStoreConfig('carriers');

            foreach ($carriers as $carrierCode=>$carrierConfig) {
                if (!Mage::getStoreConfigFlag('carriers/'.$carrierCode.'/active')) {
                    continue;
                }
                $className = Mage::getStoreConfig('carriers/'.$carrierCode.'/model');
                if (!$className) {
                    continue;
                }
                $obj = Mage::getModel($className);
                if (!$obj) {
                    continue;
                }

                $request->setCarrier($carrierCode);
                $result=$obj->checkAvailableShipCountries($request);
                /*
                * Result will be false if the admin set not to show the shipping module
                * if the devliery country is not within specific countries
                */
                if($result){
                    if(!$result instanceof Mage_Shipping_Model_Rate_Result_Error){
                         $result = $obj->collectRates($request);
                    }
                    $this->getResult()->append($result);
                }
            }
        } else {
            $carrierConfig = Mage::getStoreConfig('carriers/'.$request->getLimitCarrier());
            if (!$carrierConfig) {
                return $this;
            }
            $className = Mage::getStoreConfig('carriers/'.$request->getLimitCarrier().'/model');
            $obj = Mage::getModel($className);
            $result=$obj->checkAvailableShipCountries($request);
            if(!$result instanceof Mage_Shipping_Model_Rate_Result_Error){
                 $result = $obj->collectRates($request);
            }
            $this->getResult()->append($result);
        }

        return $this;
    }

    public function collectRatesByAddress(Varien_Object $address)
    {
        $request = Mage::getModel('shipping/rate_request');
        $request->setDestCountryId($address->getCountryId());
        $request->setDestRegionId($address->getRegionId());
        $request->setDestPostcode($address->getPostcode());
        $request->setPackageValue($address->getSubtotal());
        $request->setPackageWeight($address->getWeight());
        $request->setPackageQty($address->getItemQty());
        $request->setStoreId(Mage::app()->getStore()->getId());
        $request->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
        $request->setBaseCurrency(Mage::app()->getStore()->getBaseCurrency());
        $request->setPackageCurrency(Mage::app()->getStore()->getCurrentCurrency());

        return $this->collectRates($request);
    }

    public function getCarrierByCode($carrierCode)
    {
        $className = Mage::getStoreConfig('carriers/'.$carrierCode.'/model');
        if (!$className) {
            Mage::throwException('Invalid carrier: '.$carrierCode);
        }
        $obj = Mage::getModel($className);
        return $obj;
    }

}
