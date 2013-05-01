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
 * @package    Mage_CatalogRule
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_CatalogRule_Model_Observer
{
    protected $_rulePrices = array();

    /**
     * Processing final price on frontend
     */
    public function processFrontFinalPrice($observer)
    {
        if ($observer->hasDate()) {
            $date = $observer->getDate();
        } else {
            $date = mktime(0,0,0);
        }

        if ($observer->hasStoreId()) {
            $sId = $observer->getStoreId();
        } else {
            $sId = Mage::app()->getStore()->getId();
        }

        if ($observer->hasCustomerGroupId()) {
            $gId = $observer->getCustomerGroupId();
        } else {
            $custSession = Mage::getSingleton('customer/session');
            $gId = $custSession->isLoggedIn() ? $custSession->getCustomer()->getGroupId() : 0;
        }

        $product = $observer->getEvent()->getProduct();
        $pId = $product->getId();

        $key = "$date|$sId|$gId|$pId";
        if (!isset($this->_rulePrices[$key])) {
            $rulePrice = Mage::getResourceModel('catalogrule/rule')
                ->getRulePrice($date, $sId, $gId, $pId);
            $this->_rulePrices[$key] = $rulePrice;
        }
        if ($this->_rulePrices[$key]!==false) {
            $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
            $product->setFinalPrice($finalPrice);
        }
        return $this;
    }

    /**
     * Processing final price in admin
     */
    public function processAdminFinalPrice($observer)
    {
        if ($ruleData = Mage::registry('rule_data')) {
            $product = $observer->getEvent()->getProduct();

            $date = mktime(0,0,0);
            $sId = $ruleData->getStoreId();
            $gId = $ruleData->getCustomerGroupId();
            $pId = $product->getId();

            $key = "$date|$sId|$gId|$pId";
            if (!isset($this->_rulePrices[$key])) {
                $rulePrice = Mage::getResourceModel('catalogrule/rule')
                    ->getRulePrice($date, $sId, $gId, $pId);
                $this->_rulePrices[$key] = $rulePrice;
            }
            if ($this->_rulePrices[$key]!==false) {
                $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
                $product->setFinalPrice($finalPrice);
            }
        }
        return $this;
    }

    public function dailyCatalogUpdate($schedule)
    {
        $resource = Mage::getResourceSingleton('catalogrule/rule');
        $resource->applyAllRulesForDateRange(
            $resource->formatDate(mktime(0,0,0)),
            $resource->formatDate(mktime(0,0,0,date('m'),date('d')+1))
        );
    }
}