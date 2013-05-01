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
 * Adminhtml dashboard html/css grath block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Dashboard_Tab_Graph extends Mage_Adminhtml_Block_Dashboard_Tab_Abstract
{
	protected $_horizontalAxis = null;
	protected $_verticalAxis = null;

	protected $_allSeries = array();

	const DEFAULT_VAXIS = 'liniar';
	const DEFAULT_HAXIS = 'time';
	const SERIES_TYPE = 'adminhtml/dashboard_tab_graph_series';

	public function getHorizontalAxis()
	{
		if(is_null($this->_horizontalAxis)) {
			$this->setHorizontalAxis( $this->getData('horizontal_axis') );
		}

		return $this->_horizontalAxis;
	}

	protected function  _getTabTemplate()
	{
		return 'dashboard/tab/graph.phtml';
	}

	public function getVerticalAxis()
	{
		if(is_null($this->_verticalAxis)) {
			$this->setVerticalAxis( $this->getData('veritical_axis') );
		}

		return $this->_verticalAxis;
	}

	public function setHorizontalAxis($axisType=null)
	{
		if(is_null($axisType)) {
			$axisType = self::DEFAULT_HAXIS;
		}

		$this->_horizontalAxis = $this->getLayout()->createBlock('adminhtml/dashboard_tab_graph_axis_' . $axisType);
		$this->setChild('horizontal_axis', $this->_horizontalAxis);
		return $this;
	}

	public function setVerticalAxis($axisType=null)
	{
		if(is_null($axisType)) {
			$axisType = self::DEFAULT_VAXIS;
		}

		$this->_verticalAxis = $this->getLayout()->createBlock('adminhtml/dashboard_tab_graph_axis_' . $axisType);
		$this->setChild('vertical_axis', $this->_verticalAxis);
		return $this;
	}

	public function addSeries($seriesId, array $options)
	{
		$series = $this->getLayout()->createBlock(self::SERIES_TYPE);
		$series->setData($options);

		if(isset($options['x_field'])) {
			$series->setXField($options['x_field']);
		}

		if(isset($options['y_field'])) {
			$series->setYField($options['y_field']);
		}

		$this->setChild('series_' . $seriesId, $series);
		$this->_allSeries[] = $series;
		return $this;
	}

	public function getSeries($seriesId)
	{
		if($this->getChild('series_' . $seriesId)) {
			return $this->getChild('series_' . $seriesId);
		}

		return null;
	}

	public function getAllSeries()
	{
		return $this->_allSeries;
	}

}// Class Mage_Adminhtml_Block_Dashboard_Tab_Graph END