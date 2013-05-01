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
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Checkout_Model_Mysql4_Cart extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('sales/quote', 'entity_id');
    }

    public function fetchItemsSummaryQty($quoteId)
    {
        $entityType = Mage::getSingleton('eav/config')->getEntityType('quote_item');
        $qtyEntityTypeId = $entityType->getEntityTypeId();
        $attribute = Mage::getSingleton('eav/config')->getAttribute($qtyEntityTypeId, 'qty');
        $qtyAttributeId = $attribute->getAttributeId();
        $qtyAttributeTable = $this->getMainTable().'_'.$attribute->getBackendType();
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('qty'=>$qtyAttributeTable), 'sum(qty.value)')
            ->join(array('e'=>$this->getMainTable()), 'e.entity_id=qty.entity_id', array())
            ->where('e.parent_id=?', $quoteId)
            ->where('qty.entity_type_id=?', $qtyEntityTypeId)
            ->where('qty.attribute_id=?', $qtyAttributeId);
        $qty = $read->fetchOne($select);
        return $qty;
    }
}