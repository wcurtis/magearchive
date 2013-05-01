<?php

class Mage_GoogleCheckout_Model_Mysql4_Tax extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('tax/tax_rule', 'rule_id');
    }

    public function fetchRuleRatesForCustomerTaxClass($customerTaxClass)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('rule'=>$this->getTable('tax/tax_rule')))
            ->join(array('rd'=>$this->getTable('tax/tax_rate_data')), "rd.rate_type_id=rule.tax_rate_type_id", array('value'=>new Zend_Db_Expr('rate_value/100')))
            ->join(array('r'=>$this->getTable('tax/tax_rate')), "r.tax_rate_id=rd.tax_rate_id", array('country'=>'tax_country_id', 'postcode'=>'tax_postcode'))
            ->joinLeft(array('reg'=>$this->getTable('directory/country_region')), "reg.region_id=r.tax_region_id", array('state'=>'code'))
            ->where('rule.tax_customer_class_id=?', $customerTaxClass);
        $rows = $read->fetchAll($select);

        return $rows;
    }
}