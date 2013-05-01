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
 * Adminhtml dashboard grid with totals
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
 class Mage_Adminhtml_Block_Dashboard_Tab_Grid extends Mage_Adminhtml_Block_Dashboard_Tab_Abstract
 {
    /**
     * @see Mage_Adminhtml_Block_Widget_Grid
     * Columns array
     *
     * array(
     *      'header'    => string,
     *      'width'     => int,
     *      'sortable'  => bool,
     *      'index'     => string,
     *      //'renderer'  => Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Interface,
     *      'format'    => string
     * )
     * @var array
     */
    protected $_columns = array();

    /**
     * Totals indexes list
     *
     * @var array
     */
    protected $_totals = array();

    /**
     * Scroll auto increment
     *
     * @var integer
     */
    static protected $_scrollIndex = 0;

    /**
     * @see Mage_Adminhtml_Block_Widget_Grid
     * Add column to grid
     *
     * @param   string $columnId
     * @param   array
     * @return   Mage_Adminhtml_Block_Dashboard_Tab_Grid
     */
    public function addColumn($columnId, $column)
    {
        if (is_array($column)) {
            $this->_columns[$columnId] = $this->getLayout()->createBlock('adminhtml/widget_grid_column')
                ->setData($column)
                ->setGrid($this);
        }
        /*elseif ($column instanceof Varien_Object) {
            $this->_columns[$columnId] = $column;
        }*/
        else {
            throw new Exception(Mage::helper('adminhtml')->__('Wrong column format'));
        }

        $this->_columns[$columnId]->setId($columnId);
        return $this;
    }

    /**
     * Return columns list
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->_columns;
    }

    /**
     * Return column block
     *
     * @param string $columnId
     * @return Mage_Adminhtml_Block_Widget_Grid_Column
     */
    public function getColumn($columnId)
    {
        return isset($this->_columns[$columnId]) ? $this->_columns[$columnId] : null;
    }


    /**
     * Returns rendered row value for grid
     *
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @param Varien_Object $row
     * @return string
     */
    public function getRowValue($column, $row)
    {
   	if(is_array($row)) {
    	       $row = new Varien_Object($row);
    	}

    	return $column->getRowField($row);
    }

    /**
     * Add total row to grid
     *
     * @param string $columnId
     * @param string $labelText
     * @return Mage_Adminhtml_Block_Dashboard_Tab_Grid
     */
    public function addTotal($columnId, $labelText, $isAmouth=false, $round=false)
    {
    	$this->_totals[] = array('id'=>$columnId, 'label'=>$labelText, 'is_amouth'=>$isAmouth, 'round'=>$round);
    	return $this;
    }

    /**
     * Returns calculated totals for grid
     *
     * @return array
     */
    public function getTotals()
    {
        $result = array();
        foreach ($this->_totals as $total) {
            if($this->getColumn($total['id'])) {
                $index = $this->getColumn($total['id'])->getIndex();
                $value = $this->getColumnSum($index);
                if($total['is_amouth']) {
                    $value = $value / $this->getCount();
                }

                if($total['round']) {
                    $value = round($value, 0);
                }

                $item = new Varien_Object(array($index=>$value));
                $result[] = array('label'=>$total['label'], 'value'=>$this->getColumn($total['id'])->getRenderer()->render($item));
            }
        }

        return $result;
    }

    /**
     * Returns sum of column
     *
     * @param string $index
     * @return float
     */
    public function getColumnSum($index)
    {
        $sum = 0;
        $values = $this->getDataHelper()->getColumn($index);
        foreach ($values as $value) {
            $sum+= (float) $value;
        }

        return $sum;
    }

    /**
     * Return count of items in collection
     *
     * @return integer
     */
    public function getCount()
    {
        return sizeof($this->getDataHelper()->getItems());
    }

    /**
     * Returns auto increment for scrollbar
     *
     * @return integer
     */
    public function getScrollIndex()
    {
        return self::$_scrollIndex++;
    }

    /**
     * Returns template for grid block
     *
     * @return string
     */
    protected function  _getTabTemplate()
    {
    	return 'dashboard/tab/grid.phtml';
    }
 } // Class Mage_Adminhtml_Block_Dashboard_Tab_Grid end
