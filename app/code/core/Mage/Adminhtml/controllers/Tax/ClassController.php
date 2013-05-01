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
 * Adminhtml common tax class controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Tax_ClassController extends Mage_Adminhtml_Controller_Action
{
    public function saveAction()
    {
        if( $postData = $this->getRequest()->getPost() ) {
            $class = Mage::getModel('tax/class');
            $class->setData($postData);

            if($class->itemExists() === false) {
                try {
                    $class->save();
                    $classId = $class->getClassId();
                    $classType = $class->getClassType();
                    $classTypeString = strtolower($class->getClassType());
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('tax')->__('Tax class was successfully saved'));
                    $this->getResponse()->setRedirect(Mage::getUrl("*/tax_class_{$classTypeString}"));
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tax')->__('Error while saving this tax class. Please try again later.'));
                    Mage::getSingleton('adminhtml/session')->setClassData($postData);
                    $this->_redirectReferer();
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tax')->__('Error while saving this tax class. Class with the same name already exists.'));
                Mage::getSingleton('adminhtml/session')->setClassData($postData);
                $this->_redirectReferer();
            }
        }
    }

    public function editAction()
    {
        $classType = strtolower($this->getRequest()->getParam('classType'));
        $classTypePhrase = ucfirst($classType);

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('tax')->__("%s Tax Classes", $classTypePhrase), Mage::helper('tax')->__("%s Tax Classes Title", $classTypePhrase), Mage::getUrl('*/tax_class_'.$classType))
            ->_addBreadcrumb(Mage::helper('tax')->__("Edit %s Tax Class", $classTypePhrase), Mage::helper('tax')->__("Edit %s Tax Class Title", $classTypePhrase))
            ->_addContent($this->getLayout()->createBlock('adminhtml/tax_class_page_edit'))
        ;

        $this->renderLayout();
    }

    public function deleteAction()
    {
        try {
            $classId = $this->getRequest()->getParam('classId');
            $classType = strtolower($this->getRequest()->getParam('classType'));
            $classTypeString = strtolower($classType);

            $class = Mage::getSingleton('tax/class');
            $class->setClassId($classId);
            $class->delete();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('tax')->__('Tax class was successfully deleted'));
            $this->getResponse()->setRedirect(Mage::getUrl("*/tax_class_{$classTypeString}"));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tax')->__('Error while deleting this tax class. Please try again later.'));
            $this->_redirectReferer();
        }
    }

    public function saveGroupAction()
    {
        if( $postData = $this->getRequest()->getPost() ) {
            $group = Mage::getModel('tax/class_group');
            $group->setData($postData);
            try {
                $group->save();
                $classId = $this->getRequest()->getParam('classId');
                $classType = $this->getRequest()->getParam('classType');
                $classTypeString = strtolower($classType);
                $this->getResponse()->setRedirect("adminhtml/tax_class_{$classTypeString}");
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('tax')->__('Tax class was successfully saved'));
            } catch ( Exception $e ) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tax')->__('Error while adding a group. Please try again later.'));
                $this->_redirectReferer();
            }
        }
    }

    public function deleteGroupAction()
    {
        $groupId = $this->getRequest()->getParam('groupId');
        $classId = $this->getRequest()->getParam('classId');
        $classType = $this->getRequest()->getParam('classType');

        try {
            $group = Mage::getModel('tax/class_group');
            $group->setGroupId($groupId);
            $group->delete();
            $this->getResponse()->setRedirect(Mage::getUrl("*/tax_class/edit/classId/{$classId}/classType/{$classType}"));
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('tax')->__('Group was successfully deleted'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tax')->__('Error while deleting a group. Please try again later.'));
            $this->_redirectReferer();
        }
     }

    /**
     * Initialize action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $classType = strtolower($this->getRequest()->getParam('classType'));
        $this->loadLayout()
            ->_setActiveMenu('sales/tax/tax_classes_' . $classType)
            ->_addBreadcrumb(Mage::helper('tax')->__('Sales'), Mage::helper('tax')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('tax')->__('Tax'), Mage::helper('tax')->__('Tax'))
//            ->_addLeft($this->getLayout()->createBlock('adminhtml/tax_tabs', 'tax_tabs')->setActiveTab('tax_class_' . $classType))
        ;

        return $this;
    }

}
