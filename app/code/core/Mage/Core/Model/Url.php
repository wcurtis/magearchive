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
 * URL
 *
 * Properties:
 *
 * - request
 *
 * - relative_url: true, false
 * - type: 'route', 'skin', 'js', 'media'
 * - store: instanceof Mage_Core_Model_Store
 * - secure: true, false
 *
 * - scheme: 'http', 'https'
 * - user: 'user'
 * - password: 'password'
 * - host: 'localhost'
 * - port: 80, 443
 * - base_path: '/dev/magento'
 * - base_script: 'index.php/'
 *
 * - route_path: '/module/controller/action/param1/value1/param2/value2'
 * - route_name: 'module'
 * - controller_name: 'controller'
 * - action_name: 'action'
 * - route_params: array('param1'=>'value1', 'param2'=>'value2')
 *
 * - query: (?)'param1=value1&param2=value2'
 * - query_array: array('param1'=>'value1', 'param2'=>'value2')
 * - fragment: (#)'fragment-anchor'
 *
 * URL structure:
 *
 * https://user:password@host:443/base_path/[base_script]route_name/controller_name/action_name/param1/value1?query_param=query_value#fragment
 *       \__________A___________/\____________________________________B_____________________________________/
 * \__________________C___________________/              \__________________D_________________/ \_____E_____/
 * \_____________F______________/                        \___________________________G______________________/
 * \___________________________________________________H____________________________________________________/
 *
 * - A: authority
 * - B: path
 * - C: absolute_base_url
 * - D: action_path
 * - E: route_params
 * - F: host_url
 * - G: route_path
 * - H: route_url
 *
 * @category   Mage
 * @package    Mage_Core
 */
class Mage_Core_Model_Url extends Varien_Object
{
    const TYPE_ROUTE    = 'route';
    const TYPE_SKIN     = 'skin';
    const TYPE_JS       = 'js';
    const TYPE_MEDIA    = 'media';

    const SCHEME_UNSECURE   = 'http';
    const SCHEME_SECURE     = 'https';

    const PORT_UNSECURE = 80;
    const PORT_SECURE   = 443;

    const DEFAULT_CONTROLLER_NAME   = 'index';
    const DEFAULT_ACTION_NAME       = 'index';

    static protected $_configDataCache;
    static protected $_baseUrlCache;
    static protected $_encryptedSessionId;

    /**
     * Controller request object
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    protected function _construct()
    {
        $this->setStore(null);
    }

    /**
     * Initialize object data from retrieved url
     *
     * @param   string $url
     * @return  Mage_Core_Model_Url
     */
    public function parseUrl($url)
    {
        $data   = parse_url($url);
        $parts  = array(
            'scheme'=>'setScheme',
            'host'  =>'setHost',
            'port'  =>'setPort',
            'user'  =>'setUser',
            'pass'  =>'setPassword',
            'path'  =>'setPath',
            'query' =>'setQuery',
            'fragment'=>'setFragment');

        foreach ($parts as $component=>$method) {
            if (isset($data[$component])) {
                $this->$method($data[$component]);
            }
        }
        return $this;
    }

    /**
     * Retrieve default controller name
     *
     * @return string
     */
    public function getDefaultControllerName()
    {
        return self::DEFAULT_CONTROLLER_NAME;
    }

    /**
     * Retrieve default action name
     *
     * @return string
     */
    public function getDefaultActionName()
    {
        return self::DEFAULT_ACTION_NAME;
    }

    public function getConfigData($key, $prefix=null)
    {
//        if (!isset(self::$_configDataCache)) {
//           $this->loadCache();
//        }
        if (is_null($prefix)) {
            $prefix = 'web/'.($this->getSecure() ? 'secure' : 'unsecure').'/';
        }
        $path = $prefix.$key;

        $cacheId = $this->getStore()->getCode().'/'.$path;
        if (!isset(self::$_configDataCache[$cacheId])) {
            $data = $this->getStore()->getConfig($path);
            self::$_configDataCache[$cacheId] = $data;
        }

        return self::$_configDataCache[$cacheId];
    }

    public function isCurrentlySecure()
    {
        if (!empty($_SERVER['HTTPS'])) {
            return true;
        }

        if (Mage::app()->isInstalled()) {
            return Mage::getStoreConfig('web/secure/protocol')=='https'
                && Mage::getStoreConfig('web/secure/port')==$_SERVER['SERVER_PORT'];
        } else {
            return 443==$_SERVER['SERVER_PORT'];
        }
    }

    public function shouldBeSecure()
    {
        return false;
    }

    public function setRequest(Zend_Controller_Request_Http $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Zend request object
     *
     * @return Zend_Controller_Request_Http
     */
    public function getRequest()
    {
        if (!$this->_request) {
            $this->_request = Mage::app()->getFrontController()->getRequest();
        }
        return $this->_request;
    }

    public function setType($data)
    {
        if ($this->hasData('type') && $this->getData('type')===$data) {
            return $this;
        }
        $this->setData('type', $data);
        $this->unsetData('secure');
        $this->unsetData('base_path');
        $this->unsetData('query');
        $this->unsetData('query_params');
        $this->unsetData('fragment');
        #$this->getSecure();
        return $this;
    }

    public function getType()
    {
        if (!$this->hasData('type')) {
            $this->setData('type', self::TYPE_ROUTE);
        }
        return $this->getData('type');
    }

    public function refreshAbsoluteBase()
    {
        $this->unsetData('secure');
        $this->unsetData('scheme');
        $this->unsetData('host');
        $this->unsetData('port');
        $this->unsetData('base_path');
        $this->unsetData('authority');
        return $this;
    }

    /**
     * Declare secure flag property
     *
     * @param   boolean $flag
     * @return  Mage_Core_Model_Url
     */
    public function setSecure($flag)
    {
        if ($flag==$this->getSecure()) {
            return $this;
        }
        $this->refreshAbsoluteBase();
        $this->setData('secure', $flag);
        return $this;
    }

    public function getSecure()
    {
        if (!$this->hasData('secure')) {
            if ($this->getType()===self::TYPE_ROUTE) {
                $this->setData('secure', Mage::getConfig()->isUrlSecure('/'.$this->getActionPath()));
            } else {
                $this->setData('secure', $this->isCurrentlySecure());
            }
        }
        return $this->getData('secure');
    }

    public function setStore($data)
    {
        $defStore = Mage::app()->getStore();

        if (is_null($data)) {
            $store = $defStore;
        } elseif (is_numeric($data)) {
            if ($defStore->getId()==$data) {
                $store = $defStore;
            } else {
                $store = Mage::getModel('core/store')->load($data);
            }
        } elseif (is_string($data)) {
            if ($defStore->getCode()==$data) {
                $store = $defStore;
            } else {
                $store = Mage::getModel('core/store')->load($data);
            }
        } elseif ($data instanceof Mage_Core_Model_Store) {
            $store = $data;
        } else {
            throw Mage::exception('Mage_Core', 'Wrong store argument specified');
        }
        if ($this->hasStore() && $this->getStore()->getId()===$store->getId()) {
            return $this;
        }

        $this->setData('store', $store);
        $this->refreshAbsoluteBase();
        return $this;
    }

    public function getStore()
    {
        if (!$this->hasData('store')) {
            $this->setStore(null);
        }
        return $this->getData('store');
    }

    public function setScheme($data)
    {
        if ($data!==self::SCHEME_SECURE && $data!==self::SCHEME_UNSECURE) {
            throw Mage::exception('Mage_Core', 'Invalid scheme specified');
        }

        return $this->setData('scheme', $data);
    }

    public function getScheme()
    {
        if (!$this->hasData('scheme')) {
            $this->setData('scheme', $this->getConfigData('protocol'));
        }
        return $this->getData('scheme');
    }

    public function setHost($data)
    {
        if ($this->getData('host')==$data) {
            return $this;
        }
        $this->setData('host', $data)->unsetData('authority');
        return $this;
    }

    public function getHost()
    {
        if (!$this->hasData('host')) {
            $this->setData('host', $this->getConfigData('host'));
        }
        return $this->getData('host');
    }

    public function setPort($data)
    {
        if ($this->getData('port')==$data) {
            return $this;
        }
        $this->setData('port', $data)->unsetData('authority');
        return $this;
    }

    public function getPort()
    {
        if (!$this->hasData('port')) {
            $this->setData('port', $this->getConfigData('port'));
        }
        return $this->getData('port');
    }

    public function setBasePath($data)
    {
        if ($this->getData('base_path')==$data) {
            return $this;
        }
        $this->setData('base_path', $data)->unsetData('authority');
        return $this;
    }

    public function getBaseScript()
    {
//        if ($this->hasData('base_script')) {
//            return $this->getData('base_script');
//        }

        if ($this->getType()!==self::TYPE_ROUTE) {
            return '';
        }

        if (!Mage::getStoreConfigFlag('web/url/use_script_name')) {
            return '';
        }

        $script = basename($_SERVER['SCRIPT_NAME']).'/';

        return $script;
    }

    public function getBasePath()
    {
        if (!$this->hasData('base_path')) {
            $basePath = $this->getConfigData('base_path');
            if ($this->getType()===self::TYPE_ROUTE) {
                $path = $basePath;
            } else {
                $path = $this->getConfigData($this->getType(), 'web/url/');
                $path = str_replace('{{base_path}}', $basePath, $path);
            }
            $path .= $this->getBaseScript();
            $this->setData('base_path', $path);
        }
        return $this->getData('base_path');
    }

    public function setPath($data)
    {
        if (strpos($data, $this->getBasePath())===0) {
            $this->setRoutePath(substr($data, strlen($this->getBasePath())));
        } else {
            $this->setBasePath('');
            $this->setRoutePath($data);
        }
        return $this;
    }

    public function getPath()
    {
        return $this->getBasePath().$this->getRoutePath();
    }

    public function getAuthority()
    {
        if (!$this->hasData('authority')) {
            $authority = '//'.$this->getHost();

            if ($this->getUser()) {
                $authority .= $this->getUser();
                if ($this->getPassword()) {
                    $authority .= ':'.$this->getPassword();
                }
                $authority .= '@';
            }

            $secure = $this->getSecure();
            $port = $this->getPort();
            if (!$secure && $port!=self::PORT_UNSECURE || $secure && $port!=self::PORT_SECURE) {
                $authority .= ':'.$port;
            }
            $this->setData('authority', $authority);
        }
        return $this->getData('authority');
    }

    public function getHostUrl()
    {
        $url = $this->getScheme().':'.$this->getAuthority();
        return $url;
    }

    public function getAbsoluteBaseUrl()
    {
        $url = $this->getHostUrl().$this->getBasePath();
        return $url;
    }

    public function getBaseUrl($params=array())
    {
        if (isset($params['_type'])) {
            $this->setType($params['_type']);
        }
        if (isset($params['_secure'])) {
            $this->setSecure(!empty($params['_secure']));
        }
        if (isset($params['_absolute'])) {
            $this->setRelativeUrl(!$params['_absolute']);
        }
        if (isset($params['_store'])) {
            $this->setStore($params['_store']);
        }
        $cacheId = $this->getStore()->getCode();
        $cacheId .= '/'.($this->getSecure() ? 'secure' : 'unsecure');
        $cacheId .= '/'.$this->getType();

        if (isset(self::$_baseUrlCache[$cacheId])) {
            $url = self::$_baseUrlCache[$cacheId];
        } else {
            if ($this->getRelativeUrl()) {
                $url = $this->getBasePath();
            } else {
                $url = $this->getAbsoluteBaseUrl();
            }
            self::$_baseUrlCache[$cacheId] = $url;
        }
        $this->checkCookieDomains();

        return $url;
    }

    public function setRoutePath($data)
    {
        if ($this->getData('route_path')==$data) {
            return $this;
        }

        $a = explode('/', $data);

        $route = array_shift($a);
        if ('*'===$route) {
            $route = $this->getRequest()->getModuleName();
        }
        $this->setRouteName($route);
        $routePath = $route.'/';

        if (!empty($a)) {
            $controller = array_shift($a);
            if ('*'===$controller) {
                $controller = $this->getRequest()->getControllerName();
            }
            $this->setControllerName($controller);
            $routePath .= $controller.'/';
        }

        if (!empty($a)) {
            $action = array_shift($a);
            if ('*'===$action) {
                $action = $this->getRequest()->getActionName();
            }
            $this->setActionName($action);
            $routePath .= $action.'/';
        }

        if (!empty($a)) {
            $this->unsetData('route_params');
            while (!empty($a)) {
                $key = array_shift($a);
                if (!empty($a)) {
                    $value = array_shift($a);
                    $this->setRouteParam($key, $value);
                    #$routePath .= $key.'/'.urlencode($value).'/';
                    $routePath .= $key.'/'.$value.'/';
                }
            }
        }

        #$this->setData('route_path', $routePath);

        return $this;
    }

    public function getActionPath()
    {
        if (!$this->getRouteName()) {
            return '';
        }

        $hasParams = (bool)$this->getRouteParams();
        $path = $this->getRouteName() . '/';

        if ($this->getControllerName()) {
            $path .= $this->getControllerName() . '/';
        } elseif ($hasParams) {
            $path .= $this->getDefaultControllerName() . '/';
        }
        if ($this->getActionName()) {
            $path .= $this->getActionName() . '/';
        } elseif ($hasParams) {
            $path .= $this->getDefaultActionName() . '/';
        }

        return $path;
    }

    public function getRoutePath()
    {
        if (!$this->hasData('route_path')) {
            $routePath = $this->getActionPath();
            if ($this->getRouteParams()) {
                foreach ($this->getRouteParams() as $key=>$value) {
                    if (is_null($value) || false===$value || ''===$value || !is_scalar($value)) {
                        continue;
                    }
                    #$routePath .= $key.'/'.urlencode($value).'/';
                    $routePath .= $key.'/'.$value.'/';
                }
            }
            if ($routePath != '' && substr($routePath, -1, 1) !== '/') {
                $routePath.= '/';
            }
            $this->setData('route_path', $routePath);
        }
        return $this->getData('route_path');
    }

    public function setRouteName($data)
    {
        if ($this->getData('route_name')==$data) {
            return $this;
        }
        $this->unsetData('route_path')->unsetData('controller_name')->unsetData('action_name')->unsetData('secure');
        return $this->setData('route_name', $data);
    }

    public function getRouteName()
    {
        return $this->getData('route_name');
    }

    public function setControllerName($data)
    {
        if ($this->getData('controller_name')==$data) {
            return $this;
        }
        $this->unsetData('route_path')->unsetData('action_name')->unsetData('secure');
        return $this->setData('controller_name', $data);
    }

    public function getControllerName()
    {
        return $this->getData('controller_name');
    }

    public function setActionName($data)
    {
        if ($this->getData('action_name')==$data) {
            return $this;
        }
        $this->unsetData('route_path');
        return $this->setData('action_name', $data)->unsetData('secure');
    }

    public function getActionName()
    {
        return $this->getData('action_name');
    }

    public function setRouteParams(array $data, $unsetOldParams=true)
    {
        if (isset($data['_type'])) {
            $this->setType($data['_type']);
            unset($data['_type']);
        }

        if (isset($data['_secure'])) {
            $this->setSecure((bool)$data['_secure']);
            unset($data['_secure']);
        }

        if (isset($data['_absolute'])) {
            unset($data['_absolute']);
        }

        if ($unsetOldParams) {
            $this->unsetData('route_params');
        }

        $this->setUseUrlCache(true);
        if (isset($data['_current'])) {
            if (is_array($data['_current'])) {
                foreach ($data['_current'] as $key) {
                    if (array_key_exists($key, $data) || !$this->getRequest()->getUserParam($key)) {
                        continue;
                    }
                    $data[$key] = $this->getRequest()->getUserParam($key);
                }
            } elseif ($data['_current']) {
                foreach ($this->getRequest()->getUserParams() as $key=>$value) {
                    if (array_key_exists($key, $data) || $this->getRouteParam($key)) {
                        continue;
                    }
                    $data[$key] = $value;
                }
                foreach ($this->getRequest()->getQuery() as $key=>$value) {
                    $this->setQueryParam($key, $value);
                }
                $this->setUseUrlCache(false);
            }
            unset($data['_current']);
        }

        foreach ($data as $k=>$v) {
            $this->setRouteParam($k, $v);
        }

        return $this;
    }

    public function getRouteParams()
    {
        return $this->getData('route_params');
    }

    public function setRouteParam($key, $data)
    {
        $params = $this->getData('route_params');
        if (isset($params[$key]) && $params[$key]==$data) {
            return $this;
        }
        $params[$key] = $data;
        $this->unsetData('route_path');
        return $this->setData('route_params', $params);
    }

    public function getRouteParam($key)
    {
        return $this->getData('route_params', $key);
    }

    public function getRouteUrl($routePath=null, $routeParams=null)
    {
        $this->unsetData('route_params');

        if (!is_null($routePath)) {
            $this->setRoutePath($routePath);
        }
        if (is_array($routeParams)) {
            $this->setRouteParams($routeParams, false);
        }

        $url = $this->getBaseUrl().$this->getRoutePath();
        return $url;
    }

    /**
     * If the host was switched but session cookie won't recognize it - add session id to query
     *
     * @return unknown
     */
    public function checkCookieDomains()
    {
        $hostArr = explode(':', $this->getRequest()->getServer('HTTP_HOST'));
        if ($hostArr[0]!==$this->getHost()) {
            $session = Mage::getSingleton('core/session');
            if (!$session->isValidForHost($this->getHost())) {
                if (!self::$_encryptedSessionId) {
                    $helper = Mage::helper('core');
                    if (!$helper) {
                        return $this;
                    }
                    self::$_encryptedSessionId = $helper->encrypt($session->getSessionId());
                }
                $this->setQueryParam(
                    Mage_Core_Model_Session_Abstract::SESSION_ID_QUERY_PARAM,
                    self::$_encryptedSessionId
                );
            }
        }
        return $this;
    }

    public function setQuery($data)
    {
        if ($this->getData('query')==$data) {
            return $this;
        }
        $this->unsetData('query_params');
        return $this->setData('query', $data);
    }

    public function getQuery()
    {
        if (!$this->hasData('query')) {
            $query = '';
            if (is_array($this->getQueryParams())) {
                $query = http_build_query($this->getQueryParams());
            }
            $this->setData('query', $query);
        }
        return $this->getData('query');
    }

    public function setQueryParams(array $data)
    {
        if ($this->getData('query_params')==$data) {
            return $this;
        }
        $this->unsetData('query');
        return $this->setData('query_params', $data);
    }

    public function getQueryParams()
    {
        if (!$this->hasData('query_params')) {
            $params = array();
            if ($this->getData('query')) {
                foreach (explode('&', $this->getData('query')) as $param) {
                    $paramArr = explode('=', $param);
                    $params[$paramArr[0]] = urldecode($paramArr[1]);
                }
            }
            $this->setData('query_params', $params);
        }
        return $this->getData('query_params');
    }

    public function setQueryParam($key, $data)
    {
        $params = $this->getQueryParams();
        if (isset($params[$key]) && $params[$key]==$data) {
            return $this;
        }
        $params[$key] = $data;
        $this->unsetData('query');
        return $this->setData('query_params', $params);
    }

    public function getQueryParam($key)
    {
        if (!$this->hasData('query_params')) {
            $this->getQueryParams();
        }
        return $this->getData('query_params', $key);
    }

    public function setFragment($data)
    {
        return $this->setData('fragment', $data);
    }

    public function getFragment()
    {
        return $this->getData('fragment');
    }

    public function getUrl($routePath=null, $routeParams=null)
    {
        Varien_Profiler::start(__METHOD__);

        $url = $this->getRouteUrl($routePath, $routeParams);

        if ($this->getQuery()) {
            $url .= '?'.$this->getQuery();
        }

        if ($this->getFragment()) {
            $url .= '#'.$this->getFragment();
        }
        Varien_Profiler::stop(__METHOD__);

        return $url;
    }
}