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
 * sales admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Cms_BlockController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('cms/block')
            ->_addBreadcrumb(Mage::helper('cms')->__('CMS'), Mage::helper('cms')->__('CMS'))
            ->_addBreadcrumb(Mage::helper('cms')->__('Manage Blocks'), Mage::helper('cms')->__('Manage Blocks'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('adminhtml/cms_block'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('block_id');
        $model = Mage::getModel('cms/block');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cms')->__('This block no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getBlockData(true);
        if (! empty($data)) {
            $model->setData($data);
        }

        Mage::register('cms_block', $model);

        $this->_initAction()
            ->_addBreadcrumb($id ? Mage::helper('cms')->__('Edit Block') :  Mage::helper('cms')->__('New Block'), $id ?  Mage::helper('cms')->__('Edit Block') :  Mage::helper('cms')->__('New Block'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/cms_block_edit')->setData('action', $this->getUrl('*/cms_block/save')))
            ->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('cms/block');
//            if ($id = $this->getRequest()->getParam('block_id')) {
//                $model->load($id);
//                if ($id != $model->getId()) {
//                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cms')->__('The block you are trying to save no longer exists'));
//                    Mage::getSingleton('adminhtml/session')->setPageData($data);
//                    $this->_redirect('*/*/edit', array('block_id' => $this->getRequest()->getParam('block_id')));
//                    return;
//                }
//            }
            $model->setData($data);
            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cms')->__('Block was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setBlockData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('block_id'=>$model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setBlockData($data);
                $this->_redirect('*/*/edit', array('block_id' => $this->getRequest()->getParam('block_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('block_id')) {
            try {
                $model = Mage::getModel('cms/block');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cms')->__('Block was succesfully deleted'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('block_id' => $this->getRequest()->getParam('block_id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cms')->__('Unable to find a block to delete'));
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('cms/block');
    }

}
