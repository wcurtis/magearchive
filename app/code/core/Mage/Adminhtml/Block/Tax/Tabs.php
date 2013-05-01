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
 * Admin tax tabs block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Tax_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();

        $this->addTab('tax_rule', array(
            'label'     => Mage::helper('tax')->__('Tax Rules'),
            'url'      => Mage::getUrl('*/tax_rule')
        ));

        $this->addTab('tax_rate', array(
            'label'     => Mage::helper('tax')->__('Tax Rates'),
            'url'      => Mage::getUrl('*/tax_rate')
        ));

        $this->addTab('tax_class_customer', array(
            'label'     => Mage::helper('tax')->__('Customer Tax Classes'),
            'url'      => Mage::getUrl('*/tax_class_customer')
        ));

        $this->addTab('tax_class_product', array(
            'label'     => Mage::helper('tax')->__('Product Tax Classes'),
            'url'      => Mage::getUrl('*/tax_class_product')
        ));
    }

    protected function _checkActiveTab($tabId)
    {
        return ( $this->getActive() == $tabId ) ? true : false;
    }
}
