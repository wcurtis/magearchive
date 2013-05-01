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
 * @category   design_default
 * @package    Mage
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
	
	$data = array();

	$orders['conType'] = 'graph';
	$orders['yCount'] = 8;
	$orders['yHeading'] = 'Number of orders';
	$orders['yType'] = 'num';
	$orders['xType'] = 'hrs';
	$orders['xHeading'] = 'Time';
	$orders['tId'] = 'tOrders';
		$orders['yValues'] = array('0' => '1200', '1' => '1000', '2' => '800', '3' => '600', '4' => '400', '5' => '200', '6' => '0');
		$orders['gValues'] = array('0' => '100', '1' => '200', '2' => '50', '3' => '150', '4' => '180', '5' => '250', '6' => '200', '7' => '40', '8' => '220', '9' => '100', '10' => '150', '11' => '250');
		$orders['xValues'] = array('0' => '00:00', '1' => '01:00', '2' => '02:00', '3' => '03:00', '4' => '04:00', '5' => '05:00', '6' => '06:00', '7' => '07:00', '8' => '08:00', '9' => '09:00', '10' => '10:00', '11' => '11:00');
	$data['orders'] = $orders;

	$income['conType'] = 'graph';
	$income['yHeading'] = 'Total Income';
	$income['yCount'] = 7;
	$income['yType'] = 'mon';
	$income['xType'] = 'hrs';
	$income['xHeading'] = 'Time';
	$income['tId'] = 'tIncome';
		$income['yValues'] = array('0' => '500', '1' => '400', '2' => '300', '3' => '200', '4' => '100', '5' => '0');
		$income['gValues'] = array('0' => '100', '1' => '200', '2' => '50', '3' => '150', '4' => '180', '5' => '210', '6' => '200', '7' => '40', '8' => '120', '9' => '100', '10' => '150', '11' => '290');
		$income['xValues'] = array('0' => '00:00', '1' => '01:00', '2' => '02:00', '3' => '03:00', '4' => '04:00', '5' => '05:00', '6' => '06:00', '7' => '07:00', '8' => '08:00', '9' => '09:00', '10' => '10:00', '11' => '11:00');
	$data['income'] = $income;

	$summary['conType'] = 'tab';
	$summary['yHeading'] = '';
	$summary['yCount'] = 7;
	$summary['yType'] = 'mon';
	$summary['xType'] = 'hrs';
	$summary['xHeading'] = '';
	$summary['tId'] = 'tSummary';
		$summary['yValues'] = array('0' => 'Date range', '1' => 'Total income', '2' => 'Number of orders');
		$summary['gValues'] = array(
									'0' => array('0' => '18.07.07', '1' => '$250.52', '2' => '12'), 
									'1' => array('0' => '19.07.07', '1' => '$378.34', '2' => '6'), 
									'2' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'3' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'4' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'5' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'6' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'7' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'8' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'9' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'10' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'11' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'12' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'13' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'14' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'15' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'16' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'17' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26'),
									'18' => array('0' => '20.07.07', '1' => '$1250.25', '2' => '26')
								);
		$summary['xValues'] = array('total' => 'Total Incomes', 'sum' => '$1879.11');
	$data['summary'] = $summary;
	
	$analytics['conType'] = 'graph';
	$analytics['yCount'] = 8;
	$analytics['yHeading'] = 'Site visits';
	$analytics['yType'] = 'num';
	$analytics['gType'] = '2';
	$analytics['xType'] = 'hrs';
	$analytics['xHeading'] = 'Time';
	$analytics['tId'] = 'tAnalytics';
		$analytics['yValues'] = array('0' => '1200', '1' => '1000', '2' => '800', '3' => '600', '4' => '400', '5' => '200', '6' => '0');
		$analytics['gValues'] = array('0' => array('0' => '325', '1' => '285'), '1' => array('0' => '120', '1' => '65'), '2' => array('0' => '110', '1' => '140'), '3' => array('0' => '120', '1' => '95'), '4' => array('0' => '220', '1' => '250'), '5' => array('0' => '250', '1' => '140'), '6' => array('0' => '140', '1' => '50'), '7' => array('0' => '77', '1' => '156'), '8' => array('0' => '211', '1' => '181'), '9' => array('0' => '35', '1' => '58'), '10' => array('0' => '141', '1' => '150'), '11' => array('0' => '122', '1' => '88'));
		$analytics['xValues'] = array('0' => '00:00', '1' => '01:00', '2' => '02:00', '3' => '03:00', '4' => '04:00', '5' => '05:00', '6' => '06:00', '7' => '07:00', '8' => '08:00', '9' => '09:00', '10' => '10:00', '11' => '11:00');
	$data['analytics'] = $analytics;
	
	$online['conType'] = 'tab';
	$online['yHeading'] = '';
	$online['yCount'] = 7;
	$online['yType'] = 'mon';
	$online['xType'] = 'hrs';
	$online['xHeading'] = '';
	$online['tId'] = 'tOnline';
		$online['yValues'] = array('0' => 'Number of visitors', '1' => 'Number of logged in users');
		$online['gValues'] = array(
								'0' => array('0' => '45', '1' => '15'),
								'1' => array('0' => '56', '1' => '18'),
								'2' => array('0' => '74', '1' => '45'),
								'3' => array('0' => '38', '1' => '21'),
								'4' => array('0' => '45', '1' => '15'),
								'5' => array('0' => '56', '1' => '18'),
								'6' => array('0' => '74', '1' => '45'),
								'7' => array('0' => '38', '1' => '21'),
								'8' => array('0' => '45', '1' => '15'),
								'9' => array('0' => '56', '1' => '18'),
								'10' => array('0' => '74', '1' => '45'),
								'11' => array('0' => '38', '1' => '21'),
								'12' => array('0' => '45', '1' => '15'),
								'13' => array('0' => '56', '1' => '18'),
								'14' => array('0' => '74', '1' => '45'),
								'15' => array('0' => '38', '1' => '21')
								);
		$online['xValues'] = array('total' => 'Total Customers', 'sum' => '589');
	$data['online'] = $online;
?>