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
 * Product after creation popup window
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Catalog_Product_Created extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/created.phtml');
    }


    protected function _prepareLayout()
    {
        $this->setChild(
            'close_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => Mage::helper('catalog')->__('Close Window'),
                    'onclick' => 'addProduct(true)'
                ))
        );
    }


    public function getCloseButtonHtml()
    {
        return $this->getChildHtml('close_button');
    }

    public function getProductId()
    {
        return (int) $this->getRequest()->getParam('product');
    }
} // Class Mage_Adminhtml_Block_Catalog_Product_Created End