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

/**
 * Model for working with system.xml module files
 *
 */
class Mage_Core_Model_Config_System extends Mage_Core_Model_Config_Base
{
    function __construct($sourceData=null)
    {
        parent::__construct($sourceData);
    }

    public function load($module)
    {
        $file = Mage::getConfig()->getModuleDir('etc', $module).DS.'system.xml';
        $this->loadFile($file);
        return $this;
    }

    public function getDefaultValues()
    {
        $values = array();
        if (!$this->_xml || !$this->getNode()) {
            return $values;
        }
        $children = $this->getNode()->children();
        foreach ($children[0] as $section) {
            $sectionCode = $section->getName();
            if (!$section->groups) {
                continue;
            }
            foreach ($section->groups->children() as $group) {
                $groupCode = $group->getName();
                if (!$group->fields) {
                    continue;
                }
                foreach ($group->fields->children() as $field) {
                	$fieldCode = $field->getName();
                	$value = (string) $field->default_value;
                    $values[$sectionCode.'/'.$groupCode.'/'.$fieldCode] = $value;
                }
            }
        }
        return $values;
    }
}
