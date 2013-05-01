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
 * Category tree model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Entity_Category_Tree extends Varien_Data_Tree_Db
{
    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');

        parent::__construct(
            $resource->getConnection('catalog_read'),
            $resource->getTableName('catalog/category_tree'),
            array(
                Varien_Data_Tree_Db::ID_FIELD       => 'entity_id',
                Varien_Data_Tree_Db::PARENT_FIELD   => 'pid',
                Varien_Data_Tree_Db::LEVEL_FIELD    => 'level',
                Varien_Data_Tree_Db::ORDER_FIELD    => 'order'
            )
        );
    }

    public function addCollectionData($collection)
    {
        $nodeIds = array();
        foreach ($this->getNodes() as $node) {
        	$nodeIds[] = $node->getId();
        }

        $collection->addIdFilter($nodeIds)
            ->load();
        foreach ($collection as $category) {
        	$this->getNodeById($category->getId())->addData($category->getData());
        }
        return $this;
    }
}
