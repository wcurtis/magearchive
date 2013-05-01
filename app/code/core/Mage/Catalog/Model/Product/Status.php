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

/**
 * Product type model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Product_Status extends Mage_Core_Model_Abstract
{
    const STATUS_ENABLED            = 1;
    const STATUS_DISABLED           = 2;
    const STATUS_OUT_OF_STOCK       = 3;

    protected function _construct()
    {
        $this->_init('catalog/product_status');
    }

    public function addVisibleFilterToCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        $collection->addAttributeToFilter('status', array('in'=>$this->getVisibleStatusIds()));
        return $this;
    }

    public function addSaleableFilterToCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        $collection->addAttributeToFilter('status', array('in'=>$this->getSaleableStatusIds()));
        return $this;
    }

    public function getVisibleStatusIds()
    {
        return array(self::STATUS_ENABLED, self::STATUS_OUT_OF_STOCK);
    }

    public function getSaleableStatusIds()
    {
        return array(self::STATUS_ENABLED);
    }
}
