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


class Mage_Shipping_Model_Config extends Varien_Object
{
    protected static $_carriers;

    /**
     * Retrieve active system carriers
     *
     * @param   mixed $store
     * @return  array
     */
    public function getActiveCarriers($store=null)
    {
        $carriers = array();
        $config = Mage::getStoreConfig('carriers', $store);
        foreach ($config as $code => $carrierConfig) {
            if ($carrierConfig->is('active')) {
                $carriers[$code] = $this->_getCarrier($code, $carrierConfig);
            }
        }
        return $carriers;
    }

    /**
     * Retrieve all system carriers
     *
     * @param   mixed $store
     * @return  array
     */
    public function getAllCarriers($store=null)
    {
        $carriers = array();
        $config = Mage::getStoreConfig('carriers', $store);
        foreach ($config as $code => $carrierConfig) {
            $carriers[$code] = $this->_getCarrier($code, $carrierConfig);
        }
        return $carriers;
    }

    /**
     * Retrieve carrier model instance by carrier code
     *
     * @param   string $carrierCode
     * @param   mixed $store
     * @return  Mage_Usa_Model_Shipping_Carrier_Abstract
     */
    public function getCarrierInstance($carrierCode, $store=null)
    {
        $config =  Mage::getStoreConfig('carriers', $store);
        if (isset($config[$carrierCode])) {
            return $this->_getCarrier($carrierCode, $config[$carrierCode]);
        }
        return false;
    }

    protected function _getCarrier($code, $config)
    {
        if (isset(self::$_carriers[$code])) {
            return self::$_carriers[$code];
        }
        $modelName = (string) $config->model;
        self::$_carriers[$code] = Mage::getModel($modelName);
        self::$_carriers[$code]->setConfig($config)
            ->setId($code)
            ->setTitle((string)$config->title)
            ->setSortOrder((int)$config->sort_order);
        return self::$_carriers[$code];
    }
}
