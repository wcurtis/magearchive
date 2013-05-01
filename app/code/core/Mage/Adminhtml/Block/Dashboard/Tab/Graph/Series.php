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
 * Admihtml dashboard graph series block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
 class Mage_Adminhtml_Block_Dashboard_Tab_Graph_Series extends Mage_Adminhtml_Block_Abstract
 {
    protected $_xField = 'x';
    protected $_yField = 'y';

    public function setXField($field)
    {
        $this->_xField = $field;
        return $this;
    }

    public function setYField($field)
    {
        $this->_yField = $field;
        return $this;
    }

    public function getXField()
    {
        return $this->_xField;
    }

    public function getYField()
    {
        return $this->_yField;
    }

    public function getValue($item, $axis)
    {
        if ($axis->getDirection() == $axis->getHorizontalDirectionConstant()) {
            $field = $this->getXField();
        } else {
            $field  = $this->getYField();
        }

        if ($item instanceof Varien_Object) {
            return $item->getData($field);
        } else if (is_array($item) && isset($item[$field]))  {
            return $item[$field];
        }

        return null;
    }

 }
