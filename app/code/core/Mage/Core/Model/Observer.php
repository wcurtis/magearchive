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
 * @package    Mage_Core
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Core_Model_Observer
{
    public function removeProductRewrites($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $collection = Mage::getResourceModel('core/url_rewrite_collection');
        $collection->filterAllByProductId($product->getId())->load();

        foreach ($collection->getItems() as $item){
            $item->delete();
        }
    }

    public function removeCategoryRewrites($observer)
    {
        $category = $observer->getEvent()->getCategory();
        $collection = Mage::getResourceModel('core/url_rewrite_collection');
        $collection->addFieldToFilter('id_path', "category/{$category->getId()}")->load();

        foreach ($collection->getItems() as $item){
            $item->delete();
        }
    }
}