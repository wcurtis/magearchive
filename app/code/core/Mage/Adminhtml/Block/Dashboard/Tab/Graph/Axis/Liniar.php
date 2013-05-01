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
 * Adminhtml Dashboard graph liniar axis
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Dashboard_Tab_Graph_Axis_Liniar extends Mage_Adminhtml_Block_Dashboard_Tab_Graph_Axis_Abstract
{
    protected $_chachedMaximumValue = null;
    protected $_maximumStepValue = null;

    const DEFAULT_LENGTH = 250;
    const DEFAULT_PERIOD = 50;

    protected function _initLabels()
    {
        parent::_initLabels();

        for ($i = 0; $i<=$this->getStepCount(); $i++) {
            $this->_labels[] = $this->getLabelText($i*$this->getStepSize());
        }

        $this->_labels = array_reverse($this->_labels);

        return $this;
    }

    public function getDirection()
    {
        return self::DIRECTION_VERTICAL;
    }

    protected function _getMaximumValue()
    {
        if(is_null($this->_chachedMaximumValue)) {
            foreach($this->getParentBlock()->getAllSeries() as $series) {
                foreach ($this->getCollection() as $item) {
                    if(is_null($this->_chachedMaximumValue)) {
                        $this->_chachedMaximumValue = $series->getValue($item, $this);
                    }

                    $this->_chachedMaximumValue = max($this->_chachedMaximumValue, $series->getValue($item, $this));
                }
            }

            if($this->_chachedMaximumValue <= 0) {
                $this->_chachedMaximumValue = $this->getLegth()/$this->getPeriod();
            }
        }



        return $this->_chachedMaximumValue;
    }

    public function getLength()
    {
        if(is_null($this->getData('length'))) {
            return self::DEFAULT_LENGTH;
        }

        return $this->getData('length');
    }

    public function getPeriod()
    {
        if(is_null($this->getData('period'))) {
            return self::DEFAULT_PERIOD;
        }

        return $this->getDat('period');
    }


    public function getMaximumStepValue()
    {
        if(is_null($this->_maximumStepValue)) {
            $decimalCount = strlen((string)ceil($this->_getMaximumValue()));
            if($this->_getMaximumValue()/pow(10,$decimalCount) >= 0.75){
                $this->_maximumStepValue =  pow(10,$decimalCount);
            } else {
                $this->_maximumStepValue = ceil($this->_getMaximumValue()/pow(10,$decimalCount-1))*pow(10,$decimalCount-1);
            }

            if($this->_maximumStepValue < 10) {
                $this->_maximumStepValue = 10;
            }
        }

        return $this->_maximumStepValue;
    }

    public function getStepCount()
    {
        return ceil($this->getLength()/$this->getPeriod());
    }

    public function getStepSize()
    {
        return round($this->getMaximumStepValue()/$this->getStepCount()*100) / 100;
    }

    public function getPixelPosition($item, $series)
    {
        return $this->getMaximumStepValue() ? round($this->getLength()*($series->getValue($item, $this)/$this->getMaximumStepValue())) : 0;
    }

    public function getPixelMaximum($item)
    {
        $values = array();
        foreach($this->getParentBlock()->getAllSeries() as $series) {
            $values[] = $this->getPixelPosition($item, $series);
        }
        return max($values);
    }

} // Class Mage_Adminhtml_Block_Dashboard_Tab_Graph_Axis_Liniar end