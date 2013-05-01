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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Category controller
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_CategoryController extends Mage_Core_Controller_Front_Action
{
    /**
     * Category view
     */
    public function viewAction()
    {
        $category = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($this->getRequest()->getParam('id', false));
        Mage::getModel('catalog/design')->applyDesign($category, 2);

        if (!Mage::helper('catalog/category')->canShow($category)) {
            $this->_forward('noRoute');
            return;
        }

        Mage::register('current_category', $category);
        Mage::getSingleton('catalog/session')->setLastViewedCategoryId($category->getId());

        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();

        $update->addHandle($category->getLayoutUpdateHandle());
        $update->addHandle('CATEGORY_'.$category->getId());

        $this->loadLayoutUpdates();

        $update->addUpdate($category->getCustomLayoutUpdate());

        $this->generateLayoutXml()->generateLayoutBlocks();

        if ($root = $this->getLayout()->getBlock('root')) {
            $root->addBodyClass('categorypath-'.$category->getUrlPath())
                ->addBodyClass('category-'.$category->getUrlKey());
        }

        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }

}
