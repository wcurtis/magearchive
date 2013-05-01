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
 * Product visibility model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Product_Visibility extends Varien_Object 
{
    const VISIBILITY_NOT_VISIBLE    = 1;
    const VISIBILITY_IN_CATALOG     = 2;
    const VISIBILITY_IN_SEARCH      = 3;
    const VISIBILITY_BOTH           = 4;
    
    public function __construct()
    {
        parent::__construct();
        $this->setIdFieldName('visibility_id');
    }
    
    public function addVisibleInCatalogFilterToCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        $collection->addAttributeToFilter('visibility', array('in'=>$this->getVisibleInCatalogIds()));
        return $this;
    }
    
    public function addVisibleInSearchFilterToCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        $collection->addAttributeToFilter('visibility', array('in'=>$this->getVisibleInSearchIds()));
        return $this;
    }
    
    public function getVisibleInCatalogIds()
    {
        return array(self::VISIBILITY_IN_CATALOG, self::VISIBILITY_BOTH);
    }

    public function getVisibleInSearchIds()
    {
        return array(self::VISIBILITY_IN_SEARCH, self::VISIBILITY_BOTH);
    }
}
