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
 * System config form block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_System_Config_Form extends Mage_Adminhtml_Block_Widget_Form
{
    const SCOPE_DEFAULT = 'default';
    const SCOPE_WEBSITE = 'website';
    const SCOPE_STORE   = 'store';

    public function __construct()
    {
        parent::__construct();
    }

    public function initForm()
    {
        /**
         * @see  Varien_Object::__call()
         */
        // get section fields from config xml

        $sectionCode = $this->getRequest()->getParam('section');
        $websiteCode = $this->getRequest()->getParam('website');
        $storeCode = $this->getRequest()->getParam('store');

        $isDefault = !$websiteCode && !$storeCode;

        $prefix = $storeCode ? 'stores/'.$storeCode : ($websiteCode ? 'websites/'.$websiteCode : 'default');
        $configRoot = Mage::getConfig()->getNode($prefix);

        // get config section data from database
        $configData = Mage::getModel('adminhtml/config_data')
            ->setSection($sectionCode)
            ->setWebsite($websiteCode)
            ->setStore($storeCode)
            ->load();

        $configFields = Mage::getSingleton('adminhtml/config');
        $sections = $configFields->getSection($sectionCode, $websiteCode, $storeCode);

        $form = new Varien_Data_Form();

        $defaultFieldsetRenderer = Mage::getHelper('adminhtml/system_config_form_fieldset');
        $defaultFieldRenderer = Mage::getHelper('adminhtml/system_config_form_field');
        $fieldset = array();

        foreach ($sections as $section) {
            if (!$this->_canShowField($section)) {
                continue;
            }
            foreach ($section->groups as $groups){

                $groups = (array)$groups;
                usort($groups, array($this, '_sortForm'));

                foreach ($groups as $group){
                    if (!$this->_canShowField($group)) {
                        continue;
                    }

                    if ($group->frontend_model) {
                        $fieldsetRenderer = Mage::getHelper((string)$group->frontend_model);
                    } else {
                        $fieldsetRenderer = $defaultFieldsetRenderer;
                    }

                    $fieldsetRenderer->setForm($this);
                    $fieldsetRenderer->setConfigData($configData);

                    if ($configFields->hasChildren($group, $websiteCode, $storeCode)) {

                        $helperName = $configFields->getAttributeModule($section, $group);

                        $fieldset[$group->getName()] = $form->addFieldset(
                            $group->getName(),
                            array('legend' => Mage::helper($helperName)->__((string)$group->label)))
                            ->setRenderer($fieldsetRenderer);
                        $this->_addElementTypes($fieldset[$group->getName()]);

                        foreach ($group->fields as $elements){

                            $elements = (array)$elements;
                            usort($elements, array($this, '_sortForm'));

                            foreach ($elements as $e){
                                if (!$this->_canShowField($e)) {
                                    continue;
                                }
                                $path = $section->getName().'/'.$group->getName().'/'.$e->getName();
                                $id = $section->getName().'_'.$group->getName().'_'.$e->getName();

                                if (isset($configData[$path])) {
                                    $data = $configData[$path];
                                    $inherit = false;
                                } else {
                                    $data = $configRoot->descend($path);
                                    $inherit = true;
                                }
                                if ($e->frontend_model) {
                                    $fieldRenderer = Mage::getHelper((string)$e->frontend_model);
                                } else {
                                    $fieldRenderer = $defaultFieldRenderer;
                                }

                                $helperName = $configFields->getAttributeModule($section, $group, $e);

                                $fieldRenderer->setForm($this);
                                $fieldRenderer->setConfigData($configData);

                                $fieldType = (string)$e->frontend_type;

                                if ($e->backend_model) {
                                    $model = Mage::getModel((string)$e->backend_model);
                                    if (!$model instanceof Mage_Core_Model_Config_Data) {
                                        Mage::throwException('Invalid config field backend model: '.(string)$e->backend_model);
                                    }
                                    $model->setPath($path)->setValue($data)->afterLoad();
                                    $data = $model->getValue();
                                }

                                $comment = (string)$e->comment ? Mage::helper($helperName)->__((string)$e->comment) : '';
                                $field = $fieldset[$group->getName()]->addField(
                                  $id, $fieldType ? $fieldType : 'text',
                                    array(
                                        'name'          => 'groups['.$group->getName().'][fields]['.$e->getName().'][value]',
                                        'label'         => Mage::helper($helperName)->__((string)$e->label),
                                        'comment'       => $comment,
                                        'value'         => $data,
                                        'inherit'       => $inherit,
                                        'class'         => $e->frontend_class,
                                        'can_use_default_value' => $this->canUseDefaultValue((int)$e->show_in_default),
                                        'can_use_website_value' => $this->canUseWebsiteValue((int)$e->show_in_website),
                                    ))->setRenderer($fieldRenderer);
                                if ($srcModel = (string)$e->source_model) {
                                    $field->setValues(Mage::getSingleton($srcModel)->toOptionArray($fieldType == 'multiselect'));
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->setForm($form);
        return $this;
    }
    protected function _sortForm($a, $b)
    {
        return (int)$a->sort_order < (int)$b->sort_order ? -1 : ((int)$a->sort_order > (int)$b->sort_order ? 1 : 0);

    }
    public function canUseDefaultValue($field)
    {
        if ($this->getScope() == self::SCOPE_STORE && $field) {
            return true;
        }
        if ($this->getScope() == self::SCOPE_WEBSITE && $field) {
            return true;
        }
        return false;
    }

    public function canUseWebsiteValue($field)
    {
        if ($this->getScope() == self::SCOPE_STORE && $field) {
            return true;
        }
        return false;
    }

    /**
     * Checking field visibility
     *
     * @param   Varien_Object $field
     * @return  bool
     */
    protected function _canShowField($field)
    {
        switch ($this->getScope()) {
            case self::SCOPE_DEFAULT:
                return (int)$field->show_in_default;
                break;
            case self::SCOPE_WEBSITE:
                return (int)$field->show_in_website;
                break;
            case self::SCOPE_STORE:
                return (int)$field->show_in_store;
                break;
        }
        return true;
    }

    /**
     * Retrieve current scope
     *
     * @return string
     */
    public function getScope()
    {
        $scope = $this->getData('scope');
        if (is_null($scope)) {
            $sectionCode = $this->getRequest()->getParam('section');
            $websiteCode = $this->getRequest()->getParam('website');
            $storeCode = $this->getRequest()->getParam('store');

            if (!$websiteCode && !$storeCode) {
                $scope = self::SCOPE_DEFAULT;
            }
            elseif ($storeCode) {
                $scope = self::SCOPE_STORE;
            }
            elseif ($websiteCode) {
                $scope = self::SCOPE_WEBSITE;
            }
            else {
                $scope = false;
            }
            $this->setData('scope', $scope);
        }

        return $scope;
    }

    protected function _getAdditionalElementTypes()
    {
        return array(
            'export'    => Mage::getConfig()->getBlockClassName('adminhtml/system_config_form_field_export'),
            'import'    => Mage::getConfig()->getBlockClassName('adminhtml/system_config_form_field_import'),
            'allowspecific'  => Mage::getConfig()->getBlockClassName('adminhtml/system_config_form_field_select_allowspecific'),
        );
    }
}
