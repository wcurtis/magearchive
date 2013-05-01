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
 * @package    Mage_Review
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review model
 *
 * @category   Mage
 * @package    Mage_Review
 */
class Mage_Review_Model_Review extends Varien_Object
{
    public function getResource()
    {
        return Mage::getResourceSingleton('review/review');
    }

    public function getId()
    {
        return $this->getReviewId();
    }

    public function setId($reviewId)
    {
        $this->setReviewId($reviewId);
        return $this;
    }

    public function load($reviewId)
    {
        $this->setData($this->getResource()->load($reviewId));
        return $this;
    }

    public function save()
    {
        $this->getResource()->save($this);
        return $this;
    }

    public function delete()
    {
        $this->getResource()->delete($this);
        return $this;
    }

    public function getCollection()
    {
        return Mage::getResourceModel('review/review_collection');
    }

    public function getProductCollection()
    {
        return Mage::getResourceModel('review/review_product_collection');
    }

    public function getStatusCollection()
    {
        return Mage::getResourceModel('review/review_status_collection');
    }

    public function getTotalReviews($entityPkValue, $approvedOnly=false, $storeId=0)
    {
        return $this->getResource()->getTotalReviews($entityPkValue, $approvedOnly, $storeId);
    }

    public function aggregate()
    {
        $this->getResource()->aggregate($this);
        return $this;
    }

    public function getEntitySummary($product, $storeId=0)
    {
        $summaryData = Mage::getModel('review/review_summary')
            ->setStoreId($storeId)
            ->load($product->getId());
        $summary = new Varien_Object();
        $summary->setData($summaryData->getData());
        $product->setRatingSummary($summary);
    }

    public function appendSummary($collection)
    {
        $entityIds = array();
        foreach( $collection->getItems() as $_item ) {
            $entityIds[] = $_item->getId();
        }

        if( sizeof($entityIds) == 0 ) {
            return;
        }

        $summaryData = Mage::getResourceModel('review/review_summary_collection')
            ->addEntityFilter($entityIds)
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->load();

        foreach( $collection->getItems() as $_item ) {
            foreach( $summaryData as $_summary ) {
                if( $_summary->getEntityPkValue() == $_item->getId() ) {
                    $_item->setRatingSummary($_summary);
                }
            }
        }
    }

    public function getPendingStatus()
    {
        return 2;
    }

    public function getReviewUrl()
    {
        return Mage::getUrl('review/product/view', array('id' => $this->getReviewId()));
    }
}