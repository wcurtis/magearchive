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
 * Store model
 *
 */
class Mage_Core_Model_Store extends Mage_Core_Model_Abstract
{
    const XML_PATH_UNSECURE_PROTOCOL= 'web/unsecure/protocol';
    const XML_PATH_UNSECURE_HOST    = 'web/unsecure/host';
    const XML_PATH_UNSECURE_PORT    = 'web/unsecure/port';
    const XML_PATH_UNSECURE_PATH    = 'web/unsecure/base_path';
    const XML_PATH_SECURE_PROTOCOL  = 'web/secure/protocol';
    const XML_PATH_SECURE_HOST      = 'web/secure/host';
    const XML_PATH_SECURE_PORT      = 'web/secure/port';
    const XML_PATH_SECURE_PATH      = 'web/secure/base_path';

    const DEFAULT_CODE = 'default';

    protected $_priceFilter;

    protected $_website;

    protected $_configCache = array();

    protected $_dirCache = array();

    protected $_urlCache = array();

    protected $_session;

    public function __construct()
    {
        parent::__construct();
    }

    protected function _construct()
    {
        $this->_init('core/store');
    }

    /**
     * Retrieve store session object
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    protected function _getSession()
    {
        if (!$this->_session) {
            $this->_session = Mage::getModel('core/session')
                ->init('store_'.$this->getCode());
        }
        return $this->_session;
    }

    /**
     * Loading store data
     *
     * @param   mixed $id
     * @param   string $field
     * @return  Mage_Core_Model_Store
     */
    public function load($id, $field=null)
    {
        if (!is_numeric($id) && is_null($field)) {
            $this->_getResource()->load($this, $id, 'code');
            return $this;
        }
        return parent::load($id, $field);
    }

    /**
     * Loading store configuration data
     *
     * @param   string $code
     * @return  Mage_Core_Model_Store
     */
    public function loadConfig($code)
    {
        if (is_numeric($code)) {
            foreach (Mage::getConfig()->getNode('stores')->children() as $storeCode=>$store) {
                if ((int)$store->system->store->id==$code) {
                    $code = $storeCode;
                    break;
                }
            }
        } else {
            $store = Mage::getConfig()->getNode('stores/'.$code);
        }
        if (!empty($store)) {
            $this->setCode($code);
            $id = (int)$store->system->store->id;
            $this->setId($id)->setStoreId($id);
            $this->setWebsiteId((int)$store->system->website->id);
        }
        return $this;
    }

    /**
     * Retrieve store identifier
     *
     * @return int
     */
    public function getId()
    {
        if (is_null(parent::getId())) {
            $this->setId($this->getConfig('system/store/id'));
        }
        return parent::getId();
    }

    /**
     * Retrieve store code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * Retrieve store configuration data
     *
     * @param   string $path
     * @param   string $scope
     * @return  string|null
     */
    public function getConfig($path) {
        if (isset($this->_configCache[$path])) {
            return $this->_configCache[$path];
        }

        $config = Mage::getConfig();

        $fullPath = 'stores/'.$this->getCode().'/'.$path;
        $data = $config->getNode($fullPath);

        if (!$data && $this->getData($this->getIdFieldName())) {
            $fullPath = 'websites/'.$this->getWebsite()->getCode().'/'.$path;
            $data = $config->getNode($fullPath);
        }
        if (!$data) {
            $fullPath = 'default/'.$path;
            $data = $config->getNode($fullPath);
        }
        if (!$data) {
            Mage::log('Invalid store configuration path: '.$path);
            return null;
        }
        if (!$data->children()) {
            $value = $this->processSubst((string)$data);
        } else {
            $value = array();

            foreach ($data->children() as $k=>$v) {
                if ($v->children()) {
                    $value[$k] = $v;
                } else {
                    $value[$k] = $this->processSubst((string)$v);
                }
            }
        }

        $this->_configCache[$path] = $value;
        return $value;
    }

    /**
     * Retrieve store website
     *
     * @return Mage_Core_Model_Website
     */
    public function getWebsite()
    {
        if (empty($this->_website)) {
            $this->_website = Mage::getModel('core/website')->load($this->getConfig('system/website/id'));
        }
        return $this->_website;
    }

    public function processSubst($str)
    {
        if (!is_string($str)) {
            return $str;
        }
        if (strpos($str, '{{base_path}}')!==false) {
            $str = str_replace('{{base_path}}', $this->getDefaultBasePath(), $str);
        }
        if (strpos($str, '{{secure')!==false) {
            $hostArr = explode(':', $_SERVER['HTTP_HOST']);
            $str = str_replace(
                array('{{secure_protocol}}', '{{secure_host}}', '{{secure_port}}'),
                array('https', $hostArr[0], isset($hostArr[1]) ? $hostArr[1] : 443),
                $str
            );
        }
        return $str;
    }

    public function getDefaultBasePath()
    {
        if (!isset($_SERVER['SCRIPT_NAME'])) {
            return '/';
        }
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if (empty($basePath) || "\\"==$basePath || "/"==$basePath) {
            $basePath = '/';
        } else {
            $basePath .= '/';
        }
        return $basePath;
    }

    public function getDatashareStores($key)
    {
        // TODO store level data sharing configuration in next version
        // if ($stores = $this->getConfig('advanced/datashare/'.$key)) {
        if ($stores = $this->getWebsite()->getConfig('advanced/datashare/'.$key)) {
            return explode(',', $stores);
        } else {
            $this->updateDatasharing();
            if ($stores = $this->getWebsite()->getConfig('advanced/datashare/'.$key)) {
                return explode(',', $stores);
            }
        }
        return $this->getWebsite()->getStoresIds();
    }

    public function updateDatasharing()
    {
        $this->_getResource()->updateDatasharing();
    }

    /**
     * Retrieve url using store configuration specific
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route='', $params=array())
    {
        $url = Mage::getModel('core/url')
            ->setStore($this);
        return $url->getUrl($route, $params);
    }

    /*************************************************************************************
     * Store currency interface
     */

    /**
     * Retrieve store base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
    }

    /**
     * Retrieve store base currency
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getBaseCurrency()
    {
        $currency = $this->getData('base_currency');
        if (is_null($currency)) {
            $currency = Mage::getModel('directory/currency')->load($this->getBaseCurrencyCode());
            $this->setData('base_currency', $currency);
        }
        return $currency;
    }

    /**
     * Get default store currency code
     *
     * @return string
     */
    public function getDefaultCurrencyCode()
    {
        $result = $this->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_DEFAULT);
        return $result;
    }

    /**
     * Retrieve store default currency
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getDefaultCurrency()
    {
        $currency = $this->getData('default_currency');
        if (is_null($currency)) {
            $currency = Mage::getModel('directory/currency')->load($this->getDefaultCurrencyCode());
            $this->setData('default_currency', $currency);
        }
        return $currency;
    }

    /**
     * Set current store currency code
     *
     * @param   string $code
     * @return  string
     */
    public function setCurrentCurrencyCode($code)
    {
        $code = strtoupper($code);
        if (in_array($code, $this->getAvailableCurrencyCodes())) {
            $this->_getSession()->setCurrencyCode($code);
        }
        return $this;
    }

    /**
     * Get current store currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        $code = $this->_getSession()->getCurrencyCode();
        if (in_array($code, $this->getAvailableCurrencyCodes())) {
            return $code;
        }
        return $this->getDefaultCurrencyCode();
    }

    /**
     * Get allowed store currency codes
     *
     * @return array
     */
    public function getAvailableCurrencyCodes()
    {
        $codes = $this->getData('available_currency_codes');
        if (is_null($codes)) {
            $codes = explode(',', $this->getConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_ALLOW));
            $this->setData('available_currency_codes', $codes);
        }
        return $codes;
    }

    /**
     * Retrieve store current currency
     *
     * @return Mage_Directory_Model_Currency
     */
    public function getCurrentCurrency()
    {
        $currency = $this->getData('current_currency');
        if (is_null($currency)) {
            $currency = Mage::getModel('directory/currency')->load($this->getCurrentCurrencyCode());
            $this->setData('current_currency', $currency);
        }
        return $currency;
    }

    public function getCurrentCurrencyRate()
    {
        return $this->getBaseCurrency()->getRate($this->getCurrentCurrency());
    }

    /**
     * Convert price from default currency to current currency
     *
     * @param   double $price
     * @return  double
     */
    public function convertPrice($price, $format=false)
    {
        if ($this->getCurrentCurrency() && $this->getBaseCurrency()) {
            $value = $this->getBaseCurrency()->convert($price, $this->getCurrentCurrency());
        } else {
            $value = $price;
        }
        $value = $this->roundPrice($value);

        if ($this->getCurrentCurrency() && $format) {
            $value = $this->formatPrice($value);
        }
        return $value;
    }

    public function roundPrice($price)
    {
        return round($price, 2);
    }

    /**
     * Format price with currency filter (taking rate into consideration)
     *
     * @param   double $price
     * @return  string
     */
    public function formatPrice($price)
    {
        if ($this->getCurrentCurrency()) {
            return $this->getCurrentCurrency()->format($price);
        }
        return $price;
    }

    /**
     * Get store price filter
     *
     * @return unknown
     */
    public function getPriceFilter()
    {
        if (!$this->_priceFilter) {
            if ($this->getBaseCurrency() && $this->getCurrentCurrency()) {
                $this->_priceFilter = $this->getCurrentCurrency()->getFilter();
                $this->_priceFilter->setRate($this->getBaseCurrency()->getRate($this->getCurrentCurrency()));
            }
            elseif($this->getDefaultCurrency()) {
                $this->_priceFilter = $this->getDefaultCurrency()->getFilter();
            }
            else {
                $this->_priceFilter = new Varien_Filter_Sprintf('%s', 2);
            }
        }
        return $this->_priceFilter;
    }
}
