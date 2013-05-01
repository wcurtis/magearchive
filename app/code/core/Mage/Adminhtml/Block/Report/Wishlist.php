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
 * Adminhtml wishlist report page content block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Report_Wishlist extends Mage_Core_Block_Template
{
    public $wishlists_count;
    public $items_bought;
    public $shared_count;
    public $referrals_count;
    public $conversions_count;
    public $customer_with_wishlist;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('report/wishlist.phtml');
    }

    public function _beforeToHtml()
    {      
        $this->setChild('grid', $this->getLayout()->createBlock('adminhtml/report_wishlist_grid', 'report.grid'));

        $collection = Mage::getResourceModel('reports/wishlist_collection');
                      
        list($this->customer_with_wishlist, $this->wishlists_count) = $collection->getWishlistCustomerCount();
        
        $this->items_bought = 0;
        $this->shared_count = $collection->getSharedCount();
        $this->referrals_count = 0;
        $this->conversions_count = 0;
                
        return $this;
    }   
}