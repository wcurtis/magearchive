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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml newsletter subscribers grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Newsletter_Subscriber_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
	 * Constructor
	 *
	 * Set main configuration of grid
	 */
	public function __construct()
    {
        parent::__construct();

        $this->setId('subscriberGrid');
        $this->setDefaultSort('id', 'desc');
        $this->setSaveParametersInSession(true);
        $this->setMessageBlockVisibility(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceSingleton('newsletter/subscriber_collection')
			->showCustomerInfo(true)
			->showStoreInfo();

		if($this->getRequest()->getParam('queue', false)) {
			$collection->useQueue(Mage::getModel('newsletter/queue')
				->load($this->getRequest()->getParam('queue')));
		}

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getShowQueueAdd()
    {
    	return $this->getCollection()->getSize() > 0;
    }



    protected function _prepareColumns()
    {
    	/*if($this->getShowQueueAdd()) {*/
    	$this->addColumn('checkbox', array(
    		'align'		=> 'center',
    		'sortable' 	=> false,
    		'filter'	=> 'adminhtml/newsletter_subscriber_grid_filter_checkbox',
    		'renderer'	=> 'adminhtml/newsletter_subscriber_grid_renderer_checkbox',
    		'width'		=> '20px'
    	));
    	/*}*/

    	$this->addColumn('id', array(
    		'header'	=> Mage::helper('newsletter')->__('ID'),
	   		'index'		=> 'subscriber_id'
    	));

    	$this->addColumn('email', array(
    		'header'	=> Mage::helper('newsletter')->__('Email'),
    		'index'		=> 'subscriber_email'
    	));

    	$this->addColumn('name', array(
    		'header'	=> Mage::helper('newsletter')->__('Name'),
    		'index'		=> 'customer_name',
    		'sortable' 	=> false,
    		'filter'	=> false,
    		'default'	=>	'----'
    	));

        $this->addColumn('status', array(
    		'header'	=> Mage::helper('newsletter')->__('Status'),
    		'index'		=> 'subscriber_status',
    		'type'      => 'options',
    		'options'   => array(
        		Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE   => Mage::helper('newsletter')->__('Not activated'),
        		Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED   => Mage::helper('newsletter')->__('Subcribed'),
        		Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED => Mage::helper('newsletter')->__('Unsubcribed'),
    		),
    	));

    	$this->addColumn('website', array(
    		'sortable' 	=> false,
    		'header'	=> Mage::helper('newsletter')->__('Website'),
    		'filter'	=> 'adminhtml/newsletter_subscriber_grid_filter_website',
    		'renderer'	=> 'adminhtml/newsletter_subscriber_grid_renderer_website',
    		'index'		=> 'store_id'
    	));



    	return parent::_prepareColumns();
    }

}// Class Mage_Adminhtml_Block_Newsletter_Subscriber_Grid END
