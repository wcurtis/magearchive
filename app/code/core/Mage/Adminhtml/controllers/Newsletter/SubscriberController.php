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
 * Adminhtml newsletter subscribers controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Newsletter_SubscriberController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction() 
	{
	    if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        
        $this->getLayout()->getMessagesBlock()->setMessages(Mage::getSingleton('adminhtml/session')->getMessages(true));
		$this->loadLayout();
		
		$this->_setActiveMenu('newsletter/subscriber');
		
		$this->_addBreadcrumb(Mage::helper('newsletter')->__('Newsletter'), Mage::helper('newsletter')->__('Newsletter'));
		$this->_addBreadcrumb(Mage::helper('newsletter')->__('Subscribers'), Mage::helper('newsletter')->__('Subscribers'));
		
		$this->_addContent(
			$this->getLayout()->createBlock('adminhtml/newsletter_subscriber','subscriber')
		);
		
		$this->renderLayout();	
	}	
	
	public function gridAction()
    {
    	if($this->getRequest()->getParam('add') == 'subscribers') {
    		try {
	    		Mage::getModel('newsletter/queue')
	    			->load($this->getRequest()->getParam('queue'))
	    			->addSubscribersToQueue($this->getRequest()->getParam('subscriber', array()));
	    		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('newsletter')->__('Selected subscribers were successfully added to the selected queue'));
    		} 
    		catch (Exception $e) {
    			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
    		}
    	}
    	
    	$this->getLayout()->getMessagesBlock()->setMessages(Mage::getSingleton('adminhtml/session')->getMessages(true));
    	$grid = $this->getLayout()->createBlock('adminhtml/newsletter_subscriber_grid');
    	$this->getResponse()->setBody($grid->toHtml());
    }
    
    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('newsletter/subscriber');
    }
}// Class Mage_Adminhtml_Newsletter_SubscriberController END
