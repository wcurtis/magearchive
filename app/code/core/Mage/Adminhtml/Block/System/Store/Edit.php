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
 * Admin tag edit block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_System_Store_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'store';
        $this->_controller = 'system_store';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('adminhtml')->__('Save Store'));
        $this->_updateButton('delete', 'label', Mage::helper('adminhtml')->__('Delete Store'));
    }
    
    public function getBackUrl()
    {
        return Mage::getUrl('*/system_config/edit', array('store'=>Mage::registry('admin_current_store')->getCode()));
    }

    public function getHeaderText()
    {
        if (Mage::registry('admin_current_store')->getId()) {
            return Mage::helper('adminhtml')->__("Edit Store '%s'", Mage::registry('admin_current_store')->getName());
        }
        else {
            return Mage::helper('adminhtml')->__('New Store');
        }
    }

}
