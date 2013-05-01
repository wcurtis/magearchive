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
 * @package    Mage_Eav
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Eav_Model_Entity_Attribute extends Mage_Eav_Model_Entity_Attribute_Abstract
{
    protected function _getDefaultBackendModel()
    {
        switch ($this->getAttributeCode()) {
            case 'created_at':
                return 'eav/entity_attribute_backend_time_created';

            case 'updated_at':
                return 'eav/entity_attribute_backend_time_updated';

            case 'store_id':
                return 'eav/entity_attribute_backend_store';

            case 'increment_id':
                return 'eav/entity_attribute_backend_increment';
        }



        return parent::_getDefaultBackendModel();
    }

    protected function _getDefaultFrontendModel()
    {
        return parent::_getDefaultFrontendModel();
    }

    protected function _getDefaultSourceModel()
    {
        switch ($this->getAttributeCode()) {
            case 'store_id':
                return 'eav/entity_attribute_source_store';
        }
        return parent::_getDefaultSourceModel();
    }

    public function deleteEntity()
    {
        return $this->_getResource()->deleteEntity($this);
    }

    protected function _beforeSave()
    {
        if ($this->getBackendType() == 'datetime') {
            if (!$this->getBackendModel()) {
                $this->setBackendModel('eav/entity_attribute_backend_datetime');
            }

            if (!$this->getFrontendModel()) {
                $this->setFrontendModel('eav/entity_attribute_frontend_datetime');
            }
        }

        return parent::_beforeSave();
    }
}