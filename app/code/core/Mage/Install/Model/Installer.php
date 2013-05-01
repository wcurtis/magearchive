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
 * @package    Mage_Install
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Installer model
 *
 */
class Mage_Install_Model_Installer extends Varien_Object
{
    const INSTALLER_HOST_RESPONSE   = 'MAGENTO';

    /**
     * Checking install status of application
     *
     * @return bool
     */
    public function isApplicationInstalled()
    {
        return Mage::app()->isInstalled();
    }

    public function checkDownloads()
    {
        try {
            $result = Mage::getModel('install/installer_pear')->checkDownloads();
            $result = true;
        } catch (Exception $e) {
            $result = false;
        }
        $this->setDownloadCheckStatus($result);
        return $result;
    }

    /**
     * Check server settings
     *
     * @return bool
     */
    public function checkServer()
    {
        try {
            Mage::getModel('install/installer_filesystem')->install();
            Mage::getModel('install/installer_env')->install();
            $result = true;
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * Retrieve server checking result status
     *
     * @return unknown
     */
    public function getServerCheckStatus()
    {
        $status = $this->getData('server_check_status');
        if (is_null($status)) {
            $status = $this->checkServer();
        }
        return $status;
    }

    /**
     * Installation config data
     *
     * @param   array $data
     * @return  Mage_Install_Model_Installer
     */
    public function installConfig($data)
    {
        $data['db_active'] = true;
        Mage::getSingleton('install/installer_db')->checkDatabase($data);
        Mage::getSingleton('install/installer_config')
            ->setConfigData($data)
            ->install();
        return $this;
    }

    /**
     * Database installation
     *
     * @return Mage_Install_Model_Installer
     */
    public function installDb()
    {
        Mage_Core_Model_Resource_Setup::applyAllUpdates();
        $data = Mage::getSingleton('install/session')->getConfigData();

        /**
         * Saving host information into DB
         */
        $setupModel = new Mage_Core_Model_Resource_Setup('core_setup');
        if (!empty($data['host'])) {
            $setupModel->setConfigData(Mage_Core_Model_Store::XML_PATH_UNSECURE_HOST, $data['host']);
        }
        if (!empty($data['base_path'])) {
            $setupModel->setConfigData(Mage_Core_Model_Store::XML_PATH_UNSECURE_PATH, $data['base_path']);
        }
        if (!empty($data['protocol'])) {
            $setupModel->setConfigData(Mage_Core_Model_Store::XML_PATH_UNSECURE_PROTOCOL, $data['protocol']);
        }
        if (!empty($data['port'])) {
            $setupModel->setConfigData(Mage_Core_Model_Store::XML_PATH_UNSECURE_PORT, $data['port']);
        }
        if (!empty($data['secure_host'])) {
            $setupModel->setConfigData(Mage_Core_Model_Store::XML_PATH_SECURE_HOST, $data['secure_host']);
        }
        if (!empty($data['secure_base_path'])) {
            $setupModel->setConfigData(Mage_Core_Model_Store::XML_PATH_SECURE_PATH, $data['secure_base_path']);
        }
        if (!empty($data['secure_protocol'])) {
            $setupModel->setConfigData(Mage_Core_Model_Store::XML_PATH_SECURE_PROTOCOL, $data['secure_protocol']);
        }
        if (!empty($data['secure_port'])) {
            $setupModel->setConfigData(Mage_Core_Model_Store::XML_PATH_SECURE_PORT, $data['secure_port']);
        }

        /**
         * Saving locale information into DB
         */
        $locale = Mage::getSingleton('install/session')->getLocaleData();
        if (!empty($locale['locale'])) {
            $setupModel->setConfigData(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $locale['locale']);
        }
        if (!empty($locale['timezone'])) {
            $setupModel->setConfigData(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE, $locale['timezone']);
        }
        if (!empty($locale['currency'])) {
            $setupModel->setConfigData(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE, $locale['currency']);
        }

        /**
         * Temporary urls fix
         * @todo change this whith media library development
         */
        $basePath = isset($data['base_path']) ? $data['base_path'] : '/';
        $setupModel->updateTable('core/config_data',
            'value like \'%{{base_path}}%\'',
            "value=REPLACE(value, '{{base_path}}', '".$basePath."')");
        return $this;
    }

    public function createAdministrator($data)
    {
        $user = Mage::getModel('admin/user')->load(1)->addData($data);
        $user->save();

        /*Mage::getModel("permissions/user")->setRoleId(1)
            ->setUserId($user->getId())
            ->setFirstname($user->getFirstname())
            ->add();*/

        return $this;
    }

    public function installEnryptionKey($key)
    {
        if ($key) {
            Mage::helper('core')->validateKey($key);
        }
        Mage::getSingleton('install/installer_config')->replaceTmpEncryptKey($key);
        return $this;
    }

    public function finish()
    {
        Mage::getSingleton('install/installer_config')->replaceTmpInstallDate();
        Mage::app()->cleanCache();

        $cacheData = serialize(array(
            'config'     => 1,
            'layout'     => 1,
            'block_html' => 1,
            'eav'        => 1,
            'translate'  => 1,
            'pear'       => 1,
        ));

        Mage::app()->saveCache($cacheData, 'use_cache', array(), null);

        return $this;
    }
}