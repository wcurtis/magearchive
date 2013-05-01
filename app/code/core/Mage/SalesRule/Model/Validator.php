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
 * @package    Mage_SalesRule
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_SalesRule_Model_Validator extends Mage_Core_Model_Abstract
{
	protected function _construct()
	{
        parent::_construct();
		$this->_init('salesrule/validator');
		$this->setIsCouponCodeConfirmed(false);
	}

	public function getConfirmedCouponCode()
	{
		if ($this->getIsCouponCodeConfirmed()) {
			return $this->getCouponCode();
		}
		return false;
	}

	public function process(Mage_Core_Model_Abstract $item) {
		if (!$item instanceof Mage_Sales_Model_Quote_Item
			&& !$item instanceof Mage_Sales_Model_Quote_Address_Item) {
			throw Mage::exception('Mage_SalesRule', Mage::helper('salesrule')->__('Invalid item entity'));
		}

		$item->setFreeShipping(false);
		$item->setDiscountAmount(0);
		$item->setDiscountPercent(0);
		
		if ($item instanceof Mage_Sales_Model_Quote_Item) {
			$quote = $item->getQuote();
		} elseif ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
			$quote = $item->getAddress()->getQuote();
		}
		
		$rule = Mage::getModel('salesrule/rule');
		
		$appliedRuleIds = array();

		$actions = $this->getActionsCollection($item);
		foreach ($actions as $action) {
			if (!$rule->load($action->getRuleId())->validate($quote)) {
				continue;
			}
			
			$qty = $rule->getDiscountQty() ? min($item->getQty(), $rule->getDiscountQty()) : $item->getQty();
			
			switch ($rule->getSimpleAction()) {
				case 'by_percent':
					$discountAmount = $qty*$item->getPrice()*$rule->getDiscountAmount()/100;
					if (!$rule->getDiscountQty()) {
						$discountPercent = min(100, $item->getDiscountPercent()+$rule->getDiscountAmount());
						$item->setDiscountPercent($discountPercent);
					}
					break;

				case 'by_fixed':
					$discountAmount = $qty*$rule->getDiscountAmount();
					break;
			}
			
			$discountAmount = min($discountAmount, $item->getRowTotal());
			$item->setDiscountAmount($item->getDiscountAmount()+$discountAmount);
			
			switch ($rule->getSimpleFreeShipping()) {
				case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM:
					$item->setFreeShipping(true);
					break;
					
				case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS:
					$address = $item->getAddress();
					if (!$address) {
						$address = $item->getQuote()->getShippingAddress();
					}
					if ($address) {
						$address->setFreeShipping(true);
					}
					break;
			}
			
			$appliedRuleIds[$rule->getRuleId()] = true;
			
			if ($rule->getStopRulesProcessing()) {
				break;
			}
		}
		
		$item->setAppliedRuleIds(join(',',$appliedRuleIds));
		
		return $this;
	}

	public function getActionsCollection($item)
	{
		$actions = Mage::getResourceModel('salesrule/rule_product_collection')
			->addFieldToFilter('coupon_code', array(array('null'=>true), $this->getCouponCode()))
			->addFieldToFilter('from_time', array(0, array('lteq'=>time())))
			->addFieldToFilter('to_time', array(0, array('gteq'=>time())))
			->addFieldToFilter('customer_group_id', $this->getCustomerGroupId())
			->addFieldToFilter('store_id', $this->getStoreId())
			->addFieldToFilter('product_id', $item->getProductId())
			->setOrder('sort_order');
#print_r($actions->getSelect()->__toString());
		$actions
			->load();
		return $actions;
	}
}
