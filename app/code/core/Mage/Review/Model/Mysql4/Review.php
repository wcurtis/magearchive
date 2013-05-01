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
 * Review Mysql4 resource model
 *
 * @category   Mage
 * @package    Mage_Review
 */
class Mage_Review_Model_Mysql4_Review
{
    protected $_reviewTable;
    protected $_reviewDetailTable;
    protected $_reviewStatusTable;
    protected $_reviewEntityTable;
    protected $_reviewStoreTable;

    /**
     * Read connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_read;

    /**
     * Write connection
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_write;

    public function __construct()
    {
        $resources = Mage::getSingleton('core/resource');

        $this->_reviewTable         = $resources->getTableName('review/review');
        $this->_reviewDetailTable   = $resources->getTableName('review/review_detail');
        $this->_reviewStatusTable   = $resources->getTableName('review/review_status');
        $this->_reviewEntityTable   = $resources->getTableName('review/review_entity');
        $this->_reviewStoreTable   = $resources->getTableName('review/review_store');
        $this->_aggregateTable      = $resources->getTableName('review/review_aggregate');

        $this->_read    = $resources->getConnection('review_read');
        $this->_write   = $resources->getConnection('review_write');
    }

    public function load($reviewId)
    {
        $select = $this->_read->select();
        $select->from($this->_reviewTable)
            ->join($this->_reviewDetailTable, "{$this->_reviewTable}.review_id = {$this->_reviewDetailTable}.review_id")
            ->where("{$this->_reviewTable}.review_id = ?", $reviewId);
        $data = $this->_read->fetchRow($select);

        $data['stores'] = array();

        $storesSelect = $this->_read->select();

        $storesSelect
            ->from($this->_reviewStoreTable)
            ->where('review_id=?', $reviewId);

        $stores = $this->_read->fetchAll($storesSelect);

        foreach ($stores as $store) {
            $data['stores'][] = $store['store_id'];
        }

        return $data;
    }

    public function save(Mage_Review_Model_Review $review)
    {
        $this->_write->beginTransaction();
        try {
            if ($review->getId()) {
                $data = $this->_prepareUpdateData($review);
                $condition = $this->_write->quoteInto('review_id = ?', $review->getId());

                $this->_write->update($this->_reviewTable, $data['base'], $condition);
                $this->_write->update($this->_reviewDetailTable, $data['detail'], $condition);
            }
            else {
                $data = $this->_prepareInsertData($review);

                $data['base']['created_at'] = now();
                $this->_write->insert($this->_reviewTable, $data['base']);

                $review->setId($this->_write->lastInsertId());
                $data['detail']['review_id'] = $review->getId();
                $this->_write->insert($this->_reviewDetailTable, $data['detail']);
            }

            if(isset($data['stores'])) {
                $condition = $this->_write->quoteInto('review_id = ?', $review->getId());
                $this->_write->delete($this->_reviewStoreTable, $condition);
		$insertedStoreIds = array();
                foreach ($data['stores'] as $storeId) {
                    if (in_array($storeId, $insertedStoreIds))
		        continue;

		    $insertedStoreIds[] = $storeId;
                    $storeInsert = array();
                    $storeInsert['store_id'] = $storeId;
                    $storeInsert['review_id'] = $review->getId();
                    $this->_write->insert($this->_reviewStoreTable, $storeInsert);
                }
            }
            $this->_write->commit();
        }
        catch (Exception $e){
            $this->_write->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Prepare data for review insert
     *
     * @todo    validate data
     * @param   Mage_Review_Model_Review $review
     * @return  array
     */
    protected function _prepareInsertData(Mage_Review_Model_Review $review)
    {
        $data = array(
            'base'  => array(
                'entity_id'         => $review->getEntityId(),
                'entity_pk_value'   => $review->getEntityPkValue(),
                'status_id'         => $review->getStatusId()
            ),
            'detail'=> array(
                'title'     => strip_tags($review->getTitle()),
                'detail'    => strip_tags($review->getDetail()),
                'store_id'=> $review->getStoreId(),
                'customer_id' => $review->getCustomerId(),
                'nickname'  => strip_tags($review->getNickname())
            )
        );

        if ($review->hasData('stores') && is_array($review->getStores())) {
            $stores = $review->getStores();
            $stores[] = 0;
            $data['stores'] = $stores;
        } elseif ($review->hasData('stores')) {
            $data['stores'] = array();
        }

        return $data;
    }

    public function _prepareUpdateData(Mage_Review_Model_Review $review)
    {
        $data = array(
            'detail'=> array(
                'title'     => strip_tags($review->getTitle()),
                'detail'    => strip_tags($review->getDetail()),
                'nickname'  => strip_tags($review->getNickname())
            ),
            'base' => array(
                'status_id' => $review->getStatusId()
            )
        );

        if ($review->hasData('stores') && is_array($review->getStores())) {
            $stores = $review->getStores();
            $stores[] = 0;
            $data['stores'] = $stores;
        } elseif ($review->hasData('stores')) {
            $data['stores'] = array();
        }

        return $data;
    }

    public function delete(Mage_Review_Model_Review $review)
    {
        if( $review->getId() ) {
            try {
                $this->_write->beginTransaction();
                $condition = $this->_write->quoteInto('review_id = ?', $review->getId());
                $review->load($review->getId());
                $this->_write->delete($this->_reviewTable, $condition);
                $this->_write->commit();
                $this->aggregate($review);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    public function getTotalReviews($entityPkValue, $approvedOnly=false, $storeId=0)
    {
        $read = clone $this->_read;
        $select = $read->select();
        $select->from($this->_reviewTable, "COUNT(*)")
            ->where("{$this->_reviewTable}.entity_pk_value = ?", $entityPkValue);

        if($storeId > 0) {
            $select->join(array('store'=>$this->_reviewStoreTable), $this->_reviewTable.'.review_id=store.review_id AND store.store_id=' . (int)$storeId, array());
        }

        if( $approvedOnly ) {
            $select->where("{$this->_reviewTable}.status_id = ?", 1);
        }
        return $read->fetchOne($select);
    }

    public function aggregate($object)
    {
        if( !$object->getEntityPkValue() && $object->getId() ) {
            $object->load($object->getReviewId());
        }

        $ratingModel = Mage::getModel('rating/rating');
        $ratingSummaries = $ratingModel->getEntitySummary($object->getEntityPkValue(), false);

        $nonDelete = array();
        foreach($ratingSummaries as $ratingSummaryObject) {
            if( $ratingSummaryObject->getCount() ) {
                $ratingSummary = round($ratingSummaryObject->getSum() / $ratingSummaryObject->getCount());
            } else {
                $ratingSummary = $ratingSummaryObject->getSum();
            }

            $reviewsCount = $this->getTotalReviews($object->getEntityPkValue(), true, $ratingSummaryObject->getStoreId());
            $select = $this->_read->select();
            $select->from($this->_aggregateTable)
                ->where("{$this->_aggregateTable}.entity_pk_value = ?", $object->getEntityPkValue())
                ->where("{$this->_aggregateTable}.entity_type = ?", $object->getEntityId())
                ->where("{$this->_aggregateTable}.store_id = ?", $ratingSummaryObject->getStoreId());

            $oldData = $this->_read->fetchRow($select);

            $data = new Varien_Object();

            $data->setReviewsCount($reviewsCount)
                ->setEntityPkValue($object->getEntityPkValue())
                ->setEntityType($object->getEntityId())
                ->setRatingSummary( ($ratingSummary > 0) ? $ratingSummary : 0 )
                ->setStoreId($ratingSummaryObject->getStoreId());

            $this->_write->beginTransaction();
            try {
                if( $oldData['primary_id'] > 0 ) {
                    $condition = $this->_write->quoteInto("{$this->_aggregateTable}.primary_id = ?", $oldData['primary_id']);
                    $this->_write->update($this->_aggregateTable, $data->getData(), $condition);
                } else {
                    $this->_write->insert($this->_aggregateTable, $data->getData());
                }
                $this->_write->commit();
            } catch (Exception $e) {
                $this->_write->rollBack();
            }
        }

    }
}