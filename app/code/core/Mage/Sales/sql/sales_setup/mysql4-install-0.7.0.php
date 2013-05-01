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
 * @package    Mage_Sales
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$this->installModuleSystemDefaults();

$installer = $this;
/* @var $installer Mage_Sales_Model_Entity_Setup */

$installer->startSetup();

$installer->run("

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*Table structure for table `sales_counter` */

DROP TABLE IF EXISTS `sales_counter`;

CREATE TABLE `sales_counter` (
  `counter_id` int(10) unsigned NOT NULL auto_increment,
  `store_id` int(10) unsigned NOT NULL default '0',
  `counter_type` varchar(50) NOT NULL default '',
  `counter_value` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`counter_id`),
  UNIQUE KEY `store_id` (`store_id`,`counter_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_counter` */

/*Table structure for table `sales_discount_coupon` */

DROP TABLE IF EXISTS `sales_discount_coupon`;

CREATE TABLE `sales_discount_coupon` (
  `coupon_id` int(10) unsigned NOT NULL auto_increment,
  `coupon_code` varchar(50) NOT NULL default '',
  `discount_percent` decimal(10,4) NOT NULL default '0.0000',
  `discount_fixed` decimal(10,4) NOT NULL default '0.0000',
  `is_active` tinyint(1) NOT NULL default '1',
  `from_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `to_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `min_subtotal` decimal(12,4) NOT NULL default '0.0000',
  `limit_products` text NOT NULL,
  `limit_categories` text NOT NULL,
  `limit_attributes` text NOT NULL,
  PRIMARY KEY  (`coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_discount_coupon` */

insert  into `sales_discount_coupon`(`coupon_id`,`coupon_code`,`discount_percent`,`discount_fixed`,`is_active`,`from_date`,`to_date`,`min_subtotal`,`limit_products`,`limit_categories`,`limit_attributes`) values (1,'test',10.0000,0.0000,1,'0000-00-00 00:00:00','0000-00-00 00:00:00',0.0000,'','','');

/*Table structure for table `sales_giftcert` */

DROP TABLE IF EXISTS `sales_giftcert`;

CREATE TABLE `sales_giftcert` (
  `giftcert_id` int(10) unsigned NOT NULL auto_increment,
  `giftcert_code` varchar(50) NOT NULL default '',
  `balance_amount` decimal(12,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`giftcert_id`),
  UNIQUE KEY `gift_code` (`giftcert_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_giftcert` */

insert  into `sales_giftcert`(`giftcert_id`,`giftcert_code`,`balance_amount`) values (1,'test',20.0000);

/*Table structure for table `sales_invoice_entity` */

DROP TABLE IF EXISTS `sales_invoice_entity`;

CREATE TABLE `sales_invoice_entity` (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_set_id` smallint(5) unsigned NOT NULL default '0',
  `increment_id` varchar(50) NOT NULL default '',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `is_active` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`entity_id`),
  KEY `FK_sales_invoice_entity_type` (`entity_type_id`),
  KEY `FK_sales_invoice_entity_store` (`store_id`),
  CONSTRAINT `FK_sales_invoice_entity_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_invoice_entity` */

/*Table structure for table `sales_invoice_entity_datetime` */

DROP TABLE IF EXISTS `sales_invoice_entity_datetime`;

CREATE TABLE `sales_invoice_entity_datetime` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_invoice_entity_datetime_entity_type` (`entity_type_id`),
  KEY `FK_sales_invoice_entity_datetime_attribute` (`attribute_id`),
  KEY `FK_sales_invoice_entity_datetime_store` (`store_id`),
  KEY `FK_sales_invoice_entity_datetime` (`entity_id`),
  CONSTRAINT `FK_sales_invoice_entity_datetime` FOREIGN KEY (`entity_id`) REFERENCES `sales_invoice_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_datetime_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_datetime_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_datetime_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_invoice_entity_datetime` */

/*Table structure for table `sales_invoice_entity_decimal` */

DROP TABLE IF EXISTS `sales_invoice_entity_decimal`;

CREATE TABLE `sales_invoice_entity_decimal` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` decimal(12,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_invoice_entity_decimal_entity_type` (`entity_type_id`),
  KEY `FK_sales_invoice_entity_decimal_attribute` (`attribute_id`),
  KEY `FK_sales_invoice_entity_decimal_store` (`store_id`),
  KEY `FK_sales_invoice_entity_decimal` (`entity_id`),
  CONSTRAINT `FK_sales_invoice_entity_decimal` FOREIGN KEY (`entity_id`) REFERENCES `sales_invoice_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_decimal_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_decimal_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_decimal_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_invoice_entity_decimal` */

/*Table structure for table `sales_invoice_entity_int` */

DROP TABLE IF EXISTS `sales_invoice_entity_int`;

CREATE TABLE `sales_invoice_entity_int` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_invoice_entity_int_entity_type` (`entity_type_id`),
  KEY `FK_sales_invoice_entity_int_attribute` (`attribute_id`),
  KEY `FK_sales_invoice_entity_int_store` (`store_id`),
  KEY `FK_sales_invoice_entity_int` (`entity_id`),
  CONSTRAINT `FK_sales_invoice_entity_int` FOREIGN KEY (`entity_id`) REFERENCES `sales_invoice_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_int_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_int_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_int_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_invoice_entity_int` */

/*Table structure for table `sales_invoice_entity_text` */

DROP TABLE IF EXISTS `sales_invoice_entity_text`;

CREATE TABLE `sales_invoice_entity_text` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_invoice_entity_text_entity_type` (`entity_type_id`),
  KEY `FK_sales_invoice_entity_text_attribute` (`attribute_id`),
  KEY `FK_sales_invoice_entity_text_store` (`store_id`),
  KEY `FK_sales_invoice_entity_text` (`entity_id`),
  CONSTRAINT `FK_sales_invoice_entity_text` FOREIGN KEY (`entity_id`) REFERENCES `sales_invoice_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_text_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_text_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_text_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_invoice_entity_text` */

/*Table structure for table `sales_invoice_entity_varchar` */

DROP TABLE IF EXISTS `sales_invoice_entity_varchar`;

CREATE TABLE `sales_invoice_entity_varchar` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_invoice_entity_varchar_entity_type` (`entity_type_id`),
  KEY `FK_sales_invoice_entity_varchar_attribute` (`attribute_id`),
  KEY `FK_sales_invoice_entity_varchar_store` (`store_id`),
  KEY `FK_sales_invoice_entity_varchar` (`entity_id`),
  CONSTRAINT `FK_sales_invoice_entity_varchar` FOREIGN KEY (`entity_id`) REFERENCES `sales_invoice_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_varchar_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_varchar_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_invoice_entity_varchar_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_invoice_entity_varchar` */

/*Table structure for table `sales_order_entity` */

DROP TABLE IF EXISTS `sales_order_entity`;

CREATE TABLE `sales_order_entity` (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_set_id` smallint(5) unsigned NOT NULL default '0',
  `increment_id` varchar(50) NOT NULL default '',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `is_active` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`entity_id`),
  KEY `FK_sales_order_entity_type` (`entity_type_id`),
  KEY `FK_sales_order_entity_store` (`store_id`),
  CONSTRAINT `FK_sales_order_entity_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/*Data for the table `sales_order_entity` */

/*Table structure for table `sales_order_entity_datetime` */

DROP TABLE IF EXISTS `sales_order_entity_datetime`;

CREATE TABLE `sales_order_entity_datetime` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_order_entity_datetime_entity_type` (`entity_type_id`),
  KEY `FK_sales_order_entity_datetime_attribute` (`attribute_id`),
  KEY `FK_sales_order_entity_datetime_store` (`store_id`),
  KEY `FK_sales_order_entity_datetime` (`entity_id`),
  CONSTRAINT `FK_sales_order_entity_datetime` FOREIGN KEY (`entity_id`) REFERENCES `sales_order_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_datetime_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_datetime_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_datetime_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_order_entity_datetime` */

/*Table structure for table `sales_order_entity_decimal` */

DROP TABLE IF EXISTS `sales_order_entity_decimal`;

CREATE TABLE `sales_order_entity_decimal` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` decimal(12,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_order_entity_decimal_entity_type` (`entity_type_id`),
  KEY `FK_sales_order_entity_decimal_attribute` (`attribute_id`),
  KEY `FK_sales_order_entity_decimal_store` (`store_id`),
  KEY `FK_sales_order_entity_decimal` (`entity_id`),
  CONSTRAINT `FK_sales_order_entity_decimal` FOREIGN KEY (`entity_id`) REFERENCES `sales_order_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_decimal_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_decimal_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_decimal_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_order_entity_decimal` */

/*Table structure for table `sales_order_entity_int` */

DROP TABLE IF EXISTS `sales_order_entity_int`;

CREATE TABLE `sales_order_entity_int` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_order_entity_int_entity_type` (`entity_type_id`),
  KEY `FK_sales_order_entity_int_attribute` (`attribute_id`),
  KEY `FK_sales_order_entity_int_store` (`store_id`),
  KEY `FK_sales_order_entity_int` (`entity_id`),
  CONSTRAINT `FK_sales_order_entity_int` FOREIGN KEY (`entity_id`) REFERENCES `sales_order_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_int_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_int_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_int_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_order_entity_int` */

/*Table structure for table `sales_order_entity_text` */

DROP TABLE IF EXISTS `sales_order_entity_text`;

CREATE TABLE `sales_order_entity_text` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_order_entity_text_entity_type` (`entity_type_id`),
  KEY `FK_sales_order_entity_text_attribute` (`attribute_id`),
  KEY `FK_sales_order_entity_text_store` (`store_id`),
  KEY `FK_sales_order_entity_text` (`entity_id`),
  CONSTRAINT `FK_sales_order_entity_text` FOREIGN KEY (`entity_id`) REFERENCES `sales_order_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_text_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_text_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_text_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_order_entity_text` */

/*Table structure for table `sales_order_entity_varchar` */

DROP TABLE IF EXISTS `sales_order_entity_varchar`;

CREATE TABLE `sales_order_entity_varchar` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_order_entity_varchar_entity_type` (`entity_type_id`),
  KEY `FK_sales_order_entity_varchar_attribute` (`attribute_id`),
  KEY `FK_sales_order_entity_varchar_store` (`store_id`),
  KEY `FK_sales_order_entity_varchar` (`entity_id`),
  CONSTRAINT `FK_sales_order_entity_varchar` FOREIGN KEY (`entity_id`) REFERENCES `sales_order_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_varchar_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_varchar_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_order_entity_varchar_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_order_entity_varchar` */

/*Table structure for table `sales_quote_entity` */

DROP TABLE IF EXISTS `sales_quote_entity`;

CREATE TABLE `sales_quote_entity` (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_set_id` smallint(5) unsigned NOT NULL default '0',
  `increment_id` varchar(50) NOT NULL default '',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `is_active` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`entity_id`),
  KEY `FK_sales_quote_entity_type` (`entity_type_id`),
  KEY `FK_sales_quote_entity_store` (`store_id`),
  CONSTRAINT `FK_sales_quote_entity_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/*Table structure for table `sales_quote_entity_datetime` */

DROP TABLE IF EXISTS `sales_quote_entity_datetime`;

CREATE TABLE `sales_quote_entity_datetime` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_quote_entity_datetime_entity_type` (`entity_type_id`),
  KEY `FK_sales_quote_entity_datetime_attribute` (`attribute_id`),
  KEY `FK_sales_quote_entity_datetime_store` (`store_id`),
  KEY `FK_sales_quote_entity_datetime` (`entity_id`),
  CONSTRAINT `FK_sales_quote_entity_datetime` FOREIGN KEY (`entity_id`) REFERENCES `sales_quote_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_datetime_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_datetime_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_datetime_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_quote_entity_datetime` */

/*Table structure for table `sales_quote_entity_decimal` */

DROP TABLE IF EXISTS `sales_quote_entity_decimal`;

CREATE TABLE `sales_quote_entity_decimal` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` decimal(12,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_quote_entity_decimal_entity_type` (`entity_type_id`),
  KEY `FK_sales_quote_entity_decimal_attribute` (`attribute_id`),
  KEY `FK_sales_quote_entity_decimal_store` (`store_id`),
  KEY `FK_sales_quote_entity_decimal` (`entity_id`),
  CONSTRAINT `FK_sales_quote_entity_decimal` FOREIGN KEY (`entity_id`) REFERENCES `sales_quote_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_decimal_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_decimal_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_decimal_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*Table structure for table `sales_quote_entity_int` */

DROP TABLE IF EXISTS `sales_quote_entity_int`;

CREATE TABLE `sales_quote_entity_int` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_quote_entity_int_entity_type` (`entity_type_id`),
  KEY `FK_sales_quote_entity_int_attribute` (`attribute_id`),
  KEY `FK_sales_quote_entity_int_store` (`store_id`),
  KEY `FK_sales_quote_entity_int` (`entity_id`),
  CONSTRAINT `FK_sales_quote_entity_int` FOREIGN KEY (`entity_id`) REFERENCES `sales_quote_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_int_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_int_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_int_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*Table structure for table `sales_quote_entity_text` */

DROP TABLE IF EXISTS `sales_quote_entity_text`;

CREATE TABLE `sales_quote_entity_text` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_quote_entity_text_entity_type` (`entity_type_id`),
  KEY `FK_sales_quote_entity_text_attribute` (`attribute_id`),
  KEY `FK_sales_quote_entity_text_store` (`store_id`),
  KEY `FK_sales_quote_entity_text` (`entity_id`),
  CONSTRAINT `FK_sales_quote_entity_text` FOREIGN KEY (`entity_id`) REFERENCES `sales_quote_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_text_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_text_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_text_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_quote_entity_text` */

/*Table structure for table `sales_quote_entity_varchar` */

DROP TABLE IF EXISTS `sales_quote_entity_varchar`;

CREATE TABLE `sales_quote_entity_varchar` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_quote_entity_varchar_entity_type` (`entity_type_id`),
  KEY `FK_sales_quote_entity_varchar_attribute` (`attribute_id`),
  KEY `FK_sales_quote_entity_varchar_store` (`store_id`),
  KEY `FK_sales_quote_entity_varchar` (`entity_id`),
  CONSTRAINT `FK_sales_quote_entity_varchar` FOREIGN KEY (`entity_id`) REFERENCES `sales_quote_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_varchar_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_varchar_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_entity_varchar_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*Table structure for table `sales_quote_rule` */

DROP TABLE IF EXISTS `sales_quote_rule`;

CREATE TABLE `sales_quote_rule` (
  `quote_rule_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `is_active` tinyint(4) NOT NULL default '0',
  `start_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `expire_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `coupon_code` varchar(50) NOT NULL default '',
  `customer_registered` tinyint(1) NOT NULL default '2',
  `customer_new_buyer` tinyint(1) NOT NULL default '2',
  `show_in_catalog` tinyint(1) NOT NULL default '0',
  `sort_order` smallint(6) NOT NULL default '0',
  `conditions_serialized` text NOT NULL,
  `actions_serialized` text NOT NULL,
  PRIMARY KEY  (`quote_rule_id`),
  KEY `rule_name` (`name`),
  KEY `is_active` (`is_active`,`start_at`,`expire_at`,`coupon_code`,`customer_registered`,`customer_new_buyer`,`show_in_catalog`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_quote_rule` */

/*Table structure for table `sales_quote_temp` */

DROP TABLE IF EXISTS `sales_quote_temp`;

CREATE TABLE `sales_quote_temp` (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_set_id` smallint(5) unsigned NOT NULL default '0',
  `increment_id` varchar(50) NOT NULL default '',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `is_active` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`entity_id`),
  KEY `FK_sales_quote_temp_type` (`entity_type_id`),
  KEY `FK_sales_quote_temp_store` (`store_id`),
  CONSTRAINT `FK_sales_quote_temp_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/*Data for the table `sales_quote_temp` */

/*Table structure for table `sales_quote_temp_datetime` */

DROP TABLE IF EXISTS `sales_quote_temp_datetime`;

CREATE TABLE `sales_quote_temp_datetime` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_quote_temp_datetime_entity_type` (`entity_type_id`),
  KEY `FK_sales_quote_temp_datetime_attribute` (`attribute_id`),
  KEY `FK_sales_quote_temp_datetime_store` (`store_id`),
  KEY `FK_sales_quote_temp_datetime` (`entity_id`),
  CONSTRAINT `FK_sales_quote_temp_datetime` FOREIGN KEY (`entity_id`) REFERENCES `sales_quote_temp` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_datetime_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_datetime_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_datetime_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_quote_temp_datetime` */

/*Table structure for table `sales_quote_temp_decimal` */

DROP TABLE IF EXISTS `sales_quote_temp_decimal`;

CREATE TABLE `sales_quote_temp_decimal` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` decimal(12,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_quote_temp_decimal_entity_type` (`entity_type_id`),
  KEY `FK_sales_quote_temp_decimal_attribute` (`attribute_id`),
  KEY `FK_sales_quote_temp_decimal_store` (`store_id`),
  KEY `FK_sales_quote_temp_decimal` (`entity_id`),
  CONSTRAINT `FK_sales_quote_temp_decimal` FOREIGN KEY (`entity_id`) REFERENCES `sales_quote_temp` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_decimal_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_decimal_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_decimal_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_quote_temp_decimal` */

/*Table structure for table `sales_quote_temp_int` */

DROP TABLE IF EXISTS `sales_quote_temp_int`;

CREATE TABLE `sales_quote_temp_int` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_quote_temp_int_entity_type` (`entity_type_id`),
  KEY `FK_sales_quote_temp_int_attribute` (`attribute_id`),
  KEY `FK_sales_quote_temp_int_store` (`store_id`),
  KEY `FK_sales_quote_temp_int` (`entity_id`),
  CONSTRAINT `FK_sales_quote_temp_int` FOREIGN KEY (`entity_id`) REFERENCES `sales_quote_temp` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_int_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_int_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_int_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_quote_temp_int` */

/*Table structure for table `sales_quote_temp_text` */

DROP TABLE IF EXISTS `sales_quote_temp_text`;

CREATE TABLE `sales_quote_temp_text` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_quote_temp_text_entity_type` (`entity_type_id`),
  KEY `FK_sales_quote_temp_text_attribute` (`attribute_id`),
  KEY `FK_sales_quote_temp_text_store` (`store_id`),
  KEY `FK_sales_quote_temp_text` (`entity_id`),
  CONSTRAINT `FK_sales_quote_temp_text` FOREIGN KEY (`entity_id`) REFERENCES `sales_quote_temp` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_text_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_text_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_text_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_quote_temp_text` */

/*Table structure for table `sales_quote_temp_varchar` */

DROP TABLE IF EXISTS `sales_quote_temp_varchar`;

CREATE TABLE `sales_quote_temp_varchar` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`value_id`),
  KEY `FK_sales_quote_temp_varchar_entity_type` (`entity_type_id`),
  KEY `FK_sales_quote_temp_varchar_attribute` (`attribute_id`),
  KEY `FK_sales_quote_temp_varchar_store` (`store_id`),
  KEY `FK_sales_quote_temp_varchar` (`entity_id`),
  CONSTRAINT `FK_sales_quote_temp_varchar` FOREIGN KEY (`entity_id`) REFERENCES `sales_quote_temp` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_varchar_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_varchar_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_sales_quote_temp_varchar_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `sales_quote_temp_varchar` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;




UPDATE core_email_template set template_text='<style type=\"text/css\">\r\nbody,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }\r\n</style>\r\n\r\n<div style=\"font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;\">\r\n            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"98%\" style=\"margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;\"\">\r\n             <tr>\r\n                    <td align=\"center\" valign=\"top\">\r\n                    <!-- [ header starts here] -->\r\n                      <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n                          <tr>\r\n                                <td valign=\"top\">\r\n                                 <a href=\"{{store url=\"\"}}\"><img src=\"{{skin url=\"images/logo_email.gif\"}}\" alt=\"Magento\"  style=\"margin-bottom:10px;\" border=\"0\"/></a></td>\r\n                           </tr>\r\n                       </table>\r\n\r\n                    <!-- [ middle starts here] -->\r\n                      <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n                          <tr>\r\n                                <td valign=\"top\">\r\n                             <p><strong>Hello {{var billing.name}}</strong>,<br/>\r\n                                Thank you for your order from Magento Demo Store. Once your package ships we will send an email with a link to track your order. You can check the status of your order by <a href=\"{{store url=\"customer/account/\"}}\" style=\"color:#1E7EC8;\">logging into your account</a>. If you have any questions about your order please contact us at <a href=\"mailto:dummyemail@magentocommerce.com\" style=\"color:#1E7EC8;\">dummyemail@magentocommerce.com</a> or call us at <nobr>(800) DEMO-NUMBER</nobr> Monday - Friday, 8am - 5pm PST.</p>\r\n <p>Your order confirmation is below. Thank you again for your business.</p>\r\n                               \r\n                                <h3 style=\"border-bottom:2px solid #eee; font-size:1.05em; padding-bottom:1px; \">Your Order #{{var order.increment_id}} <small>(placed on {{var order.getCreatedAtFormated(\'long\')}})</small></h3>\r\n                              <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\r\n                                 <thead>\r\n                                 <tr>\r\n                                        <th align=\"left\" width=\"48.5%\" bgcolor=\"#d9e5ee\" style=\"padding:5px 9px 6px 9px; border:1px solid #bebcb7; border-bottom:none; line-height:1em;\">Billing \r\n                                       Information:</th>\r\n                                       <th width=\"3%\"></th>\r\n                                      <th align=\"left\" width=\"48.5%\" bgcolor=\"#d9e5ee\" style=\"padding:5px 9px 6px 9px; border:1px solid #bebcb7; border-bottom:none; line-height:1em;\">Payment \r\n                                       Method:</th>\r\n                                    </tr>\r\n                                   </thead>\r\n                                    <tbody>\r\n                                 <tr>\r\n                                        <td valign=\"top\" style=\"padding:7px 9px 9px 9px; border:1px solid #bebcb7; border-top:0; background:#f8f7f5;\">{{var order.billing_address.getFormated(\'html\')}}</td>\r\n                                      <td>&nbsp;</td>\r\n                                     <td valign=\"top\" style=\"padding:7px 9px 9px 9px; border:1px solid #bebcb7; border-top:0; background:#f8f7f5;\"> {{var payment_html}}</td>\r\n                                 </tr>\r\n                                   </tbody>\r\n                                </table><br/>\r\n                                               <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">\r\n                                 <thead>\r\n                                 <tr>\r\n                                        <th align=\"left\" width=\"48.5%\" bgcolor=\"#d9e5ee\" style=\"padding:5px 9px 6px 9px; border:1px solid #bebcb7; border-bottom:none; line-height:1em;\">Shipping \r\n                                      Information:</th>\r\n                                       <th width=\"3%\"></th>\r\n                                      <th align=\"left\" width=\"48.5%\" bgcolor=\"#d9e5ee\" style=\"padding:5px 9px 6px 9px; border:1px solid #bebcb7; border-bottom:none; line-height:1em;\">Shipping \r\n                                      Method:</th>\r\n                                    </tr>\r\n                                   </thead>\r\n                                    <tbody>\r\n                                 <tr>\r\n                                        <td valign=\"top\" style=\"padding:7px 9px 9px 9px; border:1px solid #bebcb7; border-top:0; background:#f8f7f5;\">{{var order.shipping_address.getFormated(\'html\')}}</td>\r\n                                     <td>&nbsp;</td>\r\n                                     <td valign=\"top\" style=\"padding:7px 9px 9px 9px; border:1px solid #bebcb7; border-top:0; background:#f8f7f5;\">{{var order.shipping_description}}</td>\r\n                                   </tr>\r\n                                   </tbody>\r\n                                </table><br/>\r\n\r\n{{var items_html}}<br/>\r\n      {{var order.getEmailCustomerNote()}}                          \r\n                                <p>Thank you again,<br/><strong>Magento Demo Store</strong></p>\r\n\r\n\r\n                             </td>\r\n                           </tr>\r\n                       </table>\r\n                    \r\n                    </td>\r\n               </tr>\r\n           </table>\r\n            </div>\r\n' WHERE template_code='New order (HTML)';
UPDATE core_email_template set template_text='<style type=\"text/css\">body,td { color:#2f2f2f; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; }</style>\r\n<div style=\"font:11px/1.35em Verdana, Arial, Helvetica, sans-serif;\">\r\n<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"98%\" style=\"margin-top:10px; font:11px/1.35em Verdana, Arial, Helvetica, sans-serif; margin-bottom:10px;\"\">\r\n <tr>\r\n   <td align=\"center\" valign=\"top\">\r\n     <!-- [ header starts here] -->\r\n     <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n      <tr>\r\n       <td valign=\"top\">\r\n        <a href=\"{{store url=\"\"}}\"><img src=\"{{skin url=\"images/logo_email.gif\"}}\" alt=\"Magento\"  style=\"margin-bottom:10px;\" border=\"0\"/></a></td>\r\n      </tr>\r\n     </table>\r\n\r\n     <!-- [ middle starts here] -->\r\n     <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"650\">\r\n       <tr>\r\n         <td valign=\"top\">\r\n           <p><strong>Dear {{var billing.name}}</strong>,<br/>\r\n            Your order # {{var order.increment_id}} has been <strong>{{var order.getStatusLabel()}}</strong>.</p>\r\n           <p>{{var comment}}</p>\r\n           <p>If you have any questions, please feel free to contact us at \r\n           <a href=\"mailto:magento@varien.com\" style=\"color:#1E7EC8;\">dummyemail@magentocommerce.com</a> or by phone at (800) DEMO-STORE.</p>\r\n           <p>Thank you again,<br/><strong>Magento Demo Store</strong></p>\r\n      </td>\r\n     </tr>\r\n    </table>\r\n   </td>\r\n  </tr>\r\n </table>\r\n</div>\r\n' WHERE template_code='Order update (HTML)';
    ");

$installer->installEntities();

$installer->endSetup();
