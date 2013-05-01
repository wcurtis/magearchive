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
 * @package    Mage_Cms
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Cms page helper
 *
 */
class Mage_Cms_Helper_Page extends Mage_Core_Helper_Abstract
{
    /**
    * Renders CMS page
    *
    * Call from controller action
    *
    * @param Mage_Core_Controller_Front_Action $action
    * @param integer $pageId
    * @return boolean
    */
    public function renderPage(Mage_Core_Controller_Front_Action $action, $pageId=null)
    {
        $page = Mage::getSingleton('cms/page');
        if (!is_null($pageId) && $pageId!==$page->getId()) {
            $page->setStoreId(Mage::app()->getStore()->getId());
            if (!$page->load($pageId)) {
                return false;
            }
        }

        if (!$page->getId()) {
            return false;
        }

//        $customerSession = Mage::getSingleton('customer/session');
//        if (!$customerSession->authenticate($action)) {
//            $customerSession->setBeforeAuthUrl(Mage::getBaseUrl().$page->getIdentifier());
//            return true;
//        }

        $action->loadLayout(null, false, false);
        $action->getLayout()->getUpdate()->addUpdate($page->getLayoutUpdateXml());
        $action->generateLayoutXml()->generateLayoutBlocks();

        // show breadcrumbs
        if (Mage::getStoreConfig('web/default/show_cms_breadcrumbs')
            && ($breadcrumbs = $action->getLayout()->getBlock('breadcrumbs'))
            && ($page->getIdentifier()!==Mage::getStoreConfig('web/default/cms_home_page'))
            && ($page->getIdentifier()!==Mage::getStoreConfig('web/default/cms_no_route'))) {
                $breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Go to Home Page'), 'link'=>Mage::getBaseUrl()));
                $breadcrumbs->addCrumb('cms_page', array('label'=>$page->getTitle(), 'title'=>$page->getTitle()));
        }

        if ($root = $action->getLayout()->getBlock('root')) {
            $template = (string)Mage::getConfig()->getNode('global/cms/layouts/'.$page->getRootTemplate().'/template');
            $root->setTemplate($template);
            $root->addBodyClass('cms-'.$page->getIdentifier());
        }

        if ($head = $action->getLayout()->getBlock('head')) {
            $head->setTitle($page->getTitle());
        }

        if ($content = $action->getLayout()->getBlock('content')) {
            $block = $action->getLayout()->createBlock('cms/page')->setPage($page);
            $content->append($block);
        }

        $action->renderLayout();

        return true;
    }
}