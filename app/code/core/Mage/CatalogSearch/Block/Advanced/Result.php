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
 * @package    Mage_CatalogSearch
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Advanced search result
 *
 * @category   Mage
 * @package    Mage_CatalogSearch
 */
class Mage_CatalogSearch_Block_Advanced_Result extends Mage_Catalog_Block_Product_List
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->getBlock('breadcrumbs')
            ->addCrumb('home',
                array('label'=>Mage::helper('catalogsearch')->__('Home'),
                    'title'=>Mage::helper('catalogsearch')->__('Go to Home Page'),
                    'link'=>Mage::getBaseUrl())
                )
            ->addCrumb('search',
                array('label'=>Mage::helper('catalogsearch')->__('Catalog Advanced Search'), 'link'=>$this->getUrl('*/*/'))
                )
            ->addCrumb('search_result',
                array('label'=>Mage::helper('catalogsearch')->__('Results'))
                );
        return $this;
    }
    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $this->_productCollection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image');
                Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($this->_productCollection);
                Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($this->_productCollection);

            $this->_addFilters();
        }
        return parent::_getProductCollection();
    }

    protected function _addFilters()
    {
        $attributes = $this->getSearchModel()->getAttributes();
        $values = $this->getRequest()->getQuery();

        foreach ($attributes as $attribute) {
            $code      = $attribute->getAttributeCode();
            $condition = false;

            if (isset($values[$code])) {
                $value = $values[$code];
                if (is_array($value)) {
                    if ((isset($value['from']) && strlen($value['from']) > 0) || (isset($value['to']) && strlen($value['to']) > 0)) {
                        $condition = $value;
                    }
                    elseif(!isset($value['from']) && !isset($value['to'])) {
                        if ($attribute->getBackend()->getType() == 'int') {
                            $condition = array('in'=>$value);
                        }
                    }
                }
                else {
                    if (strlen($value)>0) {
                        if (in_array($attribute->getBackend()->getType(), array('varchar', 'text'))) {
                            $condition = array('like'=>'%'.$value.'%');
                        }
                        else {
                            $condition = $value;
                        }
                    }
                }
            }

            if ($condition) {
                $this->_getProductCollection()->addFieldToFilter($code, $condition);
            }
        }
        return $this;
    }

    public function getSearchModel()
    {
        return Mage::getSingleton('catalogsearch/advanced');
    }
}
