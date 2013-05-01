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
 * Store switcher block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Store_Switcher extends Mage_Core_Block_Template
{
    protected $_storeIds;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('store/switcher.phtml');
        $this->setUseConfirm(true);
        $this->setDefaultStoreName($this->__('All Store Views'));
    }

    public function getWebsiteCollection()
    {
        return Mage::getModel('core/website')->getResourceCollection()
            ->load();
    }

    public function getStores($website)
    {
        $stores = $website->getStoreCollection();
        if (!empty($this->_storeIds)) {
            $stores->addIdFilter($this->_storeIds);
        }
        return $stores->load();
    }

    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return Mage::getUrl('*/*/*', array('_current'=>true, 'store'=>null));
    }

    public function getStoreId()
    {
        return $this->getRequest()->getParam('store');
    }

    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }
}
