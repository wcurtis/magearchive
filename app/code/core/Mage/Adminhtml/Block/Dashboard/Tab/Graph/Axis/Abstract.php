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
 * Adminhtml dashboard graph axis abstract
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

abstract class Mage_Adminhtml_Block_Dashboard_Tab_Graph_Axis_Abstract extends Mage_Core_Block_Abstract
{
	protected $_collection = null;
	protected $_labelFilter = null;
	protected $_labels = null;

	const DIRECTION_HORIZONTAL = 'horizontal';
	const DIRECTION_VERTICAL   = 'vertical';

	/**
	 * Set custom collection for axis
	 *
	 * @param mixed $collection
	 * @return Mage_Adminhtml_Block_Dashboard_Tab_Graph_Axis_Abstract
	 */
	public function setCollection($collection)
	{
		$this->_collection = $collection;
		return $this;
	}

	/**
	 * Return collection for axis, if custom not
	 * specified set as it default collection
	 *
	 * @return mixed
	 */
	public function getCollection()
	{
		if(is_null($this->_collection)) {
			$this->_collection = $this->getParentBlock()->getDataHelper()->getCollection();
		}

		return $this->_collection;
	}

	/**
	 * Return labels for this axis
	 *
	 * @return array
	 */
	public function getLabels()
	{
		if(is_null($this->_labels)) {
			$this->_initLabels();
		}

		return $this->_labels;
	}


	/**
	 * Return total count of labels
	 *
	 * @return int
	 */
	public function getLablesCount()
	{
	       return count($this->_labels);
	}

	/**
	 * Protected method for initializing of labels
	 *
	 * @return Mage_Adminhtml_Block_Dashboard_Tab_Graph_Axis_Abstract
	 */
	protected function _initLabels()
	{
		$this->_labels = array();
		return $this;
	}

	/**
	 * Return direction for axis
	 * Possible values are Mage_Adminhtml_Block_Dashboard_Tab_Graph_Axis_Abstract::DIRECTION_HORIZONTAL
	 * Mage_Adminhtml_Block_Dashboard_Tab_Graph_Axis_Abstract::DIRECTION_VERTICAL
	 */
	abstract public function getDirection();

	/**
	 * Aplies filter for labels.
	 *
	 * @param Zend_Filter_Interface $filter
	 * @return Mage_Adminhtml_Block_Dashboard_Tab_Graph_Axis_Abstract
	 */
	public function setLabelFilter(Zend_Filter_Interface $filter)
	{
		$this->_labelFilter = $filter;
		return $this;
	}

	/**
	 * Return filter for labels
	 *
	 * @return null|Zend_Filter_Interface
	 */
	public function getLabelFilter()
	{
		return $this->_labelFilter;
	}

	/**
	 * Return text from value
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function getLabelText($value)
	{
		if($this->getLabelFilter()) {
			return $this->getLabelFilter()->filter($value);
		}

		if($this->getCurrencyCode()) {
		    return Mage::app()->getLocale()->currency($this->getCurrencyCode())->toCurrency($value);
		}

		return $value;
	}

	/**
	 * Return title for axis
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->getData('title');
	}

	/**
	 * Set title for axis
	 *
	 * @param string $title
	 * @return Mage_Adminhtml_Block_Dashboard_Tab_Graph_Axis_Abstract
	 */
	public function setTitle($title)
	{
		$this->setData('title', $title);
		return $this;
	}

	/**
	 * Return value of DIRECTION_HORIZONTAL constant
	 *
	 * @return string
	 */
	public function getHorizontalDirectionConstant()
	{
		return self::DIRECTION_HORIZONTAL;
	}

	/**
	 * Return value of DIRECTION_VERTICAL constant
	 *
	 * @return string
	 */
	public function getVerticalDirectionConstant()
	{
		return self::DIRECTION_VERTICAL;
	}

	/**
	 * Return pixels from value
	 *
	 * @param mixed $item
	 * @param Mage_Adminhtml_Block_Dashboard_ $series
	 * @return int
	 */
	public function getPixelPosition($item, $series)
	{
		return $series->getValue($item, $this);
	}

	/**
	 * Return span for table with grath
	 *
	 * @return int
	 */
	public function getSpan()
	{
		return sizeof($this->getLabels()) + 2;
	}

	/**
	 * Return the maximum point on graph
	 *
	 * @param mixed $item
	 * @return int
	 */
	public function getPixelMaximum($item)
	{
		return 0;
	}
}// Class Mage_Adminhtml_Block_Graph_Axis_Abstract END