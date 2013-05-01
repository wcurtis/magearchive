<?php

class Mage_Adminhtml_Model_System_Config_Backend_Encrypted extends Mage_Core_Model_Config_Data
{
    protected function _afterLoad()
    {
        $value = (string)$this->getValue();
        if (!empty($value) && ($decrypted = Mage::helper('core')->decrypt($value))) {
            $this->setValue($decrypted);
        }
    }

    protected function _beforeSave()
    {
        $value = (string)$this->getValue();
        if (!empty($value) && ($encrypted = Mage::helper('core')->encrypt($value))) {
            $this->setValue($encrypted);
        }
    }
}