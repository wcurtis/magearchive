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
 * Catalog super product group block
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
 class Mage_Catalog_Block_Product_View_Super_Group extends Mage_Core_Block_Template
 {
     protected $_filter = null;

     public function getItems()
     {
         return Mage::registry('product')->getSuperGroupProductsLoaded();
     }

     public function filterQty($qty)
     {
         if(empty($qty)) {
             return '';
         }
         return $this->getFilter()->filter($qty);
     }

     public function getFilter()
     {
         if(is_null($this->_filter)) {
             $this->_filter = new Zend_Filter_Int();
         }

         return $this->_filter;
     }
 }
