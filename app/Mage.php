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

define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('BP', dirname(dirname(__FILE__)));

/**
 * Error reporting
 */
error_reporting(E_ALL | E_STRICT);

/**
 * Include path
 */
ini_set('include_path',
           BP . '/app/code/local'
    . PS . BP . '/app/code/community'
    . PS . BP . '/app/code/core'
    . PS . BP . '/lib'
    . PS . ini_get('include_path')
);

include_once "Mage/Core/functions.php";
include_once "Varien/Profiler.php";

Varien_Profiler::enable();

/**
 * Check magic quotes settings
 */
checkMagicQuotes();

/**
 * Main Mage hub class
 *
 * @author Moshe Gurvich <moshe@varien.com>
 */
final class Mage {
    /**
     * Registry collection
     *
     * @var array
     */
    static private $_registry = array();

    /**
     * Application model
     *
     * @var Mage_Core_Model_App
     */
    static private $_app;

    static private $_useCache = array();

    public static function getVersion()
    {
        return '0.7.15480';
    }

    /**
     * Register a new variable
     *
     * @param string $key
     * @param mixed $value
     */
    public static function register($key, $value)
    {
        if(isset(self::$_registry[$key])){
            Mage::throwException('Mage registry key "'.$key.'" already exists');
        }
        self::$_registry[$key] = $value;
    }

    /**
     * Retrieve a value from registry by a key
     *
     * @param string $key
     * @return mixed
     */
    public static function registry($key)
    {
        if (isset(self::$_registry[$key])) {
            return self::$_registry[$key];
        }
        return null;
    }

    /**
     * Set application root absolute path
     *
     * @param string $appRoot
     */
    public static function setRoot($appRoot='')
    {
        if (''===$appRoot) {
            // automagically find application root by dirname of Mage.php
            $appRoot = dirname(__FILE__);
        }

        $appRoot = realpath($appRoot);

        if (is_dir($appRoot) and is_readable($appRoot)) {
            Mage::register('appRoot', $appRoot);
        } else {
            Mage::throwException($appRoot.' is not a directory or not readable by this user');
        }
    }

    /**
     * Get application root absolute path
     *
     * @return string
     */

    public static function getRoot()
    {
        return Mage::registry('appRoot');
    }

    /**
     * Retrieve application root absolute path
     *
     * @return string
     */
    public static function getBaseDir($type='', array $params=array())
    {
        return Mage::getConfig()->getBaseDir($type, $params);
    }

    public static function getModuleDir($type, $moduleName)
    {
        return Mage::getConfig()->getModuleDir($type, $moduleName);
    }

    public static function getStoreConfig($path, $id=null)
    {
        return self::app()->getStore($id)->getConfig($path);
    }

    public static function getStoreConfigFlag($path, $id=null)
    {
        $flag = strtolower(Mage::getStoreConfig($path, $id));
        if (!empty($flag) && 'false'!==$flag && '0'!==$flag) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get base URL path by type
     *
     * @param string $type
     * @return string
     */
    public static function getBaseUrl($type=Mage_Core_Model_Store::URL_TYPE_LINK, $secure=null)
    {
        return Mage::app()->getStore()->getBaseUrl($type, $secure);
    }

    public static function getUrl($route='', $params=array())
    {
        return Mage::getModel('core/url')->getUrl($route, $params);
    }

    /**
     * Get design package singleton
     *
     * @return Mage_Core_Model_Design_Package
     */
    public static function getDesign()
    {
        return Mage::getSingleton('core/design_package');
    }

    /**
     * Get a config object
     *
     * @return Mage_Core_Model_Config
     */
    public static function getConfig()
    {
        return Mage::registry('config');
    }

    /**
     * Add observer to even object
     *
     * @param string $eventName
     * @param callback $callback
     * @param array $arguments
     * @param string $observerName
     */
    public static function addObserver($eventName, $callback, $data=array(), $observerName='', $observerClass='')
    {
        if ($observerClass=='') {
            $observerClass = 'Varien_Event_Observer';
        }
        $observer = new $observerClass();
        $observer->setName($observerName)->addData($data)->setEventName($eventName)->setCallback($callback);
        return Mage::registry('events')->addObserver($observer);
    }

    /**
     * Dispatch event
     *
     * Calls all observer callbacks registered for this event
     * and multiobservers matching event name pattern
     *
     * @param string $name
     * @param array $args
     */
    public static function dispatchEvent($name, array $data=array())
    {
        Varien_Profiler::start('DISPATCH EVENT:'.$name);
        $result = Mage::registry('events')->dispatch($name, $data);
        Varien_Profiler::stop('DISPATCH EVENT:'.$name);
        return $result;
    }

    /**
     * Retrieve model object
     *
     * @link    Mage_Core_Model_Config::getModelInstance
     * @param   string $modelClass
     * @param   array $arguments
     * @return  Mage_Core_Model_Abstract
     */
    public static function getModel($modelClass='', $arguments=array())
    {
        return Mage::getConfig()->getModelInstance($modelClass, $arguments);
    }

    /**
     * Retrieve model object singleton
     *
     * @param   string $modelClass
     * @param   array $arguments
     * @return  Mage_Core_Model_Abstract
     */
    public static function getSingleton($modelClass='', array $arguments=array())
    {
        $registryKey = '_singleton/'.$modelClass;
        if (!Mage::registry($registryKey)) {
            Mage::register($registryKey, Mage::getModel($modelClass, $arguments));
        }
        return Mage::registry($registryKey);
    }

    /**
     * Retrieve object of resource model
     *
     * @param   string $modelClass
     * @param   array $arguments
     * @return  Object
     */
    public static function getResourceModel($modelClass, $arguments=array())
    {
        return Mage::getConfig()->getResourceModelInstance($modelClass, $arguments);
    }

    /**
     * Retrieve resource vodel object singleton
     *
     * @param   string $modelClass
     * @param   array $arguments
     * @return  object
     */
    public static function getResourceSingleton($modelClass='', array $arguments=array())
    {
        $registryKey = '_resource_singleton/'.$modelClass;
        if (!Mage::registry($registryKey)) {
            Mage::register($registryKey, Mage::getResourceModel($modelClass, $arguments));
        }
        return Mage::registry($registryKey);
    }

    /**
     * Deprecated, use Mage::helper()
     *
     * @param string $type
     * @return object
     */
    public static function getHelper($type)
    {
        $action = Mage::registry('action');
        return $action ? $action->getLayout()->getHelper($type) : false;
    }

    /**
     * Retrieve helper object
     *
     * @param   helper name $name
     * @return  Mage_Core_Helper_Abstract
     */
    public static function helper($name)
    {
        return Mage::app()->getHelper($name);
    }

    /**
     * Return new exception by module to be thrown
     *
     * @param string $module
     * @param string $message
     * @param integer $code
     */
    public static function exception($module='Mage_Core', $message='', $code=0)
    {
        $className = $module.'_Exception';
        return new $className($message, $code);
    }

    public static function throwException($message, $messageStorage=null)
    {
        if ($messageStorage && ($storage = Mage::getSingleton($messageStorage))) {
            $storage->addError($message);
        }
        throw new Mage_Core_Exception($message);
    }

    /**
     * Initialize and retrieve application
     *
     * @param   string $store
     * @param   string $etcDir
     * @return  Mage_Core_Model_App
     */
    public static function app($store='', $etcDir=null)
    {
        if (is_null(self::$_app)) {
            Varien_Profiler::start('app/init');

            self::$_app = new Mage_Core_Model_App();

            Mage::setRoot();
            Mage::register('events', new Varien_Event_Collection());
            Mage::register('config', new Mage_Core_Model_Config());

            self::$_app->init($store, $etcDir);
            self::$_app->loadAreaPart(Mage_Core_Model_App_Area::AREA_GLOBAL, Mage_Core_Model_App_Area::PART_EVENTS);
        }
        return self::$_app;
    }

    /**
     * Front end main entry point
     *
     * @param string $storeCode
     */
    public static function run($storeCode='', $etcDir=null)
    {
        self::log('===================== START ==========================');

        try {
            Varien_Profiler::start('app');

            self::loadRequiredExtensions();

            self::app($storeCode, $etcDir);
            self::app()->getFrontController()->dispatch();

            Varien_Profiler::stop('app');
        }
        catch (Exception $e) {
            if (self::app()->isInstalled()) {
                self::printException($e);
                exit();
            }
            try {
                self::dispatchEvent('mage_run_exception', array('exception'=>$e));
                if (!headers_sent()) {
                	//header('Location:'.Mage::getBaseUrl().'install/');
                	header('Location:'.self::getUrl('install'));
                }
                else {
                    self::printException($e);
                }
            }
            catch (Exception $ne){
                self::printException($e);
                self::printException($ne);
            }
        }

        self::log('===================== FINISH ==========================');
    }

    /**
     * log facility (??)
     *
     * @param string $message
     * @param integer $level
     * @param string $file
     */
    public static function log($message, $level=null, $file = '')
    {
		return;

        static $loggers = array();

        $level  = is_null($level) ? Zend_Log::DEBUG : $level;
        $file   = empty($file) ? 'system.log' : $file;

        try {
            if (empty($loggers[$file])) {
                $logFile = Mage::getBaseDir('var').DS.'log'.DS.$file;
                $logDir = Mage::getBaseDir('var').DS.'log';

                if (!is_dir(Mage::getBaseDir('var').DS.'log')) {
                    mkdir(Mage::getBaseDir('var').DS.'log', 0777);
                }

                if (!file_exists($logFile)) {
                    file_put_contents($logFile,'');
                    chmod($logFile, 0777);
                }

                $format = '%timestamp% %priorityName% (%priority%): %message%' . PHP_EOL;
                $formatter = new Zend_Log_Formatter_Simple($format);
                $writer = new Zend_Log_Writer_Stream($logFile);
                $writer->setFormatter($formatter);
                $loggers[$file] = new Zend_Log($writer);
            }

            if (is_array($message) || is_object($message)) {
                $message = print_r($message, true);
            }

            $loggers[$file]->log($message, $level);
        }
        catch (Exception $e){

        }
    }

    /**
     * Display exception
     *
     * @param Exception $e
     */
    public static function printException(Exception $e)
    {
        mageSendErrorHeader();
        echo "<pre>";
        echo $e;
        echo "</pre>";
        mageSendErrorFooter();
    }


    /**
    * Tries to dynamically load an extension if not loaded
    *
    * @param string $ext
    * @return boolean
    */
    public static function loadExtension($ext)
    {
        if (extension_loaded($ext)) {
            return true;
        }

        if (ini_get('enable_dl') !== 1 || ini_get('safe_mode') === 1) {
            return false;
        }

        $file = (PHP_SHLIB_SUFFIX === 'dll' ? 'php_' : '') . $ext . '.' . PHP_SHLIB_SUFFIX;
        return @dl($file);
    }

    public static function loadRequiredExtensions()
    {
        $result = true;
        foreach (array('mcrypt', 'simplexml', 'mysqli', 'pdo_mysql', 'curl', 'iconv') as $ext) {
            if (!self::loadExtension($ext)) {
                $result = false;
            }
        }

        return $result;
    }
}
