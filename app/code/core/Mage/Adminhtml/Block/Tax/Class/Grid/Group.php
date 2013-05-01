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

class Mage_Adminhtml_Block_Tax_Class_Grid_Group extends Mage_Adminhtml_Block_Widget_Grid
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
        $classId = $this->getRequest()->getParam('classId', null);
        $classType = $this->getRequest()->getParam('classType', null);

        if( isset($classId) ) {
            switch( $classType ) {
                case "CUSTOMER":
                    $collection = Mage::getModel('tax/class')->getCustomerGroupCollection();
                    break;

                /* FIXME!!! */
                case "PRODUCT":
                    $collection = Mage::getModel('tax/class')->getCustomerGroupCollection();
                    break;
            }
        }

        $collection->setTaxGroupFilter($classId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $classId = $this->getRequest()->getParam('classId');
        $classType = $this->getRequest()->getParam('classType');

        $actionsUrl = Mage::getUrl('*/tax_class/deleteGroup', array('classId'=>$classId, 'classType'=>$classType));

        if( isset($classId) ) {
            switch( $classType ) {
                case "CUSTOMER":
                    $index = 'customer_group_code';
                    $this->setGridHeader(Mage::helper('tax')->__('Included Customer Groups'));
                    break;

                /* FIXME!!! */
                case "PRODUCT":
                    $index = 'customer_group_code';
                    $this->setGridHeader(Mage::helper('tax')->__('Included Product Categories'));
                    break;
            }
        }

        $this->addColumn('class_name',
            array(
                'header'=>Mage::helper('tax')->__('Group Name'),
                'align' =>'left',
                'filter'    =>false,
                'index' => $index
            )
        );

       $this->addColumn('grid_actions',
            array(
                'header'=>Mage::helper('tax')->__('Actions'),
                'width'=>5,
                'sortable'=>false,
                'filter'    =>false,
                'type' => 'action',
                'actions'   => array(
                                    array(
                                        'url' => $actionsUrl .'groupId/$group_id/' . $classType,
                                        'caption' => Mage::helper('tax')->__('Delete'),
                                        'confirm' => Mage::helper('tax')->__('Are you sure you want to take this action?')
                                    )
                                )
            )
        );

        $this->setFilterVisibility(false);

        return parent::_prepareColumns();
    }
}
