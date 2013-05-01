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
 * Adminhtml tier pricing item renderer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Tier extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_element = null;

    protected $_customerGroups = null;

    public function __construct()
    {
        $this->setTemplate('catalog/product/edit/price/tier.phtml');
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function getValues()
    {
        $values =array();
        $data = $this->getElement()->getValue();

        if (is_array($data)) {
            usort($data, array($this, '_sortTierPrices'));
            foreach ($data as $value) {
                if (isset($value['price'])) {
                    $value['price'] = number_format($value['price'], 2, null, '');
                }
                $values[] = $value;
            }
        }
        return $values;
    }

    protected function _sortTierPrices($a, $b)
    {
        if ($a['cust_group']!=$b['cust_group']) {
            return $this->getCustomerGroups($a['cust_group'])<$this->getCustomerGroups($b['cust_group']) ? -1 : 1;
        }
        if ($a['price_qty']!=$b['price_qty']) {
            return $a['price_qty']<$b['price_qty'] ? -1 : 1;
        }
        return 0;
    }

    public function getCustomerGroups($groupId=null)
    {
        if (!$this->_customerGroups) {
            $collection = Mage::getModel('customer/group')->getCollection()
                #->setRealGroupsFilter()
                ->load();
            $this->_customerGroups = array(
                Mage_Catalog_Model_Entity_Product_Attribute_Backend_Tierprice::CUST_GROUP_ALL => Mage::helper('catalog')->__('ALL GROUPS'),
            );
            foreach ($collection->getIterator() as $item) {
                $this->_customerGroups[$item->getId()] = $item->getCustomerGroupCode();
            }
        }
        return is_null($groupId) ? $this->_customerGroups :
            (isset($this->_customerGroups[$groupId]) ? $this->_customerGroups[$groupId] : null);
    }

    public function getDefaultCustomerGroup()
    {
        return Mage_Catalog_Model_Entity_Product_Attribute_Backend_Tierprice::CUST_GROUP_ALL;
        #return Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID);
    }

    public function getAfterElementHtml()
    {
        $html = $this->getData('after_element_html');

        $storeId = null;
        $attribute = $this->getElement()->getEntityAttribute();
        if (!$attribute->getIsGlobal()) {
            $storeId = $attribute->getEntity()->getStoreId();
        } else {
            $html .= $this->getGlobalIcon();
        }
        $currencyCode = (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE, $storeId);
        $html .= ' (' . Mage::helper('catalog')->__('Currency') . ' - <strong>'.$currencyCode.'</strong>)';

        return $html;
    }

    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Delete Tier'),
                    'onclick'   => "tierPriceControl.deleteItem('#{index}')",
                    'class' => 'delete'
                )));

        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Add Tier'),
                    'onclick'   => 'tierPriceControl.addItem()',
                    'class' => 'add'
                )));
        return parent::_prepareLayout();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

}
