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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_CatalogSearch_Model_Mysql4_Advanced_Collection extends Mage_Catalog_Model_Entity_Product_Collection
{
    public function addFieldsToFilter($fields)
    {
        if ($fields) {
            $selects = array();
            foreach ($fields as $table=>$conditions) {
                $select = $this->_read->select();
                $select->from($table, 'entity_id');

                foreach ($conditions as $attributeId=>$conditionValue) {
                    $field = 'value';
                    $storeId = $this->getEntity()->getStoreId();

                    if (is_numeric($attributeId)) {
                        $select->where("{$table}.attribute_id = ?", $attributeId);
                    } else {
                        $field = $attributeId;
                        $storeId = 0;
                    }
                    $select->where('store_id = ?', $storeId);

                    if (is_array($conditionValue)){
                        if (isset($conditionValue['in'])){
                            $condition = $conditionValue['in'];
                            $suffix = 'in (?)';
                        } else if (isset($conditionValue['like'])) {
                            $condition = $conditionValue['like'];
                            $suffix = 'like ?';
                        } else if (isset($conditionValue['from']) && isset($conditionValue['to'])) {
                            $suffix = '?';
                            if ($conditionValue['from']) {
                                if (!is_numeric($conditionValue['from'])){
                                    $conditionValue['from'] = date("Y-m-d H:i:s", strtotime($conditionValue['from']));
                                }

                                $select->where("{$table}.value >= ?", $conditionValue['from']);
                            }

                            if ($conditionValue['to']) {
                                if (!is_numeric($conditionValue['to'])){
                                    $conditionValue['to'] = date("Y-m-d H:i:s", strtotime($conditionValue['to']));
                                }

                                $select->where("{$table}.value <= ?", $conditionValue['to']);
                            }
                            continue;
                        }
                    } else {
                        $condition = $conditionValue;
                        $suffix = '= ?';
                    }

                    $select->where("{$table}.{$field} {$suffix}", $condition);
                }
                $selects[] = $select;
            }

            $this->addFieldToFilter('entity_id', array('in' => new Zend_Db_Expr(implode(" UNION ", $selects))));
        }

        return $this;
    }
}