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
 * @package    Mage_Newsletter
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Newsletter subscribe controller
 *
 * @category   Mage
 * @package    Mage_Newsletter
 */
 class Mage_Newsletter_SubscriberController extends Mage_Core_Controller_Front_Action
 {

 	/**
 	 * Subscribe form
 	 */
    public function indexAction()
    {
        $this->_redirectReferer();
        /*
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('newsletter/subscribe','subscribe.content');
        $this->getLayout()->getMessagesBlock()->setMessages(
        	Mage::getSingleton('newsletter/session')->getMessages(true)
        );
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
        */
    }

    /**
 	 * New subscription action
 	 */
    public function newAction()
    {
    	$session = Mage::getSingleton('core/session');
    	try {
            $status = Mage::getModel('newsletter/subscriber')->subscribe($this->getRequest()->getParam('email'));
    	} catch (Exception $e) {
    	    $session->addError(Mage::helper('newsletter')->__('There was a problem with the subscription: %s', $e->getMessage()));
    	    $this->_redirectReferer();
    	    return;
    	}
        if ($status instanceof Exception) {
        	$session->addError(Mage::helper('newsletter')->__('There was a problem with the subscription: %s', $status));
        } else {
	        switch ($status) {
	        	case Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE:
	        		$session->addSuccess(Mage::helper('newsletter')->__('Confirmation request has been sent'));
	        		break;

	        	case Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
	        		$session->addSuccess(Mage::helper('newsletter')->__('Thank you for your subscription'));
	        		break;

	        	default:
	        	    $session->addSuccess(Mage::helper('newsletter')->__('Thank you for your subscription'));
	        		break;
	        }
        }

        $this->_redirectReferer();
    }

    /**
     * Subscription confirm action
     */
    public function confirmAction()
    {
    	$id = (int) $this->getRequest()->getParam('id');
    	$subscriber = Mage::getModel('newsletter/subscriber')
    		->load($id);

    	if($subscriber->getId() && $subscriber->getCode()) {
    		 if($subscriber->confirm($this->getRequest()->getParam('code'))) {
    		 	Mage::getSingleton('newsletter/session')->addSuccess(Mage::helper('newsletter')->__('Your subscription was successfully confirmed'));
    		 } else {
    		 	Mage::getSingleton('newsletter/session')->addError(Mage::helper('newsletter')->__('Invalid subscription confirmation code'));
    		 }
    	} else {
    		 Mage::getSingleton('newsletter/session')->addError(Mage::helper('newsletter')->__('Invalid subscription ID'));
    	}

        $this->_redirectUrl(Mage::getBaseUrl());
    }

    public function unsubscribeAction()
    {
    	$session = Mage::getSingleton('newsletter/session');
    	$result = Mage::getModel('newsletter/subscriber')->unsubscribe($this->getRequest()->getParam('email'));

    	if ($result instanceof Exception) {
    		$session->addError(Mage::helper('newsletter')->__('There was a problem with the un-subscription: %s', $status));
    	} else {
    		$session->addSuccess(Mage::helper('newsletter')->__('You have been successfully unsubscribed'));
    	}

        $this->_redirectReferer();
    }
 }
