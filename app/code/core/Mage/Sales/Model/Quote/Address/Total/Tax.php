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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Sales_Model_Quote_Address_Total_Tax extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $address->setTaxAmount(0);
        $tax = Mage::getModel('tax/rate_data')
        	->setRegionId($address->getRegionId())
        	->setPostcode($address->getPostcode())
        	->setCustomerClassId($address->getQuote()->getCustomerTaxClassId());
        /* @var $tax Mage_Tax_Model_Rate_Data */
        foreach ($address->getAllItems() as $item) {
        	$tax->setProductClassId($item->getProduct()->getTaxClassId());
        	$rate = $tax->getRate();
            $item->setTaxPercent($rate);
            $item->calcTaxAmount();
            $address->setTaxAmount($address->getTaxAmount() + $item->getTaxAmount());
        }

        $address->setGrandTotal($address->getGrandTotal() + $address->getTaxAmount());
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getTaxAmount();
        if ($amount!=0) {
            $address->addTotal(array(
                'code'=>$this->getCode(),
                'title'=>Mage::helper('sales')->__('Tax'),
                'value'=>$amount
            ));
        }
        return $this;
    }
}