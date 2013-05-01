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
 * @package    Mage_Customer
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Customer_Model_Payment extends Varien_Object
{
    public function setData($var, $value='', $isChanged=true)
    {
        if (is_array($var)) {
            if (isset($var['cc_number'])) {
                $var['cc_last4'] = substr($var['cc_number'],-4);
                if (!empty($var['cc_number'])) {
                    $var['cc_number_enc'] = $this->encrypt($var['cc_number']);
                } else {
                    $var['cc_number_enc'] = '';
                }
            }
            if (isset($var['cc_cid'])) {
                if (!empty($var['cc_cid'])) {
                    $var['cc_cid_enc'] = $this->encrypt($var['cc_cid']);
                } else {
                    $var['cc_cid_enc'] = '';
                }
            }
        }
        else {
            if ('cc_number'===$var) {
                $this->setCcLast4(substr($value,-4));
                if (!empty($value)) {
                    $this->setCcNumberEnc($this->encrypt($value));
                } else {
                    $this->setCcNumberEnc('');
                }
            }
            if ('cc_cid'===$var) {
                if (!empty($value)) {
                    $this->setCcCidEnc($this->encrypt($value));
                } else {
                    $this->setCcCidEnc('');
                }
            }
        }
        return parent::setData($var, $value, $isChanged);
    }

    public function getData($key='', $index=false)
    {
        if ('cc_number'===$key) {
            if (empty($this->_data['cc_number']) && !empty($this->_data['cc_number_enc'])) {
                $this->_data['cc_number'] = $this->decrypt($this->getCcNumberEnc());
            }
        }
        if ('cc_cid'===$key) {
            if (empty($this->_data['cc_cid']) && !empty($this->_data['cc_cid_enc'])) {
                $this->_data['cc_cid'] = $this->decrypt($this->getCcCidEnc());
            }
        }
        return parent::getData($key, $index);
    }

    public function encrypt($data)
    {
        $key = (string)Mage::getConfig()->getNode('global/crypt/key');
        return base64_encode(Varien_Crypt::factory()->init($key)->encrypt($data));
    }

    /**
     * Customer credit card decryption
     *
     * @todo find out why it appends extra symbols if not using trim()
     * @param string $data
     * @return string
     */
    public function decrypt($data)
    {
        $key = (string)Mage::getConfig()->getNode('global/crypt/key');
        return trim(Varien_Crypt::factory()->init($key)->decrypt(base64_decode($data)));
    }

    public function getOrderStatus()
    {
        if (!$this->getMethod()) {
            return false;
        }
        $status = (string)Mage::getConfig()->getNode('global/sales/payment/methods/'.$this->getMethod().'/orderStatus');
        return $status;
    }
}