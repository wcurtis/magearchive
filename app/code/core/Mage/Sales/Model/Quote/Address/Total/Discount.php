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


class Mage_Sales_Model_Quote_Address_Total_Discount
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $validator = Mage::getModel('salesrule/validator')
        	->setCouponCode($address->getQuote()->getCouponCode())
        	->setCustomerGroupId($address->getQuote()->getCustomerGroupId())
        	->setStoreId($address->getQuote()->getStoreId());

        $address->setDiscountAmount(0);
        $address->setFreeShipping(0);

        $appliedRuleIds = '';
        $totalDiscountAmount = 0;
        foreach ($address->getAllItems() as $item) {
        	$validator->process($item);
        	$totalDiscountAmount += $item->getDiscountAmount();
        	$appliedRuleIds = trim($appliedRuleIds.','.$item->getAppliedRuleIds(), ',');
        }

        $address->setCouponCode($validator->getConfirmedCouponCode());
        $address->setDiscountAmount($totalDiscountAmount);
        $address->setAppliedRuleIds($appliedRuleIds);

        $address->setGrandTotal($address->getGrandTotal() - $address->getDiscountAmount());

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getDiscountAmount();
        if ($amount!=0) {
            $title = Mage::helper('sales')->__('Discount');
            if ($address->getQuote()->getCouponCode()) {
                $title .= ' ('.$address->getQuote()->getCouponCode().')';
            }
            $address->addTotal(array(
                'code'=>$this->getCode(), 
                'title'=>$title, 
                'value'=>-$amount
            ));
        }
        return $this;
    }

}