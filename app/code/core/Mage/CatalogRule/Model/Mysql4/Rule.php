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


class Mage_CatalogRule_Model_Mysql4_Rule extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('catalogrule/rule', 'rule_id');
    }

    public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $object->setFromDate($this->formatDate($object->getFromDate()));
        $object->setToDate($this->formatDate($object->getToDate()));
        parent::_beforeSave($object);
    }

    public function updateRuleProductData(Mage_CatalogRule_Model_Rule $rule)
    {
        foreach ($rule->getActions()->getActions() as $action) {
            break;
        }

        $ruleId = $rule->getId();

        $write = $this->_getWriteAdapter();
        $write->delete($this->getTable('catalogrule/rule_product'), $write->quoteInto('rule_id=?', $ruleId));

        if (empty($action) || !$rule->getIsActive()) {
            return $this;
        }

        $productIds = $rule->getMatchingProductIds();
        $storeIds = explode(',', $rule->getStoreIds());
        $customerGroupIds = explode(',', $rule->getCustomerGroupIds());

        $fromTime = strtotime($rule->getFromDate());
        $toTime = strtotime($rule->getToDate())+86400;
        $sortOrder = (int)$rule->getSortOrder();
        $actionOperator = $action->getOperator();
        $actionAmount = $action->getValue();
        $actionStop = $rule->getStopRulesProcessing();

        $rows = array();
        $header = 'replace into '.$this->getTable('catalogrule/rule_product').' (rule_id, from_time, to_time, store_id, customer_group_id, product_id, action_operator, action_amount, action_stop, sort_order) values ';
        try {
            $write->beginTransaction();

            foreach ($productIds as $productId) {
                foreach ($storeIds as $storeId) {
                    foreach ($customerGroupIds as $customerGroupId) {
                        $rows[] = "('$ruleId', '$fromTime', '$toTime', '$storeId', '$customerGroupId', '$productId', '$actionOperator', '$actionAmount', '$actionStop', '$sortOrder')";
                        if (sizeof($rows)==100) {
                            $sql = $header.join(',', $rows);
                            $write->query($sql);
                            $rows = array();
                        }
                    }
                }
            }
            if (!empty($rows)) {
                $sql = $header.join(',', $rows);
                $write->query($sql);
            }

            $write->commit();
        } catch (Exception $e) {

            $write->rollback();
            throw $e;

        }

        return $this;
    }

    public function removeCatalogPricesForDateRange($fromDate, $toDate)
    {
        $write = $this->_getWriteAdapter();
        $cond = $write->quoteInto('rule_date between ?', $this->formatDate($fromDate));
        $cond = $write->quoteInto($cond.' and ?', $this->formatDate($toDate));
        $write->delete($this->getTable('catalogrule/rule_product_price'), $cond);
        return $this;
    }

    public function getRuleProductsForDateRange($fromDate, $toDate)
    {
        $read = $this->_getReadAdapter();
        if (is_null($toDate)) {
            $toDate = $fromDate;
        }
        $sql = "select * from ".$this->getTable('catalogrule/rule_product')." where
            (".$read->quoteInto('from_time=0 or from_time<=?', strtotime($toDate))
            ." or ".$read->quoteInto('to_time=0 or to_time>=?', strtotime($fromDate)).")
            order by to_time, from_time, store_id, customer_group_id, product_id, sort_order";
        return $read->fetchAll($sql);
    }

    public function applyAllRulesForDateRange($fromDate, $toDate=null)
    {
        if (is_null($toDate)) {
            $toDate = $fromDate;
        }

        $this->removeCatalogPricesForDateRange($fromDate, $toDate);

        $ruleProducts = $this->getRuleProductsForDateRange($fromDate, $toDate);
        if (empty($ruleProducts)) {
            return $this;
        }

        $productIds = array();
        foreach ($ruleProducts as $r) {
            $productIds[$r['product_id']] = $r['product_id'];
        }

        $products = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('price')
            ->addAttributeToFilter('entity_id', array('in'=>$productIds))
            ->load();

        $prices = array();
        $stop = array();
        $fromTime = strtotime($fromDate);
        $toTime = strtotime($toDate);
        for ($time=$fromTime; $time<=$toTime; $time+=86400) {
            $date = $this->formatDate($time);
            foreach ($ruleProducts as $r) {
                if (!(($r['from_time']==0 || $r['from_time']<=$time) && ($r['to_time']==0 || $r['to_time']>=$time))) {
                    continue;
                }

                $key = $this->formatDate($time).'|'.$r['store_id'].'|'.$r['customer_group_id'].'|'.$r['product_id'];

                if (!isset($prices[$key])) {
                    $product = $products->getItemById($r['product_id']);
                    if ($product) {
                        $prices[$key] = $product->getPrice();
                    } else {
                        $prices[$key] = false;
                    }
                } elseif ($prices[$key]===false) {
                    continue;
                }

                if (!empty($stop[$key])) {
                    continue;
                }

                $amount = $r['action_amount'];
                switch ($r['action_operator']) {
                    case 'to_fixed':
                        $prices[$key] = $amount;
                        break;

                    case 'to_percent':
                        $prices[$key] = $prices[$key]*$amount/100;
                        break;

                    case 'by_fixed':
                        $prices[$key] -= $amount;
                        break;

                    case 'by_percent':
                        $prices[$key] = $prices[$key]*(1-$amount/100);
                        break;
                }

                if ($r['action_stop']) {
                    $stop[$key] = true;
                }
            }
        }

        $write = $this->_getWriteAdapter();
        $header = 'replace into '.$this->getTable('catalogrule/rule_product_price').' (rule_date, store_id, customer_group_id, product_id, rule_price) values ';

        try {
            $write->beginTransaction();

            foreach ($prices as $key=>$value) {
                $k = explode('|', $key);
                $rows[] = "('{$k[0]}', {$k[1]}, {$k[2]}, {$k[3]}, {$value})";
                if (sizeof($rows)==100) {
                    $sql = $header.join(',', $rows);
                    $write->query($sql);
                    $rows = array();
                }
            }
            if (!empty($rows)) {
                $sql = $header.join(',', $rows);
                $write->query($sql);
            }

            $write->commit();

        } catch (Exception $e) {

            $write->rollback();
            throw $e;

        }

        return $this;
    }

    public function getRulePrice($date, $sId, $gId, $pId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getTable('catalogrule/rule_product_price'), 'rule_price')
            ->where('rule_date=?', $this->formatDate($date))
            ->where('store_id=?', $sId)
            ->where('customer_group_id=?', $gId)
            ->where('product_id=?', $pId);
        return $read->fetchOne($select);
    }

    public function getRulesForProduct($date, $sId, $pId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getTable('catalogrule/rule_product_price'), '*')
            ->where('rule_date=?', $this->formatDate($date))
            ->where('store_id=?', $sId)
            ->where('product_id=?', $pId);
        return $read->fetchAll($select);
    }
}