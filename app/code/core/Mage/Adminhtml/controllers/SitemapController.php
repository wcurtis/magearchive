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
 * @package    Mage_Sitemap
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_SitemapController extends  Mage_Adminhtml_Controller_Action
{

     /**
     * Create initial action
     */
    protected function _initAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('adminhtml/sistem_sitemap');
        return $this;
    }

    /**
     * Create index sitemap action
     */
    public function indexAction()
    {
        $this->_initAction();

        /**
         * Append customers block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('adminhtml/sitemap')
        );

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('adminhtml/sistem_sitemap');



        $this->_addContent($this->getLayout()->createBlock('adminhtml/sitemap_new'));

        $this->renderLayout();
    }


    /**
     * Save urlrewrite action
     */
    public function saveAction()
    {

        if ($data = $this->getRequest()->getPost()) {

            $model = Mage::getModel('sitemap/sitemap');

            if (empty($data['sitemap_filename'])) {
            	Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sitemap')->__('Filename can\'t be empty'))->setSitemapData($data);
            	$this->getResponse()->setRedirect($this->getUrl('*/sitemap/new', array('id'=>$model->getId())));
            	return;
            }

            try {
            	//if (!$model->getId()) {

            		$model->setData($data);


            	$model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sitemap')->__('Sitemap was successfully saved'));
            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage())->setSitemapData($data);

                $this->getResponse()->setRedirect($this->getUrl('*/sitemap/new', array('id'=>$model->getId())));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/sitemap'));
    }

    /**
     * Create edit sitemap action
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $sitemap = Mage::getModel('sitemap/sitemap')->load($id);
        if ($sitemap->getId()) {

        Mage::register('sitemap_sitemap', $sitemap);

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Sitemap'),
            Mage::helper('adminhtml')->__('Edit Sitemap'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/sitemap_edit'))
            ->renderLayout();

        } else {
            $this->getResponse()->setRedirect($this->getUrl('*/sitemap'));
        }
    }


    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $sitemap = Mage::getModel('sitemap/sitemap')->load($id);

            try {

                $sitemap->delete();

            }
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')
                    ->addError($e->getMessage());
            }
        }

        $this->getResponse()->setRedirect($this->getUrl('*/sitemap'));
    }

    public function generateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $sitemap = Mage::getModel('sitemap/sitemap')->load($id);
        if ($sitemap->getId()) {
            $xml = $sitemap->generateSitemap();
            $file = Mage::getBaseDir('base') . '/' . $sitemap->getSitemapPath() . '/' . $sitemap->getSitemapFilename();

            $file = str_replace('//', '/', $file);

            $fp = fopen($file, 'w');
            fputs($fp, $xml);
            fclose($fp);
        }
        $this->getResponse()->setRedirect($this->getUrl('*/sitemap'));

    }
}


