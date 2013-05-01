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
class Mage_Adminhtml_System_ConfigController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        $this->setFlag('index', 'no-preDispatch', true);
        return parent::_construct();
    }

    public function indexAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $current = $this->getRequest()->getParam('section');
        $websiteCode = $this->getRequest()->getParam('website');
        $storeCode = $this->getRequest()->getParam('store');


        $configFields = Mage::getSingleton('adminhtml/config');
        $sections=$configFields->getSections($current);

        //        $sections=(array)$sections;


        $section = $sections->$current;

        $hasChildren = $configFields->hasChildren($section, $websiteCode, $storeCode);

        if (!$hasChildren && $current) {
            $this->_redirect('*/*/', array('website'=>$websiteCode, 'store'=>$storeCode));
        }

        $this->loadLayout();

        $this->_setActiveMenu('system/config');

        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('System'), Mage::helper('adminhtml')->__('System'), Mage::getUrl('*/system'));

        $this->getLayout()->getBlock('left')
            ->append($this->getLayout()->createBlock('adminhtml/system_config_tabs')->initTabs());

        $this->_addContent($this->getLayout()->createBlock('adminhtml/system_config_edit')->initForm());
        $this->_addJs($this->getLayout()->createBlock('core/template')->setTemplate('system/shipping/ups.phtml'));
        $this->_addJs($this->getLayout()->createBlock('core/template')->setTemplate('system/config/js.phtml'));
        $this->_addJs($this->getLayout()->createBlock('core/template')->setTemplate('system/shipping/applicable_country.phtml'));
		$this->renderLayout();
    }

    public function saveAction()
    {
        try {
            Mage::app()->removeCache('config_global');
            Mage::getResourceModel('adminhtml/config')->saveSectionPost(
                $this->getRequest()->getParam('section'),
                $this->getRequest()->getParam('website'),
                $this->getRequest()->getParam('store'),
                $this->getRequest()->getPost('groups')
            );
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Configuration successfully saved'));
            $this->_redirect('*/*/edit', array('_current'=>array('section', 'website', 'store')));
            return;
        } catch( Exception $e ) {
            Mage::getSingleton('adminhtml/session')->addError(nl2br($e->getMessage()));
            $this->_redirect('*/*/edit', array('_current'=>array('section', 'website', 'store')));
        }
    }

    public function exportTableratesAction()
    {
        $websiteModel = Mage::getModel('core/website')->load($this->getRequest()->getParam('website'));

        if ($this->getRequest()->getParam('conditionName')) {
            $conditionName = $this->getRequest()->getParam('conditionName');
        } else {
            $conditionName = $websiteModel->getConfig('carriers/tablerate/condition_name');
        }

        $tableratesCollection = Mage::getResourceModel('shipping/carrier_tablerate_collection');
        /* @var $tableratesCollection Mage_Shipping_Model_Mysql4_Carrier_Tablerate_Collection */
        $tableratesCollection->setConditionFilter($conditionName);
        $tableratesCollection->setWebsiteFilter($websiteModel->getId());
        $tableratesCollection->load();

        $csv = '';

        $conditionName = Mage::getModel('shipping/carrier_tablerate')->getCode('condition_name_short', $conditionName);

        $csvHeader = array('"'.Mage::helper('adminhtml')->__('Country').'"', '"'.Mage::helper('adminhtml')->__('Region/State').'"', '"'.Mage::helper('adminhtml')->__('Zip/Postal Code').'"', '"'.$conditionName.'"', '"'.Mage::helper('adminhtml')->__('Shipping Price').'"');
        $csv .= implode(',', $csvHeader)."\n";

        foreach ($tableratesCollection->getItems() as $item) {
            if ($item->getData('dest_country') == '') {
                $country = '*';
            } else {
                $country = $item->getData('dest_country');
            }
            if ($item->getData('dest_region') == '') {
                $region = '*';
            } else {
                $region = $item->getData('dest_region');
            }
            if ($item->getData('dest_zip') == '') {
                $zip = '*';
            } else {
                $zip = $item->getData('dest_zip');
            }

            $csvData = array($country, $region, $zip, $item->getData('condition_value'), $item->getData('price'));
            foreach ($csvData as $cell) {
                $cell = '"'.str_replace('"', '""', $cell).'"';
            }
            $csv .= implode(',', $csvData)."\n";
        }

        header("Content-disposition: attachment; filename=tablerates.csv");
        echo $csv;
    }

    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('system/config');
    }

}
