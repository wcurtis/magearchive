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
 * Product Stores tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Stores extends Mage_Adminhtml_Block_Store_Switcher
{
    protected $_storeCillection;
    protected $_storeFromHtml;
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/edit/stores.phtml');
        $this->_storeCillection = Mage::getResourceModel('core/store_collection')
            ->load();
    }
    
    public function getStoreId()
    {
        return Mage::registry('product')->getStoreId();
    }
    
    public function getProductId()
    {
        return Mage::registry('product')->getId();
    }
    
    public function isProductInStore($storeId)
    {
        return in_array($storeId, Mage::registry('product')->getStoreIds());
    }
    
    public function getStoreName($storeId)
    {
        if ($store = $this->_storeCillection->getItemById($storeId)) {
            return $store->getName();
        }
        return '';
    }
    
    public function getStoreCollection()
    {
        return $this->_storeCillection;
    }
    
    public function getChooseFromStoreHtml()
    {
        if (!$this->_storeFromHtml) {
            $stores = Mage::registry('product')->getStoreIds();
            $this->_storeFromHtml = '<select name="store_chooser">';
            $this->_storeFromHtml.= '<option value="0">'.Mage::helper('catalog')->__('Default Store').'</option>';
            foreach ($this->_storeCillection as $store) {
            	if ($store->getId() && in_array($store->getId(), $stores)) {
            	    $this->_storeFromHtml.= '<option value="'.$store->getId().'">'.$store->getName().'</option>';
            	}
            }
            $this->_storeFromHtml.= '</select>';
        }
        return $this->_storeFromHtml;
    }
}
