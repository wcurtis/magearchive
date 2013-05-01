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
 * Catalog category controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Catalog_CategoryController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialization category object in registry
     *
     * @return this
     */
    protected function _initCategory()
    {
        Mage::register('category', Mage::getModel('catalog/category'));
        if ($id = (int) $this->getRequest()->getParam('id')) {
            Mage::registry('category')->setStoreId((int)$this->getRequest()->getParam('store'))
                ->load($id);
        }
        return $this;
    }
    /**
     * Catalog categories index action
     */
    public function indexAction()
    {
        $this->_forward('edit');
    }

    /**
     * Add new category form
     */
    public function addAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit category page
     */
    public function editAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('catalog/categories');
        $this->getLayout()->getBlock('root')->setCanLoadExtJs(true)
            ->setContainerCssClass('catalog-categories');

        $this->_initCategory();
        $data = Mage::getSingleton('adminhtml/session')->getCategoryData(true);
        if (isset($data['general'])) {
            Mage::registry('category')->addData($data['general']);
        }
        //$this->_addBreadcrumb(Mage::helper('catalog')->__('Catalog'), Mage::helper('catalog')->__('Catalog'));
        $this->_addBreadcrumb(Mage::helper('catalog')->__('Manage Catalog Categories'), Mage::helper('catalog')->__('Manage Categories'));

        $this->_addLeft(
            $this->getLayout()->createBlock('adminhtml/catalog_category_tree', 'category.tree')
        );

        $this->_addContent(
            $this->getLayout()->createBlock('adminhtml/catalog_category_edit')
        );

        $this->renderLayout();
    }

    /**
     * Move category tree node action
     */
    public function moveAction()
    {
        $nodeId         = $this->getRequest()->getPost('id', false);
        $parentNodeId   = $this->getRequest()->getPost('pid', false);
        $prevNodeId     = $this->getRequest()->getPost('aid', false);

        try {
            $tree = Mage::getResourceModel('catalog/category_tree')
                ->load();

            $node = $tree->getNodeById($nodeId);
            $parentNode     = $node->getParent();
            $newParentNode  = $tree->getNodeById($parentNodeId);
            $prevNode       = $tree->getNodeById($prevNodeId);
            if (!$prevNode || !$prevNode->getId()) {
                $prevNode = null;
            }

            $tree->moveNodeTo($node, $newParentNode, $prevNode);
            $category = Mage::getModel('catalog/category')
                ->setStoreId((int) $this->getRequest()->getParam('store'))
                ->load($nodeId)
                ->move($newParentNode->getId())
                ->save();

            $this->getResponse()->setBody("SUCCESS");

            /*$parentCategory = Mage::getModel('catalog/category')
                ->setStoreId(0)
                ->load($parentNode->getId())
                ->save();

            $category = Mage::getModel('catalog/category')
                ->setStoreId(0)
                ->load($nodeId)
                ->setParentId($newParentNode->getId())
                ->save();

            $newParentCategory = Mage::getModel('catalog/category')
                ->setStoreId(0)
                ->load($newParentNode->getId())
                ->save();*/
        }
        catch (Exception $e){
            $this->getResponse()->setBody(Mage::helper('catalog')->__('Category move error'));
        }
    }

    /**
     * Delete category action
     */
    public function deleteAction()
    {
        if ($id = (int) $this->getRequest()->getParam('id')) {
            try {
                Mage::getModel('catalog/category')->load($id)
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('Category deleted'));
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('Category delete error'));
                $this->getResponse()->setRedirect(Mage::getUrl('*/*/edit', array('_current'=>true)));
                return;
            }
        }
        $this->getResponse()->setRedirect(Mage::getUrl('*/*/', array('_current'=>true, 'id'=>null)));
    }

    /**
     * Category save
     */
    public function saveAction()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        if ($data = $this->getRequest()->getPost()) {
            $category = Mage::getModel('catalog/category')
                ->setStoreId($storeId)
                ->load($this->getRequest()->getParam('id'))
                ->addData($data['general']);

            $category->setAttributeSetId($category->getDefaultAttributeSetId());

            if (isset($data['category_products'])) {
                $products = array();
                parse_str($data['category_products'], $products);
                $category->setPostedProducts($products);
            }

            try {
                $category->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('Category saved'));
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage())
                    ->setCategoryData($data);
                $this->getResponse()->setRedirect(Mage::getUrl('*/*/edit', array('id'=>$category->getId(), 'store'=>$storeId)));
                return;
            }
        }

        $this->getResponse()->setRedirect(Mage::getUrl('*/*/edit', array('id'=>$category->getId(), 'store'=>$storeId)));
    }

    public function gridAction()
    {
        $this->_initCategory();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/catalog_category_tab_product')->toHtml()
        );
    }

    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('catalog/categories');
    }

}
