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
 * Report Products Review collection
 *
 * @category   Mage
 * @package    Mage_Reports
 */

class Mage_Reports_Model_Mysql4_Review_Product_Collection extends Mage_Catalog_Model_Entity_Product_Collection
{
    protected $reviewTable;

    protected function _construct()
    {
        parent::__construct();
    }

    protected function _joinFields()
    {
        $this->addAttributeToSelect("name");

        $reviewTable = Mage::getSingleton('core/resource')->getTableName('review/review');

        $this->getSelect()
            ->join(array('rt' => $reviewTable), 'e.entity_id=rt.entity_pk_value', array('review_cnt' => 'count(rt.entity_id)', 'last_created' => 'max(rt.created_at)', 'avg_rating' => 'entity_id'))
            ->group('rt.entity_pk_value');
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

        $sql = preg_replace('/^select\s+.+?\s+from\s+/is', 'select count(DISTINCT(entity_pk_value)) from ', $sql);

        return $sql;
    }

    public function setOrder($attribute, $dir='desc')
    {
        $fields = array(
            'avg_rating',
            'review_cnt',
            'last_created'
        );

        if (in_array($attribute, $fields)) {
                $this->getSelect()->order($attribute . ' ' . $dir);
        } else {
                parent::setOrder($attribute, $dir);
        }

        return $this;
    }

}
