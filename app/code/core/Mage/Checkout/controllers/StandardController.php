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
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Checkout_StandardController extends Mage_Core_Controller_Front_Action 
{
    protected $_data = array();

	public function preDispatch()
	{
		$this->_data['params'] = $this->getRequest()->getParams();
		parent::preDispatch();
	}
    
    function indexAction()
    {
        $this->_redirect('checkout/standard/shipping');
    }
    
    function shippingAction()
    {
        // check customer auth
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            return;
        }
        
        // TODO: change address id
        $addressId = Mage::getSingleton('customer/session')->getCustomer()->getPrimaryAddress('shipping');
        $address = Mage::getModel('customer/address')->getRow($addressId);
        
        $block = $this->getLayout()->createBlock('core/template', 'checkout.shipping')
            ->setTemplate('checkout/shipping.phtml')
            ->assign('data', $this->_data)
            ->assign('address', $address)
            ->assign('action', Mage::getUrl('checkout/standard/shippingPost'));
        $this->getLayout()->getBlock('content')->append($block);
    }
    
    function shippingPostAction()
    {
        $this->_redirect('checkout/standard/payment');
    }
    
    function paymentAction()
    {
        $block = $this->getLayout()->createBlock('core/template', 'checkout.payment')
            ->setTemplate('checkout/payment.phtml')
            ->assign('data', $this->_data);
        $this->getLayout()->getBlock('content')->append($block);
    }
    
    function paymentPostAction()
    {
        $this->_redirect('checkout/standard/overview');
    }
    
    function overviewAction()
    {
        $block = $this->getLayout()->createBlock('core/template', 'checkout.overview')
            ->setTemplate('checkout/overview.phtml')
            ->assign('data', $this->_data);
        $this->getLayout()->getBlock('content')->append($block);
    }
    
    function overviewPostAction()
    {
        $this->_redirect('checkout/standard/checkout');
    }
    
    function successAction()
    {
        $block = $this->getLayout()->createBlock('core/template', 'checkout.success')
            ->setTemplate('checkout/success.phtml')
            ->assign('data', $this->_data);
        $this->getLayout()->getBlock('content')->append($block);
    }
}