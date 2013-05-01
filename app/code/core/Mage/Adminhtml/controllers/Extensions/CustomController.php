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

require_once 'Varien/Pear/Package.php';

/**
 * Extension controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Extensions_CustomController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('system/extensions/custom');

        $this->_addContent($this->getLayout()->createBlock('adminhtml/extensions_custom_edit'));

        $this->_addLeft($this->getLayout()->createBlock('adminhtml/extensions_custom_edit_tabs'));

        $this->renderLayout();
    }

    public function resetAction()
    {
        Mage::getSingleton('adminhtml/session')->unsCustomExtensionPackageFormData();
        $this->_redirect('*/*/edit');
    }

    public function loadAction()
    {
        $package = $this->getRequest()->getParam('id');
        if ($package) {
            $filename = Mage::getBaseDir('var').DS.'pear'.DS.$package.'.ser';
            $session = Mage::getSingleton('adminhtml/session');
            if (is_readable($filename)) {
                $p = file_get_contents($filename);
                $data = unserialize($p);
                $session->setCustomExtensionPackageFormData($data);
                $session->addSuccess(Mage::helper('adminhtml')->__("Package %s data was successfully loaded", $package));
            } else {
                $session->addError(Mage::helper('adminhtml')->__("File %s.ser could not be read", $package));
            }
        }
        $this->_redirect('*/*/edit');
    }

    public function saveAction()
    {
        $session = Mage::getSingleton('adminhtml/session');
        $p = $this->getRequest()->getPost();

        if (!empty($p['_create'])) {
            $create = true;
            unset($p['_create']);
        }

        $session->setCustomExtensionPackageFormData($p);
        try {
            $ext = Mage::getModel('adminhtml/extension');
            $ext->setData($p);
            $output = $ext->getPear()->getOutput();
            if ($ext->savePackage()) {
                $session->addSuccess('Package data was successfully saved');
            } else {
                $session->addError('There was a problem saving package data');
                $this->_redirect('*/*/edit');
            }
            if (empty($create)) {
                $this->_redirect('*/*/edit');
            } else {
                $this->_forward('create');
            }
        }
        catch(Mage_Core_Exception $e){ // Mage::throwException(Mage::helper('adminhtml')->__('aasdasdsadasd')) || throw Mage::exception('')
            $session->addError($e->getMessage());
            $this->_redirect('*/*');
        }
        catch(Exception $e){
            $session->addError($e->getMessage());
            $this->_redirect('*/*');
        }
    }

    public function createAction()
    {
        $session = Mage::getSingleton('adminhtml/session');
        try {
            $p = $this->getRequest()->getPost();
            $session->setCustomExtensionPackageFormData($p);
            $ext = Mage::getModel('adminhtml/extension');
            $ext->setData($p);
            $result = $ext->createPackage();
            $pear = Varien_Pear::getInstance();
            if ($result) {
                $data = $pear->getOutput();
                $session->addSuccess($data[0]['output']);
                $this->_redirect('*/*');
                #$this->_forward('reset');
            } else {
                $session->addError($result->getMessage());
                $this->_redirect('*/*');
            }
        }
        catch(Mage_Core_Exception $e){ // Mage::throwException(Mage::helper('adminhtml')->__('aasdasdsadasd')) || throw Mage::exception('')
            $session->addError($e->getMessage());
            $this->_redirect('*/*');
        }
        catch(Exception $e){
            $session->addError($e->getMessage());
            $this->_redirect('*/*');
        }
    }

    public function testAction()
    {
        Varien_Pear::getInstance()->runHtmlConsole(array('command'=>'list-channels'));
    }

}