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


class Mage_Checkout_Model_Session extends Mage_Core_Model_Session_Abstract
{
    const CHECKOUT_STATE_BEGIN = 'begin';

    protected $_quote = null;
    protected $_processedQuote = null;

    public function __construct()
    {
        $this->init('checkout');
        Mage::dispatchEvent('checkout_session_init', array('checkout_session'=>$this));
    }

    public function unsetAll()
    {
        parent::unsetAll();
        $this->_quote = null;
    }

    /**
     * Enter description here...
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (empty($this->_quote)) {
            $quote = Mage::getModel('sales/quote');
            /* @var $quote Mage_Sales_Model_Quote */
            if ($this->getQuoteId()) {
                $quote->load($this->getQuoteId());
                if (!$quote->getId()) {
                    $this->setQuoteId(null);
                }
            }
            if (!$this->getQuoteId()) {
                $quote->save();
                Mage::dispatchEvent('checkout_quote_init', array('quote'=>$quote));
                $this->setQuoteId($quote->getId());
            }
            if ($this->getQuoteId() && !$quote->getCustomerId()) {
                $customerSession = Mage::getSingleton('customer/session');
                if ($customerSession->isLoggedIn()) {
                    $quote->setCustomer($customerSession->getCustomer())->save();
                }
            }
            $this->_quote = $quote;
        }
        return $this->_quote;
    }

    public function loadCustomerQuote()
    {
        // coment until quote fix
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $customerQuote = Mage::getResourceModel('sales/quote_collection')
            ->loadByCustomerId($customerId);
        if ($customerQuote) {
            if ($this->getQuoteId()) {
                foreach ($this->getQuote()->getAllItems() as $item) {
                    $item->setId(null);
                    $found = false;
                    foreach ($customerQuote->getAllItems() as $quoteItem) {
                        if ($quoteItem->getProductId()==$item->getProductId()) {
                            $quoteItem->setQty($quoteItem->getQty() + $item->getQty());
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $customerQuote->addItem($item);
                    }
                }
                $customerQuote->save();
            }
            $this->setQuoteId($customerQuote->getId());
            if ($this->_quote) {
                $this->_quote->delete();
            }
            $this->_quote = $customerQuote;
        }
        return $this;
    }

    public function setStepData($step, $data, $value=null)
    {
        $steps = $this->getSteps();
        if (is_null($value)) {
            if (is_array($data)) {
                $steps[$step] = $data;
            }
        } else {
            if (!isset($steps[$step])) {
                $steps[$step] = array();
            }
            if (is_string($data)) {
                $steps[$step][$data] = $value;
            }
        }
        $this->setSteps($steps);

        return $this;
    }

    public function getStepData($step=null, $data=null)
    {
        $steps = $this->getSteps();
        if (is_null($step)) {
            return $steps;
        }
        if (!isset($steps[$step])) {
            return false;
        }
        if (is_null($data)) {
            return $steps[$step];
        }
        if (!is_string($data) || !isset($steps[$step][$data])) {
            return false;
        }
        return $steps[$step][$data];
    }

    public function clear()
    {
        Mage::dispatchEvent('checkout_quote_destroy', array('quote'=>$this->getQuote()));
        $this->_quote = null;
        $this->setQuoteId(null);
    }

    public function resetCheckout()
    {
        $this->setCheckoutState(self::CHECKOUT_STATE_BEGIN);
        return $this;
    }
}
