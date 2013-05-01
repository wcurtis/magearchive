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
 * Adminhtml Sitemap add block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Sitemap_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'sitemap';
        $this->_mode = 'edit';

        $this->_updateButton('save', 'label', Mage::helper('adminhtml')->__('Save Sitemap'));
        $this->_updateButton('delete', 'label', Mage::helper('adminhtml')->__('Delete Sitemap'));

        $this->_updateButton('reset', 'id', 'reset_button');

        
    }

    public function getHeaderText()
    {
        return Mage::helper('adminhtml')->__('Edit Sitemap');
    }
}