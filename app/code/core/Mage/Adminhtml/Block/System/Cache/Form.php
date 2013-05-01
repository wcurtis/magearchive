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
 * Cache management form page
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_System_Cache_Form extends Mage_Adminhtml_Block_Widget_Form
{

    public function initForm()
    {
        $types = array(
            'config'     => Mage::helper('adminhtml')->__('Configuration'),
            'layout'     => Mage::helper('adminhtml')->__('Layouts'),
            'block_html' => Mage::helper('adminhtml')->__('Blocks HTML output'),
            'eav'        => Mage::helper('adminhtml')->__('EAV types and attributes'),
            'translate'  => Mage::helper('adminhtml')->__('Translations'),
            'pear'       => Mage::helper('adminhtml')->__('PEAR Channels and Packages'),
        );

        $options = array(
            0 => Mage::helper('adminhtml')->__('Disabled'),
            1 => Mage::helper('adminhtml')->__('Enabled'),
            2 => Mage::helper('adminhtml')->__('Clean and Disable'),
            3 => Mage::helper('adminhtml')->__('Clean and Enable')
        );

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('cache_enable', array(
            'legend'=>Mage::helper('adminhtml')->__('Cache control')
        ));

        $fieldset->addField('refresh_all_cache', 'checkbox', array(
            'name'=>'refresh[all_cache]',
            'label'=>Mage::helper('adminhtml')->__('Refresh All Cache'),
            'value'=>1,
        ));

        foreach ($types as $type=>$label) {
            $fieldset->addField('enable_'.$type, 'select', array(
                'name'=>'enable['.$type.']',
                'label'=>$label,
                'value'=>(int)Mage::app()->useCache($type),
                'options'=>$options,
            ));
        }

        $fieldset = $form->addFieldset('catalog', array(
            'legend'=>Mage::helper('adminhtml')->__('Catalog')
        ));

        $fieldset->addField('refresh_catalog_rewrites', 'checkbox', array(
            'name'=>'refresh[catalog_rewrites]',
            'label'=>Mage::helper('adminhtml')->__('Refresh Catalog Rewrites'),
            'value'=>1,
        ));

        $fieldset->addField('clear_images_cache', 'checkbox', array(
            'name'=>'clear_images_cache',
            'label'=>Mage::helper('adminhtml')->__('Clear Images Cache'),
            'value'=>1,
        ));
/*
        $fieldset = $form->addFieldset('database', array(
            'legend'=>Mage::helper('adminhtml')->__('Database')
        ));

        $values = Mage::getSingleton('adminhtml/system_config_source_dev_dbautoup')
            ->toOptionArray();
        $fieldset->addField('db_auto_update', 'select', array(
            'name'=>'db_auto_update',
            'label'=>Mage::helper('adminhtml')->__('Auto Updates'),
            'value'=>Mage::getSingleton('core/resource')->getAutoUpdate(),
            'values'=>$values,
        ));
*/
        $this->setForm($form);

        return $this;
    }

}
