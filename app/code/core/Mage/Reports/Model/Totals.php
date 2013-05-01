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
 * @package    Mage_Reports
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *  Totals Class
 *
 * @category   Mage
 * @package    Mage_Reports
 */
class Mage_Reports_Model_Totals
{    
    public function countTotals($grid)
    {
        $columns = array();
        foreach ($grid->getColumns() as $col)
            $columns[$col->getIndex()] = array("total" => $col->getTotal(), "value" => 0);
                
        foreach ($grid->getCollection()->getItems() as $item)
        {        
            $data = $item->getData();
            foreach ($columns as $field=>$a)
                $columns[$field]['value'] += $data[$field];
        }
        $data = array();
        foreach ($columns as $field=>$a)
        {
            if ($a['total'] == 'avg')
            {
                $data[$field] = $a['value']/$grid->getCollection()->count();
            } else if ($a['total'] == 'sum')
                {
                    $data[$field] = $a['value'];
                } else if ($a['total'] != '') $data[$field] = $a['total'];
        }
        
        $totals = new Varien_Object();
        
        $totals->setData($data);
                                
        return $totals;
    }   
}
