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
 * Catalog category tree_path attribute backend model
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Entity_Category_Attribute_Backend_Tree_Path extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    public function afterSave($object)
    {
        parent::afterSave($object);
        $tree = $object->getTreeModel()
            ->load();

        $store = $this->getAttribute()->getEntity()->getStore();
        $lastNodeId = $store->getConfig('catalog/category/root_id');

        $nodeIds = array();
        $path = $tree->getPath($object->getId());
        foreach ($path as $node) {
            // $node->getLevel()<=1 - need fix
            if ($node->getId() == $lastNodeId || $node->getLevel()<=1) {
                break;
            }
            $nodeIds[] = $node->getId();
        }
        
        $object->setData($this->getAttribute()->getAttributeCode(), implode(',', $nodeIds));
        $this->getAttribute()->getEntity()
            ->saveAttribute($object, $this->getAttribute()->getAttributeCode());

        return $this;
    }
}
