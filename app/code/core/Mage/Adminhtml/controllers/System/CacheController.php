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
 * config controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_System_CacheController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('system/cache');

        $this->_addContent($this->getLayout()->createBlock('adminhtml/system_cache_edit')->initForm());

        $this->renderLayout();
    }

    public function saveAction()
    {
        $a = $this->getRequest()->getPost('refresh');
        if (!empty($a) && is_array($a)) {
            if (!empty($a['catalog_rewrites'])) {
                Mage::getSingleton('catalog/url')->refreshRewrites();
            }
            if (!empty($a['all_cache'])) {
                Mage::app()->cleanCache();
            }
        }

        $a = $this->getRequest()->getPost('enable');
        if (!empty($a) && is_array($a)) {
            foreach ($a as $type=>$value) {
                if ($value&2) {
                    Mage::app()->cleanCache(array($type));
                    if ($type==='config') {
                        Mage::app()->getConfig()->removeCache();
                    }
                    $a[$type] = $value&1;
                }
            }

            Mage::app()->saveCache(serialize($a), 'use_cache', array(), null);
        }
        $this->_redirect('*/*');
    }
}
