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
 * @package    Mage_Tax
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax rule resource
 *
 * @category   Mage
 * @package    Mage_Tax
 */
class Mage_Tax_Model_Mysql4_Rule
{

    /**
     * resource tables
     */
    protected $_ruleTable;

    /**
     * resources
     */
    protected $_write;

    protected $_read;


    public function __construct()
    {
        $this->_ruleTable = Mage::getSingleton('core/resource')->getTableName('tax/tax_rule');

        $this->_read = Mage::getSingleton('core/resource')->getConnection('tax_read');
        $this->_write = Mage::getSingleton('core/resource')->getConnection('tax_write');
    }

    public function getIdFieldName()
    {
        return 'tax_rule_id';
    }

    public function load($model, $ruleId)
    {
        if( intval($ruleId) <= 0 ) {
            return;
        }
        $select = $this->_read->select();
        $select->from($this->_ruleTable);
        $select->where("{$this->_ruleTable}.tax_rule_id = ?", $ruleId);

        $ruleData = $this->_read->fetchRow($select);
        $model->setData($ruleData);
    }

    public function save($ruleObject)
    {
        $ruleArray = array(
            'tax_customer_class_id' => $ruleObject->getTaxCustomerClassId(),
            'tax_product_class_id' => $ruleObject->getTaxProductClassId(),
            'tax_rate_type_id' => $ruleObject->getTaxRateTypeId()
        );

        if( $ruleObject->getTaxRuleId() > 0 ) {
            $condition = $this->_write->quoteInto("{$this->_ruleTable}.tax_rule_id = ?", $ruleObject->getTaxRuleId());
            $this->_write->update($this->_ruleTable, $ruleArray, $condition);
        } else {
            $this->_write->insert($this->_ruleTable, $ruleArray);
        }

    }

    public function delete($ruleObject)
    {
        $condition = $this->_write->quoteInto("{$this->_ruleTable}.tax_rule_id=?", $ruleObject->getTaxRuleId());
        $this->_write->delete($this->_ruleTable, $condition);
    }
}