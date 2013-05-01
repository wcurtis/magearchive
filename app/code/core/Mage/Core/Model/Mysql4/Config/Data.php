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
 * @package    Mage_Core
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Core_Model_Mysql4_Config_Data extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_configFieldTable;
    
    public function __construct()
    {
        parent::__construct();
        $this->_configFieldTable = Mage::getSingleton('core/resource')->getTableName('core/config_field');
    }
    
    protected function _construct()
    {
        $this->_init('core/config_data', 'config_id');
    }
    
    /**
     * Perform actions after object save
     *
     * @param Varien_Object $object
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if ($backend = $this->_getPathBackend($object->getPath())) {
            $backend->afterSave($object);
        }
        return $this;
    }
    
    protected function _getPathBackend($path)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->_configFieldTable, 'backend_model')
            ->where($read->quoteInto('path=?', $path));
            
        $modelName = $read->fetchOne($select);
        if ($modelName) {
            return Mage::getSingleton($modelName);
        }
        return false;
    }
}