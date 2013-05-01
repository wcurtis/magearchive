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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Configuration for Admin model
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Model_Config extends Varien_Simplexml_Config
{
    protected $_sections;

    public function getSections($sectionCode=null, $websiteCode=null, $storeCode=null)
    {
        if (empty($this->_sections)) {

            $mergeConfig = Mage::getModel('core/config_base');

            $config = Mage::getConfig();
            $modules = $config->getNode('modules')->children();

            // check if local modules are disabled
            $disableLocalModules = (string)$config->getNode('global/disable_local_modules');
            $disableLocalModules = !empty($disableLocalModules) && (('true' === $disableLocalModules) || ('1' === $disableLocalModules));

            foreach ($modules as $modName=>$module) {
                if ($module->is('active')) {
                    if ($disableLocalModules && ('local' === (string)$module->codePool)) {
                        continue;
                    }
                    $configFile = $config->getModuleDir('etc', $modName).DS.'system.xml';
                    if ($mergeConfig->loadFile($configFile)) {
                        $config->extend($mergeConfig, true);
                    }
                }
            }
            #$config->applyExtends();

            $this->_sections = $config->getNode('sections');
        }

        return $this->_sections;
    }

    public function getSection($sectionCode=null, $websiteCode=null, $storeCode=null)
    {

        if ($sectionCode){
            return  $this->getSections()->$sectionCode;
        } elseif ($websiteCode) {
            return  $this->getSections()->$websiteCode;
        } elseif ($storeCode) {
            return  $this->getSections()->$storeCode;
        }
    }

    public function hasChildren ($node, $websiteCode=null, $storeCode=null, $isField=false)
    {
        $showTab = false;
        if ($storeCode) {
            if (isset($node->show_in_store)) {
                if ((int)$node->show_in_store) {
                    $showTab=true;
                }
            }
        }elseif ($websiteCode) {
            if (isset($node->show_in_website)) {
                if ((int)$node->show_in_website) {
                    $showTab=true;
                }
            }
        } elseif (isset($node->show_in_default)) {
                if ((int)$node->show_in_default) {
                    $showTab=true;
                }
        }
        if ($showTab) {
            if (isset($node->groups)) {
                foreach ($node->groups->children() as $children){
                    if ($this->hasChildren ($children, $websiteCode, $storeCode)) {
                    	return true;
                    }

                }
            }elseif (isset($node->fields)) {

                foreach ($node->fields->children() as $children){
                    if ($this->hasChildren ($children, $websiteCode, $storeCode, true)) {
                    	return true;
                    }
                }
            } else {
                return true;
            }
        }
        return false;

    }

    function getAttributeModule($sectionNode = null, $groupNode = null, $fieldNode = null)
    {
        $moduleName = 'adminhtml';
        if (is_object($sectionNode) && method_exists($sectionNode, 'attributes')) {
            $sectionAttributes = $sectionNode->attributes();
            $moduleName = isset($sectionAttributes['module']) ? (string)$sectionAttributes['module'] : $moduleName;
        }
        if (is_object($groupNode) && method_exists($groupNode, 'attributes')) {
            $groupAttributes = $groupNode->attributes();
            $moduleName = isset($groupAttributes['module']) ? (string)$groupAttributes['module'] : $moduleName;
        }
        if (is_object($fieldNode) && method_exists($fieldNode, 'attributes')) {
            $fieldAttributes = $fieldNode->attributes();
            $moduleName = isset($fieldAttributes['module']) ? (string)$fieldAttributes['module'] : $moduleName;
        }

        return $moduleName;
    }
}