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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Quotes collection
 *
 * @category   Mage
 * @package    Mage_Sales
 */

class Mage_Sales_Model_Entity_Quote_Collection extends Mage_Eav_Model_Entity_Collection_Abstract
{
    public function __construct()
    {
        $this->setEntity(Mage::getResourceSingleton('sales/quote'));
        $this->setObject('sales/quote');
    }

    public function loadByCustomerId($customerId)
    {
        $this->addAttributeToSelect('entity_id')
            ->addAttributeToFilter('customer_id', $customerId)
            ->addAttributeToFilter('is_active', 1)
            ->setOrder('updated_at', 'desc')
            ->setPage(1,1)
            ->load();

        if (!$this->count()) {
            return false;
        }
        foreach ($this as $quote) {
            return $quote;
        }
    }

    /**
     * Enter description here...
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Sales_Model_Quote|false
     */
    public function loadByCustomer(Mage_Customer_Model_Customer $customer)
    {
        $this->addAttributeToSelect('entity_id')
            ->addAttributeToFilter('customer_id', $customerId)
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('store_id', array('in', $customer->getSharedStoreIds()))
            ->setOrder('updated_at', 'desc')
            ->setPage(1,1)
            ->load();

        if (!$this->count()) {
            return false;
        }
        foreach ($this as $quote) {
            return $quote;
        }

    }

}