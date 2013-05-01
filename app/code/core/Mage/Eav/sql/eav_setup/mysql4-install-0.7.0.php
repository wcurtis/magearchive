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
 * @package    Mage_Eav
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;

$installer->startSetup();

$installer->run("

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*Table structure for table `eav_attribute` */

DROP TABLE IF EXISTS `eav_attribute`;

CREATE TABLE `eav_attribute` (
  `attribute_id` smallint(5) unsigned NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_code` varchar(255) NOT NULL default '',
  `attribute_model` varchar(255) default NULL,
  `backend_model` varchar(255) default NULL,
  `backend_type` enum('static','datetime','decimal','int','text','varchar') NOT NULL default 'static',
  `backend_table` varchar(255) default NULL,
  `frontend_model` varchar(255) default NULL,
  `frontend_input` varchar(50) default NULL,
  `frontend_label` varchar(255) default NULL,
  `frontend_class` varchar(255) default NULL,
  `source_model` varchar(255) default NULL,
  `is_global` tinyint(1) unsigned NOT NULL default '1',
  `is_visible` tinyint(1) unsigned NOT NULL default '1',
  `is_required` tinyint(1) unsigned NOT NULL default '0',
  `is_user_defined` tinyint(1) unsigned NOT NULL default '0',
  `default_value` text,
  `is_searchable` tinyint(1) unsigned NOT NULL default '0',
  `is_filterable` tinyint(1) unsigned NOT NULL default '0',
  `is_comparable` tinyint(1) unsigned NOT NULL default '0',
  `is_visible_on_front` tinyint(1) unsigned NOT NULL default '0',
  `is_unique` tinyint(1) unsigned NOT NULL default '0',
  `apply_to` tinyint(3) unsigned NOT NULL default '0',
  `use_in_super_product` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`attribute_id`),
  UNIQUE KEY `entity_type_id` (`entity_type_id`,`attribute_code`),
  CONSTRAINT `FK_eav_attribute` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `eav_attribute` */

insert  into `eav_attribute`(`attribute_id`,`entity_type_id`,`attribute_code`,`attribute_model`,`backend_model`,`backend_type`,`backend_table`,`frontend_model`,`frontend_input`,`frontend_label`,`frontend_class`,`source_model`,`is_global`,`is_visible`,`is_required`,`is_user_defined`,`default_value`,`is_searchable`,`is_filterable`,`is_comparable`,`is_visible_on_front`,`is_unique`,`apply_to`,`use_in_super_product`) values (1,1,'firstname',NULL,'','varchar','','','text','First Name','','',1,1,1,0,'',0,0,0,0,0,0,1),(2,1,'lastname',NULL,'','varchar','','','text','Last Name','','',1,1,1,0,'',0,0,0,0,0,0,1),(3,1,'email',NULL,'','varchar','','','text','Email','validate-email','',1,1,1,0,'',0,0,0,0,0,0,1),(4,1,'password_hash',NULL,'customer_entity/customer_attribute_backend_password','varchar','','','hidden','','','',1,1,0,0,'',0,0,0,0,0,0,1),(5,1,'customer_group',NULL,'','int','','','select','Customer Group','','customer_entity/customer_attribute_source_group',1,1,1,0,'',0,0,0,0,0,0,1),(6,1,'store_balance',NULL,'','decimal','','','hidden','Balance','validate-number','',1,1,1,0,'',0,0,0,0,0,0,1),(7,1,'default_billing',NULL,'customer_entity/customer_attribute_backend_billing','int','','','text','','','',1,0,0,0,'',0,0,0,0,0,0,1),(8,1,'default_shipping',NULL,'customer_entity/customer_attribute_backend_shipping','int','','','text','','','',1,0,0,0,'',0,0,0,0,0,0,1),(9,2,'firstname',NULL,'','varchar','','','text','First Name','','',1,1,1,0,'',0,0,0,0,0,0,1),(10,2,'lastname',NULL,'','varchar','','','text','Last Name','','',1,1,1,0,'',0,0,0,0,0,0,1),(11,2,'country_id',NULL,'','varchar','','','select','Country','countries input-text','customer_entity/address_attribute_source_country',1,1,1,0,'',0,0,0,0,0,0,1),(12,2,'region',NULL,'customer_entity/address_attribute_backend_region','varchar','','','text','State/Province','regions','',1,1,1,0,'',0,0,0,0,0,0,1),(13,2,'region_id',NULL,'','int','','','hidden','','','customer_entity/address_attribute_source_region',1,1,0,0,'',0,0,0,0,0,0,1),(14,2,'postcode',NULL,'','varchar','','','text','Zip/Post Code','','',1,1,1,0,'',0,0,0,0,0,0,1),(15,2,'city',NULL,'','varchar','','','text','City','','',1,1,1,0,'',0,0,0,0,0,0,1),(16,2,'street',NULL,'customer_entity/address_attribute_backend_street','text','','','textarea','Street Address','','',1,1,1,0,'',0,0,0,0,0,0,1),(17,2,'telephone',NULL,'','varchar','','','text','Telephone','','',1,1,1,0,'',0,0,0,0,0,0,1),(18,2,'fax',NULL,'','varchar','','','text','Fax','','',1,1,0,0,'',0,0,0,0,0,0,1),(19,3,'method_type',NULL,'','int','','','select','Payment Method','','',1,1,1,0,'',0,0,0,0,0,0,1),(95,2,'company',NULL,'','varchar','','','text','Company',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(96,10,'name',NULL,'','varchar','','','text','Name',NULL,'',0,1,1,0,'',1,0,0,0,0,0,1),(97,10,'description',NULL,'','text','','','textarea','Description','','',1,1,1,0,'',1,0,0,0,0,0,1),(98,10,'sku',NULL,'','varchar','','','text','SKU',NULL,'',1,1,1,0,'',1,0,1,0,1,0,1),(99,10,'price',NULL,'','decimal','','','price','Price',NULL,'',1,1,1,0,'',1,0,0,0,0,0,1),(100,10,'cost',NULL,'','decimal','','','price','Cost<br/>(For internal use)',NULL,'',1,1,0,1,'',0,0,0,0,0,0,1),(101,10,'weight',NULL,'','decimal','','','text','Weight','','',1,1,0,0,'',0,0,0,0,0,0,1),(102,10,'manufacturer',NULL,'','varchar','','','select','Manufacturer','','eav/entity_attribute_source_table',1,1,0,1,'',1,1,1,0,0,0,1),(103,10,'meta_title',NULL,'','varchar','','','text','Page Title',NULL,'',0,1,0,0,'',0,0,0,0,0,0,1),(104,10,'meta_keyword',NULL,'','text','','','textarea','Meta Keywords',NULL,'',0,1,0,0,'',0,0,0,0,0,0,1),(105,10,'meta_description',NULL,'','varchar','','','textarea','Meta Description',NULL,'',0,1,0,0,'',0,0,0,0,0,0,1),(106,10,'image',NULL,'catalog_entity/product_attribute_backend_image','varchar','','catalog_entity/product_attribute_frontend_image','image','Main Image','','',1,1,0,0,'',0,0,0,0,0,0,1),(109,10,'small_image',NULL,'catalog_entity/product_attribute_backend_image','varchar','','catalog_entity/product_attribute_frontend_image','image','Small Image','','',1,1,1,0,'',0,0,0,0,0,0,1),(110,10,'old_id',NULL,'','int','','','','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(111,9,'name',NULL,'','varchar','','','text','Name',NULL,'',0,1,1,0,'',0,0,0,0,0,0,1),(112,9,'description',NULL,'','text','','','textarea','Description',NULL,'',0,1,0,0,'',0,0,0,0,0,0,1),(113,9,'image',NULL,'catalog_entity/category_attribute_backend_image','varchar','','catalog_entity/category_attribute_frontend_image','image','Image',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(114,9,'meta_title',NULL,'','varchar','','','text','Page Title',NULL,'',0,1,0,0,'',0,0,0,0,0,0,1),(115,9,'meta_keywords',NULL,'','text','','','textarea','Meta Keywords',NULL,'',0,1,0,0,'',0,0,0,0,0,0,1),(116,9,'meta_description',NULL,'','text','','','textarea','Meta Description',NULL,'',0,1,0,0,'',0,0,0,0,0,0,1),(117,9,'landing_page',NULL,'','int','','','select','Landing Page',NULL,'catalog_entity/category_attribute_source_page',1,1,0,0,'',0,0,0,0,0,0,1),(118,9,'display_mode',NULL,'','varchar','','','select','Display Mode',NULL,'catalog_entity/category_attribute_source_mode',1,1,0,0,'',0,0,0,0,0,0,1),(119,9,'is_active',NULL,'','static','','','select','Is Active',NULL,'eav/entity_attribute_source_boolean',1,1,0,0,'',0,0,0,0,0,0,1),(120,9,'is_anchor',NULL,'','int','','','select','Is Anchor',NULL,'eav/entity_attribute_source_boolean',1,1,0,0,'',0,0,0,0,0,0,1),(121,9,'all_children',NULL,'catalog_entity/category_attribute_backend_tree_children','text','','','','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(122,9,'path_in_store',NULL,'catalog_entity/category_attribute_backend_tree_path','text','','','','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(123,9,'children',NULL,'catalog_entity/category_attribute_backend_tree_children','text','','','','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(194,4,'grand_total',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(195,4,'currency_rate',NULL,NULL,'decimal',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(196,4,'weight',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(197,4,'tax_percent',NULL,NULL,'decimal',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(198,4,'subtotal',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(199,4,'discount_amount',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(200,4,'tax_amount',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(201,4,'shipping_amount',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(202,4,'giftcert_amount',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(203,4,'custbalance_amount',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(204,4,'quote_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(205,4,'customer_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(207,4,'currency_base_id',NULL,NULL,'int',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(208,4,'shipping_description',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(209,4,'real_order_id',NULL,NULL,'varchar',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(210,4,'remote_ip',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(211,4,'currency_code',NULL,NULL,'varchar',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(212,4,'coupon_code',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(213,4,'giftcert_code',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(214,4,'shipping_method',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(215,4,'status',NULL,NULL,'varchar',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(216,4,'shipping_address_id',NULL,'sales_entity/order_attribute_backend_shipping','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(217,4,'billing_address_id',NULL,'sales_entity/order_attribute_backend_billing','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(218,6,'region_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(219,6,'country_id',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(220,6,'address_id',NULL,NULL,'int',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(221,6,'customer_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(222,6,'street',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(223,6,'email',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(224,6,'firstname',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(225,6,'lastname',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(226,6,'company',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(227,6,'city',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(228,6,'region',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(229,6,'postcode',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(230,6,'telephone',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(231,6,'fax',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(232,6,'tax_id',NULL,NULL,'varchar',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(233,6,'address_type',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(234,7,'weight',NULL,NULL,'decimal',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(235,7,'qty',NULL,NULL,'decimal',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(236,7,'qty_backordered',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(237,7,'qty_canceled',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(238,7,'qty_shipped',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(239,7,'qty_returned',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(240,7,'price',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(241,7,'tier_price',NULL,NULL,'decimal',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(242,7,'cost',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(243,7,'discount_percent',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(244,7,'discount_amount',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(245,7,'tax_percent',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(246,7,'tax_amount',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(247,7,'row_total',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(248,7,'row_weight',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(249,7,'product_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(250,7,'image',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(251,7,'name',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(252,7,'model',NULL,NULL,'varchar',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(253,8,'cc_exp_month',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(254,8,'cc_exp_year',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(255,8,'cc_raw_request',NULL,NULL,'text',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(256,8,'cc_raw_response',NULL,NULL,'text',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(257,8,'method',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(258,8,'po_number',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(259,8,'cc_type',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(260,8,'cc_number_enc',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(261,8,'cc_last4',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(262,8,'cc_owner',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(263,8,'cc_trans_id',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(264,8,'cc_approval',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(265,8,'cc_avs_status',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(266,8,'cc_cid_status',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(267,5,'status',NULL,NULL,'varchar',NULL,NULL,NULL,NULL,NULL,NULL,1,1,1,0,NULL,0,0,0,0,0,0,1),(268,5,'comments',NULL,NULL,'text',NULL,NULL,NULL,NULL,NULL,NULL,1,1,0,0,NULL,0,0,0,0,0,0,1),(269,10,'qty',NULL,'','int','','','text','Qty',NULL,'',1,1,1,0,'',0,0,0,0,0,0,0),(270,10,'tier_price',NULL,'catalog/entity_product_attribute_backend_tierprice','decimal','','catalog/entity_product_attribute_frontend_tierprice','text','Tier Price',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(271,10,'gallery',NULL,'catalog_entity/product_attribute_backend_gallery','varchar','catalog_product_entity_gallery','','gallery','Image Gallery',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(272,10,'color',NULL,'','int','','','select','Color','','eav/entity_attribute_source_table',1,1,1,1,'',1,1,1,0,0,0,1),(273,10,'status',NULL,'','int','','','select','Status',NULL,'catalog/entity_product_attribute_source_status',0,1,1,0,'',1,0,0,0,0,0,1),(274,10,'tax_class_id',NULL,'','int','','','select','Tax Class',NULL,'tax/class_source_product',0,1,1,0,'',1,0,0,0,0,0,1),(275,11,'entity_id',NULL,'sales_entity/quote_attribute_backend_parent','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(276,11,'is_active',NULL,'','static','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(277,11,'customer_id',NULL,'','int','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(278,11,'remote_ip',NULL,'','varchar','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(279,11,'checkout_method',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(280,11,'password_hash',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(281,11,'quote_status_id',NULL,'','int','','','text','Quote Status',NULL,'sales_entity/quote_attribute_source_status',1,1,0,0,'',0,0,0,0,0,0,1),(282,11,'billing_address_id',NULL,'','int','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(283,11,'converted_at',NULL,'','datetime','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(284,11,'coupon_code',NULL,'','varchar','','','text','Coupon',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(285,11,'giftcert_code',NULL,'','varchar','','','text','Gift certificate',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(286,11,'custbalance_amount',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(287,11,'base_currency_code',NULL,'','varchar','','','text','Base currency',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(288,11,'store_currency_code',NULL,'','varchar','','','text','Store currency',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(289,11,'quote_currency_code',NULL,'','varchar','','','text','Quote currency',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(290,11,'store_to_base_rate',NULL,'','decimal','','','text','Store to Base rate',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(291,11,'store_to_quote_rate',NULL,'','decimal','','','text','Store to Quote rate',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(292,11,'grand_total',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(293,11,'orig_order_id',NULL,'','varchar','','','text','Original order ID',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(294,11,'applied_rule_ids',NULL,'','text','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(295,11,'is_virtual',NULL,'','int','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(296,11,'is_multi_shipping',NULL,'','int','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(297,11,'is_multi_payment',NULL,'','int','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(298,12,'entity_id',NULL,'sales_entity/quote_address_attribute_backend_parent','static','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(299,12,'parent_id',NULL,'sales_entity/quote_attribute_backend_child','static','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(300,12,'address_type',NULL,'','varchar','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(301,12,'customer_id',NULL,'','int','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(302,12,'customer_address_id',NULL,'','int','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(303,12,'email',NULL,'','varchar','','','text','Email',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(304,12,'firstname',NULL,'','varchar','','','text','First Name',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(305,12,'lastname',NULL,'','varchar','','','text','Last Name',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(306,12,'company',NULL,'','varchar','','','text','Company',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(307,12,'street',NULL,'','varchar','','','text','Street Address',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(308,12,'city',NULL,'','varchar','','','text','City',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(309,12,'region',NULL,'','varchar','','','text','State/Province',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(310,12,'region_id',NULL,'','int','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(311,12,'postcode',NULL,'','varchar','','','text','Zip/Post Code',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(312,12,'country_id',NULL,'','varchar','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(313,12,'telephone',NULL,'','varchar','','','text','Telephone',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(314,12,'fax',NULL,'','varchar','','','text','Fax',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(315,12,'same_as_billing',NULL,'','int','','','text','Same as billing',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(316,12,'weight',NULL,'','decimal','','','text','Weight',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(317,12,'shipping_method',NULL,'','varchar','','','text','Shipping Method',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(318,12,'shipping_description',NULL,'','text','','','text','',NULL,'',1,0,0,0,'',0,0,0,0,0,0,1),(319,12,'subtotal',NULL,'','varchar','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(320,12,'tax_amount',NULL,'','varchar','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(321,12,'shipping_amount',NULL,'','varchar','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(322,12,'discount_amount',NULL,'','varchar','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(323,12,'custbalance_amount',NULL,'','varchar','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(324,12,'grand_total',NULL,'','varchar','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(325,12,'customer_notes',NULL,'','text','','','text','Customer Notes',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(326,13,'parent_id',NULL,'sales_entity/quote_address_attribute_backend_child','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(327,13,'code',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(328,13,'carrier',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(329,13,'carrier_title',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(330,13,'method',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(331,13,'method_description',NULL,'','text','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(332,13,'price',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(333,13,'error_message',NULL,'','text','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(334,14,'parent_id',NULL,'sales_entity/quote_address_attribute_backend_child','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(335,14,'quote_item_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(336,14,'qty',NULL,'','decimal','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(337,14,'discount_percent',NULL,'','decimal','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(338,14,'discount_amount',NULL,'','decimal','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(339,14,'tax_percent',NULL,'','decimal','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(340,14,'tax_amount',NULL,'','decimal','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(341,14,'row_total',NULL,'','decimal','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(342,14,'row_weight',NULL,'','decimal','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(343,15,'parent_id',NULL,'sales_entity/quote_attribute_backend_child','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(344,15,'product_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(345,15,'parent_product_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(346,15,'sku',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(347,15,'image',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(348,15,'name',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(349,15,'description',NULL,'','text','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(350,15,'weight',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(351,15,'qty',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(352,15,'price',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(353,15,'discount_percent',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(354,15,'discount_amount',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(355,15,'tax_percent',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(356,15,'tax_amount',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(357,15,'row_total',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(358,15,'row_weight',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(359,16,'parent_id',NULL,'sales_entity/quote_attribute_backend_child','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(360,16,'customer_payment_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(361,16,'method',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(362,16,'po_number',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(363,16,'cc_type',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(364,16,'cc_number_enc',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(365,16,'cc_last4',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(366,16,'cc_owner',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(367,16,'cc_exp_month',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(368,16,'cc_exp_year',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(369,16,'cc_cid_enc',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(370,4,'entity_id',NULL,'sales_entity/order_attribute_backend_parent','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(371,4,'order_status_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(372,4,'quote_address_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(373,4,'base_currency_code',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(374,4,'store_currency_code',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(375,4,'order_currency_code',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(376,4,'store_to_base_rate',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(377,4,'store_to_order_rate',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(378,4,'is_virtual',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(379,4,'is_multi_payment',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(380,4,'total_paid',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(381,4,'total_due',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(382,4,'customer_notes',NULL,'','text','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(383,4,'total_qty_ordered',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(384,6,'parent_id',NULL,'sales_entity/order_attribute_backend_child','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(385,6,'quote_address_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(386,6,'customer_address_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(387,7,'parent_id',NULL,'sales_entity/order_attribute_backend_child','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(388,7,'quote_item_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(389,7,'sku',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(390,7,'description',NULL,'','text','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(391,7,'qty_ordered',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(392,8,'parent_id',NULL,'sales_entity/order_attribute_backend_child','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(393,8,'quote_payment_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(394,8,'customer_payment_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(395,8,'amount',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(396,8,'cc_status',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(397,8,'cc_status_description',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(398,8,'cc_debug_request_body',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(399,8,'cc_debug_response_body',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(400,8,'cc_debug_response_serialized',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(401,8,'anet_trans_method',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(402,8,'echeck_routing_number',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(403,8,'echeck_bank_name',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(404,8,'echeck_account_type',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(405,8,'echeck_account_name',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(406,8,'echeck_type',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(407,17,'parent_id',NULL,'sales_entity/order_attribute_backend_child','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(408,17,'order_status_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(409,17,'comments',NULL,'','text','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(410,17,'is_customer_notified',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(411,18,'entity_id',NULL,'sales_entity/invoice_attribute_backend_parent','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(412,18,'invoice_type',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(413,18,'customer_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(414,18,'order_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(415,18,'real_order_id',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(416,18,'invoice_status_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(417,18,'billing_address_id',NULL,'sales_entity/order_attribute_backend_billing','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(418,18,'shipping_address_id',NULL,'sales_entity/order_attribute_backend_shipping','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(419,18,'base_currency_code',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(420,18,'store_currency_code',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(421,18,'order_currency_code',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(422,18,'store_to_base_rate',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(423,18,'store_to_order_rate',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(424,18,'is_virtual',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(425,18,'subtotal',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(426,18,'tax_amount',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(427,18,'shipping_amount',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(428,18,'grand_total',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(429,18,'total_paid',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(430,18,'total_due',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(431,18,'total_qty',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(432,19,'parent_id',NULL,'sales_entity/invoice_attribute_backend_child','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(433,19,'order_address_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(434,19,'address_type',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(435,19,'customer_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(436,19,'customer_address_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(437,19,'email',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(438,19,'firstname',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(439,19,'lastname',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(440,19,'company',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(441,19,'street',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(442,19,'city',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(443,19,'region',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(444,19,'region_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(445,19,'postcode',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(446,19,'country_id',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(447,19,'telephone',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(448,19,'fax',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(449,20,'parent_id',NULL,'sales_entity/invoice_attribute_backend_child','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(450,20,'order_item_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(451,20,'product_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(452,20,'name',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(453,20,'description',NULL,'','text','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(454,20,'sku',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(455,20,'qty',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(456,20,'price',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(457,20,'cost',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(458,20,'row_total',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(459,20,'shipment_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(460,21,'parent_id',NULL,'sales_entity/invoice_attribute_backend_child','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(461,21,'order_payment_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(462,21,'amount',NULL,'','decimal','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(463,21,'method',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(464,21,'cc_trans_id',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(465,21,'cc_approval',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(466,21,'cc_debug_request',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(467,21,'cc_debug_response',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(468,22,'parent_id',NULL,'sales_entity/invoice_attribute_backend_child','static','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(469,22,'order_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(470,22,'shipping_method',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(471,22,'tracking_id',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(472,22,'shipment_status_id',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(473,11,'customer_tax_class_id',NULL,'','int','','','select','Customer Tax Class',NULL,'tax/class_source_customer',1,1,0,0,'',0,0,0,0,0,0,1),(474,15,'tax_class_id',NULL,'','int','','','select','Tax Class',NULL,'tax/class_source_product',1,1,0,0,'',0,0,0,0,0,0,1),(475,14,'tax_class_id',NULL,'','int','','','select','Tax Class',NULL,'tax/class_source_product',1,1,0,0,'',0,0,0,0,0,0,1),(477,1,'created_in',NULL,'','int','','','select','Created From',NULL,'customer_entity/customer_attribute_source_store',1,1,1,0,'',0,0,0,0,0,0,1),(478,1,'store_id',NULL,'customer_entity/customer_attribute_backend_store','static','','','select','Create In',NULL,'customer_entity/customer_attribute_source_store',1,1,1,0,'',0,0,0,0,0,0,1),(479,9,'url_key',NULL,'','varchar','','','text','SEF URL Identifier<br/>(will replace category name)',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(480,10,'qty_is_decimal',NULL,'','int','','','boolean','Qty Uses Decimals',NULL,'',1,1,0,0,'',0,0,0,0,0,0,0),(481,10,'url_key',NULL,'catalog_entity/product_attribute_backend_urlkey','varchar','','','text','SEF URL Identifier<br/>(will replace product name)',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(482,15,'applied_rule_ids',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(483,7,'applied_rule_ids',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(488,12,'free_shipping',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(489,14,'free_shipping',NULL,'','int','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(490,15,'free_shipping',NULL,'','int','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(491,4,'applied_rule_ids',NULL,'','varchar','','','text','',NULL,'',1,1,0,0,'',0,0,0,0,0,0,1),(493,10,'thumbnail',NULL,'catalog_entity/product_attribute_backend_image','varchar','','catalog_entity/product_attribute_frontend_image','image','Thumbnail','','',1,1,0,0,'',0,0,0,0,0,0,1),(503,10,'minimal_price',NULL,'','decimal','','','price','Minimal Price',NULL,'',0,0,0,0,'',0,0,0,0,0,0,1),(504,12,'collect_shipping_rates',NULL,'','int','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(505,15,'super_product_id',NULL,'','int','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(506,10,'short_description',NULL,'','text','','','textarea','Short Description','','',1,1,1,0,'',1,0,1,0,0,0,1),(514,9,'page_layout',NULL,'','varchar','','','select','Page Layout',NULL,'catalog_entity/category_attribute_source_layout',1,1,0,0,'',0,0,0,0,0,0,1),(515,14,'product_id',NULL,'','int','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(516,14,'super_product_id',NULL,'','int','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(517,14,'parent_product_id',NULL,'','int','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(518,14,'sku',NULL,'','varchar','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(519,14,'image',NULL,'','varchar','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(520,14,'name',NULL,'','varchar','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(521,14,'description',NULL,'','text','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(522,14,'weight',NULL,'','decimal','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(523,14,'price',NULL,'','decimal','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(524,14,'applied_rule_ids',NULL,'','varchar','','','text','',NULL,'',1,1,1,0,'',0,0,0,0,0,0,1),(526,10,'visibility',NULL,'','int','','','select','Visibility',NULL,'catalog/entity_product_attribute_source_visibility',0,1,1,0,'3',0,0,0,0,0,0,1);

/*Table structure for table `eav_attribute_group` */

DROP TABLE IF EXISTS `eav_attribute_group`;

CREATE TABLE `eav_attribute_group` (
  `attribute_group_id` smallint(5) unsigned NOT NULL auto_increment,
  `attribute_set_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_group_name` varchar(255) character set latin1 NOT NULL default '',
  `sort_order` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`attribute_group_id`),
  UNIQUE KEY `attribute_set_id` (`attribute_set_id`,`attribute_group_name`),
  KEY `attribute_set_id_2` (`attribute_set_id`,`sort_order`),
  CONSTRAINT `FK_eav_attribute_group` FOREIGN KEY (`attribute_set_id`) REFERENCES `eav_attribute_set` (`attribute_set_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `eav_attribute_group` */

insert  into `eav_attribute_group`(`attribute_group_id`,`attribute_set_id`,`attribute_group_name`,`sort_order`) values (1,1,'General',4),(2,2,'General',4),(3,3,'General',4),(4,9,'General',1),(7,12,'General',3),(9,9,'Prices',2),(12,9,'Meta Information',3),(15,9,'Images',4),(17,23,'General',3),(18,24,'General',3),(19,25,'General',3),(20,26,'General',3),(21,27,'General',3),(22,28,'General',3),(23,18,'General',2),(24,20,'General',2),(25,21,'General',2),(26,22,'General',2),(27,29,'General',3),(28,30,'General',3),(29,31,'General',3),(30,32,'General',3),(31,33,'General',3),(32,34,'General',3),(95,9,'Description',5);

/*Table structure for table `eav_attribute_option` */

DROP TABLE IF EXISTS `eav_attribute_option`;

CREATE TABLE `eav_attribute_option` (
  `option_id` int(10) unsigned NOT NULL auto_increment,
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `sort_order` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`option_id`),
  KEY `FK_ATTRIBUTE_OPTION_ATTRIBUTE` (`attribute_id`),
  CONSTRAINT `FK_ATTRIBUTE_OPTION_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attributes option (for source model)';

/*Data for the table `eav_attribute_option` */

insert  into `eav_attribute_option`(`option_id`,`attribute_id`,`sort_order`) values (1,102,1),(2,102,2),(3,102,3),(4,102,4),(5,102,5),(6,102,6),(7,102,7),(8,102,8),(9,102,9),(10,102,10),(11,465,1),(12,465,2),(13,465,3),(20,102,12),(21,102,11),(22,272,0),(23,272,0),(24,272,0),(25,272,0),(26,272,0),(27,102,0),(28,102,0),(29,102,0),(30,102,0),(31,102,0),(32,102,0),(33,102,0),(34,102,0),(54,97,0),(55,97,0),(57,272,0),(58,272,0),(59,272,0),(60,272,0),(61,272,0),(62,102,0),(63,102,0),(64,102,0),(83,102,0),(101,102,0),(102,102,0),(103,102,0),(104,102,0),(105,102,0);

/*Table structure for table `eav_attribute_option_value` */

DROP TABLE IF EXISTS `eav_attribute_option_value`;

CREATE TABLE `eav_attribute_option_value` (
  `value_id` int(10) unsigned NOT NULL auto_increment,
  `option_id` int(10) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`value_id`),
  KEY `FK_ATTRIBUTE_OPTION_VALUE_OPTION` (`option_id`),
  KEY `FK_ATTRIBUTE_OPTION_VALUE_STORE` (`store_id`),
  CONSTRAINT `FK_ATTRIBUTE_OPTION_VALUE_OPTION` FOREIGN KEY (`option_id`) REFERENCES `eav_attribute_option` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ATTRIBUTE_OPTION_VALUE_STORE` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attribute option values per store';

/*Data for the table `eav_attribute_option_value` */

insert  into `eav_attribute_option_value`(`value_id`,`option_id`,`store_id`,`value`) values (51,11,1,'Red'),(52,12,1,'Blue'),(53,13,1,'Yellow'),(54,11,0,'Red'),(55,12,0,'Blue'),(56,13,0,'Yellow'),(919,55,0,'Mens'),(920,54,0,'Womens'),(1036,105,0,'Nine West'),(1037,27,0,'Gateway'),(1038,28,0,'Acer'),(1039,29,0,'Apple'),(1040,30,0,'Dell'),(1041,31,0,'Kodak'),(1042,32,0,'Argus'),(1043,33,0,'Olympus'),(1044,34,0,'Canon'),(1045,62,0,'Steve Madden'),(1046,63,0,'CN CLogs'),(1047,64,0,'Asics'),(1048,83,0,'Toshiba'),(1049,101,0,'At&t'),(1050,102,0,'Anashria'),(1051,103,0,'Kenneth Cole'),(1052,104,0,'Ecco'),(1053,1,0,'LG'),(1054,1,1,'LG'),(1055,2,0,'Sony'),(1056,2,1,'Sony'),(1057,3,0,'Samsung'),(1058,3,1,'Samsung'),(1059,4,0,'HP'),(1060,4,1,'HP'),(1061,5,0,'JVC'),(1062,5,1,'JVC'),(1063,6,0,'Panasonic'),(1064,6,1,'Panasonic'),(1065,7,0,'Yamaha'),(1066,7,1,'Yamaha'),(1067,8,0,'Philips'),(1068,8,1,'Philips'),(1069,9,0,'Acco'),(1070,9,1,'Acco'),(1071,10,0,'Aiwa'),(1072,10,1,'Aiwa'),(1073,21,0,'BlackBerry'),(1074,21,1,'BlackBerry'),(1075,20,0,'Nokia'),(1076,20,1,'Nokia'),(1110,61,0,'Gray'),(1111,23,0,'Silver'),(1112,23,1,'Silver'),(1113,24,0,'Black'),(1114,24,1,'Black'),(1115,25,0,'Blue'),(1116,25,1,'Blue'),(1117,26,0,'Red'),(1118,26,1,'Red'),(1119,57,0,'Pink'),(1120,58,0,'Magneta'),(1121,59,0,'Brown'),(1122,60,0,'White'),(1123,22,0,'Green'),(1124,22,1,'Green');

/*Table structure for table `eav_attribute_set` */

DROP TABLE IF EXISTS `eav_attribute_set`;

CREATE TABLE `eav_attribute_set` (
  `attribute_set_id` smallint(5) unsigned NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_set_name` varchar(255) character set utf8 collate utf8_swedish_ci NOT NULL default '',
  `sort_order` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`attribute_set_id`),
  UNIQUE KEY `entity_type_id` (`entity_type_id`,`attribute_set_name`),
  KEY `entity_type_id_2` (`entity_type_id`,`sort_order`),
  CONSTRAINT `FK_eav_attribute_set` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `eav_attribute_set` */

insert  into `eav_attribute_set`(`attribute_set_id`,`entity_type_id`,`attribute_set_name`,`sort_order`) values (1,1,'Default',4),(2,2,'Default',4),(3,3,'Default',4),(9,10,'Default',5),(12,9,'Default',3),(18,4,'Default',3),(19,5,'Default',1),(20,6,'Default',3),(21,7,'Default',3),(22,8,'Default',3),(23,11,'Default',2),(24,12,'Default',2),(25,13,'Default',2),(26,14,'Default',2),(27,15,'Default',2),(28,16,'Default',2),(29,17,'Default',2),(30,18,'Default',2),(31,19,'Default',2),(32,20,'Default',2),(33,21,'Default',2),(34,22,'Default',2);

/*Table structure for table `eav_entity` */

DROP TABLE IF EXISTS `eav_entity`;

CREATE TABLE `eav_entity` (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_set_id` smallint(5) unsigned NOT NULL default '0',
  `increment_id` varchar(50) NOT NULL default '',
  `parent_id` int(11) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `is_active` tinyint(1) unsigned NOT NULL default '1',
  PRIMARY KEY  (`entity_id`),
  KEY `FK_ENTITY_ENTITY_TYPE` (`entity_type_id`),
  KEY `FK_ENTITY_STORE` (`store_id`),
  CONSTRAINT `FK_eav_entity` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_eav_entity_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Entityies';

/*Data for the table `eav_entity` */

/*Table structure for table `eav_entity_attribute` */

DROP TABLE IF EXISTS `eav_entity_attribute`;

CREATE TABLE `eav_entity_attribute` (
  `entity_attribute_id` int(10) unsigned NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_set_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_group_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `sort_order` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`entity_attribute_id`),
  UNIQUE KEY `attribute_set_id_2` (`attribute_set_id`,`attribute_id`),
  UNIQUE KEY `attribute_group_id` (`attribute_group_id`,`attribute_id`),
  KEY `attribute_set_id_3` (`attribute_set_id`,`sort_order`),
  KEY `FK_EAV_ENTITY_ATTRIVUTE_ATTRIBUTE` (`attribute_id`),
  CONSTRAINT `FK_eav_entity_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_eav_entity_attribute_group` FOREIGN KEY (`attribute_group_id`) REFERENCES `eav_attribute_group` (`attribute_group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_ATTRIVUTE_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_ATTRIVUTE_GROUP` FOREIGN KEY (`attribute_group_id`) REFERENCES `eav_attribute_group` (`attribute_group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `eav_entity_attribute` */

insert  into `eav_entity_attribute`(`entity_attribute_id`,`entity_type_id`,`attribute_set_id`,`attribute_group_id`,`attribute_id`,`sort_order`) values (1,1,1,1,1,3),(2,1,1,1,2,4),(3,1,1,1,3,5),(4,1,1,1,4,4),(5,1,1,1,5,6),(6,1,1,1,6,7),(7,1,1,1,7,7),(8,1,1,1,8,8),(9,2,2,2,9,1),(10,2,2,2,10,2),(11,2,2,2,11,6),(12,2,2,2,12,7),(13,2,2,2,13,8),(14,2,2,2,14,9),(15,2,2,2,15,5),(16,2,2,2,16,4),(17,2,2,2,17,10),(18,2,2,2,18,11),(19,3,3,3,19,1),(95,2,2,2,95,3),(112,10,9,4,110,15),(194,8,1,1,256,0),(195,8,1,1,255,0),(196,8,1,1,254,0),(197,8,1,1,253,0),(198,7,1,1,252,0),(199,7,1,1,251,0),(200,7,1,1,250,0),(201,7,1,1,249,0),(202,7,1,1,248,0),(203,7,1,1,247,0),(204,7,1,1,246,0),(205,7,1,1,245,0),(206,7,1,1,244,0),(207,7,1,1,243,0),(208,7,1,1,242,0),(209,7,1,1,241,0),(210,7,1,1,240,0),(211,7,1,1,239,0),(212,7,1,1,238,0),(213,7,1,1,237,0),(214,7,1,1,236,0),(215,7,1,1,235,0),(216,7,1,1,234,0),(217,6,1,1,233,0),(218,6,1,1,232,0),(219,6,1,1,231,0),(220,6,1,1,230,0),(221,6,1,1,229,0),(222,6,1,1,228,0),(223,6,1,1,227,0),(224,6,1,1,226,0),(225,6,1,1,225,0),(226,6,1,1,223,0),(227,6,1,1,224,0),(228,6,1,1,222,0),(229,6,1,1,221,0),(230,6,1,1,220,0),(231,6,1,1,219,0),(232,6,1,1,218,0),(233,4,1,1,217,0),(234,4,1,1,216,0),(235,4,1,1,215,0),(236,4,1,1,214,0),(237,4,1,1,213,0),(238,4,1,1,212,0),(239,4,1,1,211,0),(240,4,1,1,210,0),(241,4,1,1,209,0),(242,4,1,1,208,0),(243,4,1,1,207,0),(245,4,1,1,205,0),(246,4,1,1,204,0),(247,4,1,1,203,0),(248,4,1,1,202,0),(249,4,1,1,201,0),(250,4,1,1,200,0),(251,4,1,1,199,0),(252,4,1,1,198,0),(253,4,1,1,197,0),(254,4,1,1,196,0),(255,4,1,1,195,0),(256,4,1,1,194,0),(257,8,1,1,257,0),(258,8,1,1,258,0),(259,8,1,1,259,0),(260,8,1,1,260,0),(261,8,1,1,261,0),(262,8,1,1,262,0),(263,8,1,1,263,0),(264,8,1,1,264,0),(265,8,1,1,265,0),(266,8,1,1,266,0),(267,5,1,1,267,0),(268,5,1,1,268,0),(271,9,12,7,111,1),(272,9,12,7,112,2),(273,9,12,7,113,3),(274,9,12,7,114,4),(275,9,12,7,115,5),(276,9,12,7,116,6),(277,9,12,7,117,7),(278,9,12,7,118,8),(279,9,12,7,119,9),(280,9,12,7,120,10),(281,9,12,7,121,11),(282,9,12,7,122,12),(283,9,12,7,123,13),(326,11,23,17,275,1),(327,11,23,17,276,2),(328,11,23,17,277,3),(329,11,23,17,278,4),(330,11,23,17,279,5),(331,11,23,17,280,6),(332,11,23,17,281,7),(333,11,23,17,282,8),(334,11,23,17,283,9),(335,11,23,17,284,10),(336,11,23,17,285,11),(337,11,23,17,286,12),(338,11,23,17,287,13),(339,11,23,17,288,14),(340,11,23,17,289,15),(341,11,23,17,290,16),(342,11,23,17,291,17),(343,11,23,17,292,18),(344,11,23,17,293,19),(345,11,23,17,294,20),(346,11,23,17,295,21),(347,11,23,17,296,22),(348,11,23,17,297,23),(349,12,24,18,298,1),(350,12,24,18,299,2),(351,12,24,18,300,3),(352,12,24,18,301,4),(353,12,24,18,302,5),(354,12,24,18,303,6),(355,12,24,18,304,7),(356,12,24,18,305,8),(357,12,24,18,306,9),(358,12,24,18,307,10),(359,12,24,18,308,11),(360,12,24,18,309,12),(361,12,24,18,310,13),(362,12,24,18,311,14),(363,12,24,18,312,15),(364,12,24,18,313,16),(365,12,24,18,314,17),(366,12,24,18,315,18),(367,12,24,18,316,19),(368,12,24,18,317,20),(369,12,24,18,318,21),(370,12,24,18,319,22),(371,12,24,18,320,23),(372,12,24,18,321,24),(373,12,24,18,322,25),(374,12,24,18,323,26),(375,12,24,18,324,27),(376,12,24,18,325,28),(377,13,25,19,326,1),(378,13,25,19,327,2),(379,13,25,19,328,3),(380,13,25,19,329,4),(381,13,25,19,330,5),(382,13,25,19,331,6),(383,13,25,19,332,7),(384,13,25,19,333,8),(385,14,26,20,334,1),(386,14,26,20,335,2),(387,14,26,20,336,3),(388,14,26,20,337,4),(389,14,26,20,338,5),(390,14,26,20,339,6),(391,14,26,20,340,7),(392,14,26,20,341,8),(393,14,26,20,342,9),(394,15,27,21,343,1),(395,15,27,21,344,2),(396,15,27,21,345,3),(397,15,27,21,346,4),(398,15,27,21,347,5),(399,15,27,21,348,6),(400,15,27,21,349,7),(401,15,27,21,350,8),(402,15,27,21,351,9),(403,15,27,21,352,10),(404,15,27,21,353,11),(405,15,27,21,354,12),(406,15,27,21,355,13),(407,15,27,21,356,14),(408,15,27,21,357,15),(409,15,27,21,358,16),(410,16,28,22,359,1),(411,16,28,22,360,2),(412,16,28,22,361,3),(413,16,28,22,362,4),(414,16,28,22,363,5),(415,16,28,22,364,6),(416,16,28,22,365,7),(417,16,28,22,366,8),(418,16,28,22,367,9),(419,16,28,22,368,10),(420,16,28,22,369,11),(421,4,18,23,370,1),(422,4,18,23,205,2),(423,4,18,23,210,3),(424,4,18,23,371,4),(425,4,18,23,204,5),(426,4,18,23,372,6),(427,4,18,23,217,7),(428,4,18,23,216,8),(429,4,18,23,212,9),(430,4,18,23,213,10),(431,4,18,23,373,11),(432,4,18,23,374,12),(433,4,18,23,375,13),(434,4,18,23,376,14),(435,4,18,23,377,15),(436,4,18,23,378,16),(437,4,18,23,379,17),(438,4,18,23,196,18),(439,4,18,23,214,19),(440,4,18,23,208,20),(441,4,18,23,198,21),(442,4,18,23,200,22),(443,4,18,23,201,23),(444,4,18,23,199,24),(445,4,18,23,202,25),(446,4,18,23,203,26),(447,4,18,23,194,27),(448,4,18,23,380,28),(449,4,18,23,381,29),(450,4,18,23,382,30),(451,4,18,23,383,31),(452,6,20,24,384,1),(453,6,20,24,385,2),(454,6,20,24,233,3),(455,6,20,24,221,4),(456,6,20,24,386,5),(457,6,20,24,223,6),(458,6,20,24,224,7),(459,6,20,24,225,8),(460,6,20,24,226,9),(461,6,20,24,222,10),(462,6,20,24,227,11),(463,6,20,24,228,12),(464,6,20,24,218,13),(465,6,20,24,229,14),(466,6,20,24,219,15),(467,6,20,24,230,16),(468,6,20,24,231,17),(469,7,21,25,387,1),(470,7,21,25,388,2),(471,7,21,25,249,3),(472,7,21,25,389,4),(473,7,21,25,250,5),(474,7,21,25,251,6),(475,7,21,25,390,7),(476,7,21,25,391,8),(477,7,21,25,236,9),(478,7,21,25,237,10),(479,7,21,25,238,11),(480,7,21,25,239,12),(481,7,21,25,240,13),(482,7,21,25,242,14),(483,7,21,25,243,15),(484,7,21,25,244,16),(485,7,21,25,245,17),(486,7,21,25,246,18),(487,7,21,25,247,19),(488,7,21,25,248,20),(489,8,22,26,392,1),(490,8,22,26,393,2),(491,8,22,26,394,3),(492,8,22,26,395,4),(493,8,22,26,257,5),(494,8,22,26,258,6),(495,8,22,26,259,7),(496,8,22,26,260,8),(497,8,22,26,261,9),(498,8,22,26,262,10),(499,8,22,26,253,11),(500,8,22,26,254,12),(501,8,22,26,396,13),(502,8,22,26,397,14),(503,8,22,26,263,15),(504,8,22,26,264,16),(505,8,22,26,265,17),(506,8,22,26,266,18),(507,8,22,26,398,19),(508,8,22,26,399,20),(509,8,22,26,400,21),(510,8,22,26,401,22),(511,8,22,26,402,23),(512,8,22,26,403,24),(513,8,22,26,404,25),(514,8,22,26,405,26),(515,8,22,26,406,27),(516,17,29,27,407,1),(517,17,29,27,408,2),(518,17,29,27,409,3),(519,17,29,27,410,4),(520,18,30,28,411,1),(521,18,30,28,412,2),(522,18,30,28,413,3),(523,18,30,28,414,4),(524,18,30,28,415,5),(525,18,30,28,416,6),(526,18,30,28,417,7),(527,18,30,28,418,8),(528,18,30,28,419,9),(529,18,30,28,420,10),(530,18,30,28,421,11),(531,18,30,28,422,12),(532,18,30,28,423,13),(533,18,30,28,424,14),(534,18,30,28,425,15),(535,18,30,28,426,16),(536,18,30,28,427,17),(537,18,30,28,428,18),(538,18,30,28,429,19),(539,18,30,28,430,20),(540,18,30,28,431,21),(541,19,31,29,432,1),(542,19,31,29,433,2),(543,19,31,29,434,3),(544,19,31,29,435,4),(545,19,31,29,436,5),(546,19,31,29,437,6),(547,19,31,29,438,7),(548,19,31,29,439,8),(549,19,31,29,440,9),(550,19,31,29,441,10),(551,19,31,29,442,11),(552,19,31,29,443,12),(553,19,31,29,444,13),(554,19,31,29,445,14),(555,19,31,29,446,15),(556,19,31,29,447,16),(557,19,31,29,448,17),(558,20,32,30,449,1),(559,20,32,30,450,2),(560,20,32,30,451,3),(561,20,32,30,452,4),(562,20,32,30,453,5),(563,20,32,30,454,6),(564,20,32,30,455,7),(565,20,32,30,456,8),(566,20,32,30,457,9),(567,20,32,30,458,10),(568,20,32,30,459,11),(569,21,33,31,460,1),(570,21,33,31,461,2),(571,21,33,31,462,3),(572,21,33,31,463,4),(573,21,33,31,464,5),(574,21,33,31,465,6),(575,21,33,31,466,7),(576,21,33,31,467,8),(577,22,34,32,468,1),(578,22,34,32,469,2),(579,22,34,32,470,3),(580,22,34,32,471,4),(581,22,34,32,472,5),(582,11,23,17,473,24),(583,15,27,21,474,17),(584,14,26,20,475,10),(624,1,1,1,477,2),(625,1,1,1,478,1),(626,9,12,7,479,14),(633,15,27,21,482,18),(634,7,21,25,483,21),(904,12,24,18,488,29),(905,14,26,20,489,11),(906,15,27,21,490,19),(907,4,18,23,491,32),(1168,10,9,9,503,1),(1175,12,24,18,504,30),(1176,15,27,21,505,20),(1674,9,12,7,514,15),(1675,14,26,20,515,12),(1676,14,26,20,516,13),(1677,14,26,20,517,14),(1678,14,26,20,518,15),(1679,14,26,20,519,16),(1680,14,26,20,520,17),(1681,14,26,20,521,18),(1682,14,26,20,522,19),(1683,14,26,20,523,20),(1684,14,26,20,524,21),(2307,10,9,4,96,1),(2308,10,9,4,98,2),(2309,10,9,4,101,3),(2311,10,9,4,269,5),(2312,10,9,4,273,6),(2313,10,9,4,274,7),(2314,10,9,4,480,8),(2315,10,9,4,481,9),(2316,10,9,4,526,10),(2317,10,9,9,99,1),(2318,10,9,9,100,2),(2319,10,9,9,270,3),(2320,10,9,12,103,1),(2321,10,9,12,104,2),(2322,10,9,12,105,3),(2323,10,9,15,106,4),(2324,10,9,15,109,2),(2325,10,9,15,271,3),(2326,10,9,15,493,1),(2327,10,9,95,97,1),(2328,10,9,95,506,2);

/*Table structure for table `eav_entity_datetime` */

DROP TABLE IF EXISTS `eav_entity_datetime`;

CREATE TABLE `eav_entity_datetime` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`value_id`),
  KEY `FK_ATTRIBUTE_DATETIME_ENTITY_TYPE` (`entity_type_id`),
  KEY `FK_ATTRIBUTE_DATETIME_ATTRIBUTE` (`attribute_id`),
  KEY `FK_ATTRIBUTE_DATETIME_STORE` (`store_id`),
  KEY `FK_ATTRIBUTE_DATETIME_ENTITY` (`entity_id`),
  KEY `value_by_attribute` (`attribute_id`,`value`),
  KEY `value_by_entity_type` (`entity_type_id`,`value`),
  CONSTRAINT `FK_EAV_ENTITY_DATETIME_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_DATETIME_ENTITY_TYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_DATETIME_STORE` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Datetime values of attributes';

/*Data for the table `eav_entity_datetime` */

/*Table structure for table `eav_entity_decimal` */

DROP TABLE IF EXISTS `eav_entity_decimal`;

CREATE TABLE `eav_entity_decimal` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` decimal(12,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`value_id`),
  KEY `FK_ATTRIBUTE_DECIMAL_ENTITY_TYPE` (`entity_type_id`),
  KEY `FK_ATTRIBUTE_DECIMAL_ATTRIBUTE` (`attribute_id`),
  KEY `FK_ATTRIBUTE_DECIMAL_STORE` (`store_id`),
  KEY `FK_ATTRIBUTE_DECIMAL_ENTITY` (`entity_id`),
  KEY `value_by_attribute` (`attribute_id`,`value`),
  KEY `value_by_entity_type` (`entity_type_id`,`value`),
  CONSTRAINT `FK_EAV_ENTITY_DECIMAL_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_DECIMAL_ENTITY_TYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_DECIMAL_STORE` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Decimal values of attributes';

/*Data for the table `eav_entity_decimal` */

/*Table structure for table `eav_entity_int` */

DROP TABLE IF EXISTS `eav_entity_int`;

CREATE TABLE `eav_entity_int` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY  (`value_id`),
  KEY `FK_ATTRIBUTE_INT_ENTITY_TYPE` (`entity_type_id`),
  KEY `FK_ATTRIBUTE_INT_ATTRIBUTE` (`attribute_id`),
  KEY `FK_ATTRIBUTE_INT_STORE` (`store_id`),
  KEY `FK_ATTRIBUTE_INT_ENTITY` (`entity_id`),
  KEY `value_by_attribute` (`attribute_id`,`value`),
  KEY `value_by_entity_type` (`entity_type_id`,`value`),
  CONSTRAINT `FK_EAV_ENTITY_INT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_INT_ENTITY_TYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_INT_STORE` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Integer values of attributes';

/*Data for the table `eav_entity_int` */

/*Table structure for table `eav_entity_store` */

DROP TABLE IF EXISTS `eav_entity_store`;

CREATE TABLE `eav_entity_store` (
  `entity_store_id` int(10) unsigned NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `increment_prefix` varchar(20) NOT NULL default '',
  `increment_last_id` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`entity_store_id`),
  KEY `FK_eav_entity_store_entity_type` (`entity_type_id`),
  KEY `FK_eav_entity_store_store` (`store_id`),
  CONSTRAINT `FK_eav_entity_store_entity_type` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_eav_entity_store_store` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `eav_entity_store` */

insert  into `eav_entity_store`(`entity_store_id`,`entity_type_id`,`store_id`,`increment_prefix`,`increment_last_id`) values (2,1,0,'0','000000001'),(7,11,1,'1','100000002');

/*Table structure for table `eav_entity_text` */

DROP TABLE IF EXISTS `eav_entity_text`;

CREATE TABLE `eav_entity_text` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`value_id`),
  KEY `FK_ATTRIBUTE_TEXT_ENTITY_TYPE` (`entity_type_id`),
  KEY `FK_ATTRIBUTE_TEXT_ATTRIBUTE` (`attribute_id`),
  KEY `FK_ATTRIBUTE_TEXT_STORE` (`store_id`),
  KEY `FK_ATTRIBUTE_TEXT_ENTITY` (`entity_id`),
  CONSTRAINT `FK_EAV_ENTITY_TEXT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_TEXT_ENTITY_TYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_TEXT_STORE` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Text values of attributes';

/*Data for the table `eav_entity_text` */

/*Table structure for table `eav_entity_type` */

DROP TABLE IF EXISTS `eav_entity_type`;

CREATE TABLE `eav_entity_type` (
  `entity_type_id` smallint(5) unsigned NOT NULL auto_increment,
  `entity_type_code` varchar(50) NOT NULL default '',
  `entity_table` varchar(255) NOT NULL default '',
  `value_table_prefix` varchar(255) NOT NULL default '',
  `entity_id_field` varchar(255) NOT NULL default '',
  `is_data_sharing` tinyint(4) unsigned NOT NULL default '1',
  `data_sharing_key` varchar(100) default 'default',
  `default_attribute_set_id` smallint(5) unsigned NOT NULL default '0',
  `increment_model` varchar(255) NOT NULL default '',
  `increment_per_store` tinyint(1) unsigned NOT NULL default '0',
  `increment_pad_length` tinyint(8) unsigned NOT NULL default '8',
  `increment_pad_char` char(1) NOT NULL default '0',
  PRIMARY KEY  (`entity_type_id`),
  KEY `entity_name` (`entity_type_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `eav_entity_type` */

insert  into `eav_entity_type`(`entity_type_id`,`entity_type_code`,`entity_table`,`value_table_prefix`,`entity_id_field`,`is_data_sharing`,`data_sharing_key`,`default_attribute_set_id`,`increment_model`,`increment_per_store`,`increment_pad_length`,`increment_pad_char`) values (1,'customer','customer/entity','','',1,'default',1,'eav/entity_increment_numeric',0,8,'0'),(2,'customer_address','customer/entity','','',1,'default',2,'',0,8,'0'),(3,'customer_payment','customer/entity','','',1,'default',3,'',0,8,'0'),(4,'order','sales/order','','',1,'default',18,'eav/entity_increment_numeric',1,8,'0'),(5,'order_status','sales/order','','',1,'default',0,'',0,8,'0'),(6,'order_address','sales/order','','',1,'default',20,'',0,8,'0'),(7,'order_item','sales/order','','',1,'default',21,'',0,8,'0'),(8,'order_payment','sales/order','','',1,'default',22,'',0,8,'0'),(9,'catalog_category','catalog/category','','',0,'default',12,'',0,8,'0'),(10,'catalog_product','catalog/product','','',0,'default',9,'',0,8,'0'),(11,'quote','sales/quote','','',1,'default',23,'eav/entity_increment_alphanum',1,8,'0'),(12,'quote_address','sales/quote','','',1,'default',24,'',0,8,'0'),(13,'quote_address_rate','sales/quote_temp','','',1,'default',25,'',0,8,'0'),(14,'quote_address_item','sales/quote_temp','','',1,'default',26,'',0,8,'0'),(15,'quote_item','sales/quote','','',1,'default',27,'',0,8,'0'),(16,'quote_payment','sales/quote','','',1,'default',28,'',0,8,'0'),(17,'order_status_history','sales/order','','',1,'default',29,'',0,8,'0'),(18,'invoice','sales/invoice','','',1,'default',30,'eav/entity_increment_numeric',1,8,'0'),(19,'invoice_address','sales/invoice','','',1,'default',31,'',0,8,'0'),(20,'invoice_item','sales/invoice','','',1,'default',32,'',0,8,'0'),(21,'invoice_payment','sales/invoice','','',1,'default',33,'',0,8,'0'),(22,'invoice_shipment','sales/invoice','','',1,'default',34,'',0,8,'0');

/*Table structure for table `eav_entity_varchar` */

DROP TABLE IF EXISTS `eav_entity_varchar`;

CREATE TABLE `eav_entity_varchar` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`value_id`),
  KEY `FK_ATTRIBUTE_VARCHAR_ENTITY_TYPE` (`entity_type_id`),
  KEY `FK_ATTRIBUTE_VARCHAR_ATTRIBUTE` (`attribute_id`),
  KEY `FK_ATTRIBUTE_VARCHAR_STORE` (`store_id`),
  KEY `FK_ATTRIBUTE_VARCHAR_ENTITY` (`entity_id`),
  KEY `value_by_attribute` (`attribute_id`,`value`),
  KEY `value_by_entity_type` (`entity_type_id`,`value`),
  CONSTRAINT `FK_EAV_ENTITY_VARCHAR_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_VARCHAR_ENTITY_TYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_VARCHAR_STORE` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Varchar values of attributes';

/*Data for the table `eav_entity_varchar` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;

    ");

$installer->endSetup();
