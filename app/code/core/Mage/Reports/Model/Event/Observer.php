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
 * Report event observer model
 *
 * @category   Mage
 * @package    Mage_Reports
 */

class Mage_Reports_Model_Event_Observer
{
    protected function _event($eventTypeId, $objectId, $subjectId = null)
    {
        if (is_null($subjectId)) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            if ($customer->getId()) {
                $subjectId = $customer->getId();
            }
        }

        $eventModel = Mage::getModel('reports/event');
        $storeId    = Mage::app()->getStore()->getId();
        $eventModel
            ->setEventTypeId($eventTypeId)
            ->setObjectId($objectId)
            ->setSubjectId($subjectId)
            ->setStoreId($storeId);
        $eventModel->save();

        return $this;
    }

    public function catalogProductView(Varien_Event_Observer $observer)
    {
        return $this->_event(1, $observer->getEvent()->getProduct()->getId());
    }

    public function sendfriendProduct(Varien_Event_Observer $observer)
    {
        return $this->_event(2, $observer->getEvent()->getProduct()->getId());
    }

    public function catalogProductCompareAddProduct(Varien_Event_Observer $observer)
    {
        return $this->_event(3, $observer->getEvent()->getProduct()->getId());
    }

    public function checkoutCartAddProduct(Varien_Event_Observer $observer)
    {
        return $this->_event(4, $observer->getEvent()->getProduct()->getId());
    }

    public function wishlistAddProduct(Varien_Event_Observer $observer)
    {
        return $this->_event(5, $observer->getEvent()->getProduct()->getId());
    }

    public function wishlistShare(Varien_Event_Observer $observer)
    {
        return $this->_event(6, $observer->getEvent()->getWishlist()->getId());
    }
}