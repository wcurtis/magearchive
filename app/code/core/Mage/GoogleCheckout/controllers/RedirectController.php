<?php


class Mage_GoogleCheckout_RedirectController extends Mage_Core_Controller_Front_Action
{
    public function checkoutAction()
    {
        $session = Mage::getSingleton('checkout/session');

        $api = Mage::getModel('googlecheckout/api')
            ->checkout($session->getQuote());

        $response = $api->getResponse();
        if ($api->getError()) {
            Mage::getSingleton('checkout/session')->addError($api->getError());
            $url = Mage::getUrl('checkout/cart');
        } else {
            $url = $api->getRedirectUrl();
            $session->setGoogleCheckoutQuoteId($session->getQuoteId());
            $session->unsQuoteId();
        }
        $this->getResponse()->setRedirect($url);
    }

    public function cartAction()
    {
        $session = Mage::getSingleton('checkout/session');

        if ($session->getQuoteId()) {
            $session->getQuote()->delete();
        }

        $session->setQuoteId($session->getGoogleCheckoutQuoteId());
        $session->unsGoogleCheckoutQuoteId();

        $this->_redirect('checkout/cart');
    }

    public function continueAction()
    {
        $session = Mage::getSingleton('checkout/session');

        if ($quoteId = $session->getGoogleCheckoutQuoteId()) {
            $quote = Mage::getModel('sales/quote')->load($quoteId)
                ->setIsActive(false)->save();
        }

        $session->unsGoogleCheckoutQuoteId();

        $url = Mage::getStoreConfig('google/checkout/continue_shopping_url');
        if (empty($url)) {
            $this->_redirect('');
        } elseif (substr($url, 0, 4)==='http') {
            $this->getResponse()->setRedirect($url);
        } else {
            $this->_redirect($url);
        }
    }
}