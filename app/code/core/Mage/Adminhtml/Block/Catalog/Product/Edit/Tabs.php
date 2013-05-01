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
 * admin product edit tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('product_info_tabs');
        $this->setDestElementId('product_edit_form');
        $this->setTitle(Mage::helper('catalog')->__('Product Information'));
    }

    protected function _prepareLayout()
    {
        $product = Mage::registry('product');

        if (!($setId = $product->getAttributeSetId())) {
            $setId = $this->getRequest()->getParam('set', null);
        }

        if (!($superAttributes = $product->getSuperAttributesIds())) {
            $superAttributes = false;
        }

        if ($setId && (!$product->isSuperConfig() || $superAttributes !== false ) ) {
            $groupCollection = Mage::getResourceModel('eav/entity_attribute_group_collection')
                ->setAttributeSetFilter($setId)
                ->load();

            foreach ($groupCollection as $group) {
                $attributes = $product->getAttributes($group->getId(), true);
                // do not add grops without attributes
                if (count($attributes)==0) {
                    continue;
                }

                $this->addTab('group_'.$group->getId(), array(
                    'label'     => Mage::helper('catalog')->__($group->getAttributeGroupName()),
                    'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_attributes')
                        ->setGroup($group)
                        ->setGroupAttributes($attributes)
                        ->toHtml(),
                ));
            }

            if (!$product->isSuperConfig()) {
                $this->addTab('inventory', array(
                    'label'     => Mage::helper('catalog')->__('Inventory'),
                    'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_inventory')->toHtml(),
                ));
            }

            $this->addTab('stores', array(
                'label'     => Mage::helper('catalog')->__('Stores'),
                'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_stores')->toHtml(),
            ));

            $this->addTab('categories', array(
                'label'     => Mage::helper('catalog')->__('Categories'),
                'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_categories')->toHtml(),
            ));

            $this->addTab('related', array(
                'label'     => Mage::helper('catalog')->__('Related Products'),
                'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_related', 'admin.related.products')->toHtml(),
            ));

            $this->addTab('upsell', array(
                'label'     => Mage::helper('catalog')->__('Up-sells'),
                'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_upsell', 'admin.upsell.products')->toHtml(),
            ));

            $this->addTab('crosssell', array(
                'label'     => Mage::helper('catalog')->__('Cross-sells'),
                'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_crosssell', 'admin.crosssell.products')->toHtml(),
            ));
            $storeCode = $this->getRequest()->getParam('store');
            /*if($storeCode){
                $isDisabled = Mage::getStoreConfig('advanced/modules_disable_output/Mage_CustomerAlert',$storeCode);
            }
            if( $this->getRequest()->getParam('store', false) > 0 && !$isDisabled) {
                $this->addTab('alerts', array(
                    'label'     => Mage::helper('catalog')->__('Product Alerts'),
                    'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_alerts', 'admin.alerts.products')->toHtml(),
                ));
            } */

            if( $this->getRequest()->getParam('id', false) ) {
                $this->addTab('reviews', array(
                    'label'     => Mage::helper('catalog')->__('Product Reviews'),
                    'content'   => $this->getLayout()->createBlock('adminhtml/review_grid', 'admin.product.reviews')
                            ->setProductId($this->getRequest()->getParam('id'))
                            ->setUseAjax(true)
                            ->toHtml(),
                ));

                $this->addTab('tags', array(
                    'label'     => Mage::helper('catalog')->__('Product Tags'),
                    'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_tag', 'admin.product.tags')
                            ->setProductId($this->getRequest()->getParam('id'))
                            ->toHtml(),
                ));

                $this->addTab('customers_tags', array(
                    'label'     => Mage::helper('catalog')->__('Customers Tagged Product'),
                    'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_tag_customer', 'admin.product.tags.customers')
                            ->setProductId($this->getRequest()->getParam('id'))
                            ->toHtml(),
                ));
            }

            if ($product->isBundle()) {

                $this->addTab('bundle', array(
                    'label' => Mage::helper('catalog')->__('Bundle'),
                    'content' => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_bundle')->toHtml(),
                ));
            }

            if ($product->isSuperGroup()) {
                $this->addTab('super', array(
                    'label' => Mage::helper('catalog')->__('Associated Products'),
                    'content' => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_super_group', 'admin.super.group.product')->toHtml()
                ));
            }
            elseif ($product->isSuperConfig()) {
                $this->addTab('super', array(
                    'label' => Mage::helper('catalog')->__('Associated Products'),
                    'content' => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_super_config', 'admin.super.config.product')->toHtml()
                ));
            }
        }
        elseif ($setId) {
            $this->addTab('super_settings', array(
                'label'     => Mage::helper('catalog')->__('Configurable Product Settings'),
                'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_super_settings')->toHtml(),
                'active'    => true
            ));
        }
        else {
            $this->addTab('set', array(
                'label'     => Mage::helper('catalog')->__('Settings'),
                'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_settings')->toHtml(),
                'active'    => true
            ));
        }
        return parent::_prepareLayout();
    }
}