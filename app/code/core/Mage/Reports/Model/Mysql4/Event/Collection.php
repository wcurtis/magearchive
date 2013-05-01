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
 * Report event collection
 *
 * @category   Mage
 * @package    Mage_Reports
 */

class Mage_Reports_Model_Mysql4_Event_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('reports/event');
    }

    public function addRecentlyFiler($typeId, $subjectId, $subtype = 0, $ignore = null, $limit = 15)
    {
        $stores = array();
        if (Mage::app()->getStore()->getId() == 0) {
            foreach (Mage::getSingleton('adminhtml/system_store')->getStoreCollection() as $store) {
                $stores[] = $store->getId();
            }
        } else{
            foreach (Mage::app()->getStore()->getGroup()->getStores() as $store) {
                $stores[] = $store->getId();
            }
        }
        $this->_select
            ->where('event_type_id=?', $typeId)
            ->where('subject_id=?', $subjectId)
            ->where('subtype=?', $subtype)
            ->where('store_id IN(?)', $stores);
        if ($ignore) {
            if (is_array($ignore)) {
                $this->_select->where('object_id NOT IN(?)', $ignore);
            }
            else {
                $this->_select->where('object_id<>?', $ignore);
            }
        }
        $this->_select->group('object_id')
            ->limit(0, $limit);
        return $this;
    }
}