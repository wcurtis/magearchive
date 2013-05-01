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
 * Tax class grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */

class Mage_Adminhtml_Block_Tax_Class_Grid_Default extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('class_name');
        $this->setDefaultDir('asc');
    }

    protected function _prepareCollection()
    {
        $classType = ( $this->getClassType() ) ? $this->getClassType() : 'CUSTOMER' ;

        $collection = Mage::getResourceModel('tax/class_collection')->setClassTypeFilter($classType);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $classType = ( $this->getClassType() ) ? $this->getClassType() : 'CUSTOMER';
        $this->setClassType($classType);

        $this->addColumn('class_name',
            array(
                'header'=>Mage::helper('tax')->__('Class Name'),
                'align' =>'left',
                'index' => 'class_name'
            )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return Mage::getUrl('*/tax_class/edit', array('classId' => $row->getClassId(), 'classType' => $this->getClassType()));
    }
}