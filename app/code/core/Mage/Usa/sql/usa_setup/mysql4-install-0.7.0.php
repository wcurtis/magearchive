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
 * @package    Mage_Usa
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$this->installModuleSystemDefaults();

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `usa_postcode`;

CREATE TABLE `usa_postcode` (
  `country_id` varchar(2) NOT NULL default 'US',
  `postcode` varchar(16) NOT NULL default '',
  `region_id` int(10) unsigned NOT NULL default '0',
  `county` varchar(50) NOT NULL default '',
  `city` varchar(50) NOT NULL default '',
  `postcode_class` char(1) NOT NULL default '',
  PRIMARY KEY  (`country_id`,`postcode`),
  KEY `country_id_2` (`country_id`,`region_id`),
  KEY `country_id_3` (`country_id`,`city`),
  KEY `country_id` (`country_id`),
  KEY `postcode` (`postcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

set_time_limit(240);

$fp = fopen($sqlFilesDir.'/us_zipcodes.txt', 'r');
while ($row = fgets($fp)) {
    $this->run("insert into `usa_postcode` (country_id, postcode, region_id, county, city, postcode_class) values ".$row);
}
fclose($fp);

$installer->endSetup();
