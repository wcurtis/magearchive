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
 * @package    Mage_Reports
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Report Customers Review collection
 *
 * @category   Mage
 * @package    Mage_Reports
 */

class Mage_Reports_Model_Mysql4_Review_Customer_Collection extends Mage_Customer_Model_Entity_Customer_Collection
{
    protected function _construct()
    {
        parent::__construct();
    }

    protected function _joinFields()
    {
        $this->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname');

        $this->getSelect()
            ->join('review_detail', 'review_detail.customer_id = e.entity_id', array('review_cnt' => 'count(review_detail.review_id)'))
            ->group('e.entity_id');

    }

    public function resetSelect()
    {
        parent::resetSelect();
        $this->_joinFields();
        return $this;
    }

    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::GROUP);

        $sql = $countSelect->__toString();

        $sql = preg_replace('/^select\s+.+?\s+from\s+/is', 'select count(DISTINCT(e.entity_id)) from ', $sql);

        return $sql;
    }

    public function setOrder($attribute, $dir='desc')
    {

        if ($attribute == 'review_cnt') {
                $this->getSelect()->order($attribute . ' ' . $dir);
        } else {
                parent::setOrder($attribute, $dir);
        }

        return $this;
    }

}
