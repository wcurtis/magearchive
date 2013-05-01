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
 * Core data helper
 *
 */
class Mage_Core_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_crypt;

    /**
     * Convert and format price value for current application store
     *
     * @param   float $value
     * @param   bool $format
     * @return  mixed
     */
    public static function currency($value, $format=true)
    {
        try {
            $value = Mage::app()->getStore()->convertPrice($value, $format);
        }
        catch (Exception $e){
            $value = $e->getMessage();
        }
    	return $value;
    }

    /**
     * Format and convert currency using current store option
     *
     * @param   float $value
     * @return  string
     */
    public function formatCurrency($value)
    {
        return $this->currency($value, true);
    }

    /**
     * Format date using current locale options
     *
     * @param   date $date
     * @param   string $format
     * @param   bool $showTime
     * @return  string
     */
    public function formatDate($date=null, $format='short', $showTime=false)
    {
        if (Mage_Core_Model_Locale::FORMAT_TYPE_FULL    !==$format &&
            Mage_Core_Model_Locale::FORMAT_TYPE_LONG    !==$format &&
            Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM  !==$format &&
            Mage_Core_Model_Locale::FORMAT_TYPE_SHORT   !==$format) {
            return $date;
        }

        if (is_null($date)) {
            $date = Mage::app()->getLocale()->date(time());
        }
        else {
            $date = Mage::app()->getLocale()->date(strtotime($date));
        }

        if ($showTime) {
            $format = Mage::app()->getLocale()->getDateTimeFormat($format);
        }
        else {
            $format = Mage::app()->getLocale()->getDateFormat($format);
        }

        return $date->toString($format);
    }

    /**
     * Format time using current locale options
     *
     * @param   date $date
     * @param   string $format
     * @param   bool $showTime
     * @return  string
     */
    public function formatTime($time=null, $format='short', $showDate=false)
    {
        if (Mage_Core_Model_Locale::FORMAT_TYPE_FULL    !==$format &&
            Mage_Core_Model_Locale::FORMAT_TYPE_LONG    !==$format &&
            Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM  !==$format &&
            Mage_Core_Model_Locale::FORMAT_TYPE_SHORT   !==$format) {
            return $date;
        }

        if (is_null($time)) {
            $date = Mage::app()->getLocale()->date(time());
        }
        else {
            $date = Mage::app()->getLocale()->date(strtotime($time));
        }

        if ($showDate) {
            $format = Mage::app()->getLocale()->getDateTimeFormat($format);
        }
        else {
            $format = Mage::app()->getLocale()->getTimeFormat($format);
        }

        return $date->toString($format);
    }

    /**
     * Encrypt data using application key
     *
     * @param   string $data
     * @return  string
     */
    public function encrypt($data)
    {
        if (!Mage::app()->isInstalled()) {
            return $data;
        }
        $result = base64_encode($this->_getCrypt()->encrypt($data));
        return $result;
    }

    /**
     * Decrypt data using application key
     *
     * @param   string $data
     * @return  string
     */
    public function decrypt($data)
    {
        if (!Mage::app()->isInstalled()) {
            return $data;
        }
        $result = trim($this->_getCrypt()->decrypt(base64_decode($data)));
        return $result;
    }
    
    public function validateKey($key)
    {
        return $this->_getCrypt($key);
    }

    protected function _getCrypt($key=null)
    {
        if (!$this->_crypt) {
            if (is_null($key)) {
                $key = (string)Mage::getConfig()->getNode('global/crypt/key');
            }
            $this->_crypt = Varien_Crypt::factory()->init($key);
        }
        return $this->_crypt;
    }

    /**
     * Retrieve store identifier
     *
     * @param   mixed $store
     * @return  int
     */
    public function getStoreId($store=null)
    {
        if (is_null($store)) {
            $storeId = Mage::app()->getStore()->getId();
        } elseif ($store instanceof Mage_Core_Model_Store) {
            $storeId = $store->getId();
        } elseif (is_numeric($store)) {
            $storeId = $store;
        } elseif (is_string($store)) {
            $storeId = Mage::getModel('core/store')->loadConfig($store)->getId();
        }
        return $storeId;
    }
}
