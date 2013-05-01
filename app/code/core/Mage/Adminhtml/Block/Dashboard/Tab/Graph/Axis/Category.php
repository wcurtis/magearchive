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
 * Dashboard graph category axis
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Dashboard_Tab_Graph_Axis_Category extends Mage_Adminhtml_Block_Dashboard_Tab_Graph_Axis_Abstract
{
    protected function _initLabels()
    {
	parent::_initLabels();
         foreach($this->getParentBlock()->getAllSeries() as $series) {
		foreach ($this->getCollection() as $item) {
		    if(!$this->_labelExists($this->getLabelText($series->getValue($item, $this)))) {
			$this->_labels[] = $this->getLabelText($series->getValue($item, $this));
		    }
		}
	}
	return $this;
    }

    protected function _labelExists($label)
    {
        return in_array($label, $this->_labels);
    }

    public function getDirection()
    {
        return self::DIRECTION_HORIZONTAL;
    }
} // Class Mage_Adminhtml_Block_Dashboard_Tab_Graph_Axis_Category end