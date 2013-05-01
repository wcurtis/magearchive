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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog install
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('catalog_category_entity')}`;
CREATE TABLE `{$installer->getTable('catalog_category_entity')}` (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_set_id` smallint(5) unsigned NOT NULL default '0',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `is_active` tinyint(1) unsigned NOT NULL default '1',
  `path` varchar(255) NOT NULL default '',
  `position` int(11) NOT NULL default '0',
  PRIMARY KEY  (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Category Entities';

DROP TABLE IF EXISTS `{$installer->getTable('catalog_category_entity_datetime')}`;
CREATE TABLE `{$installer->getTable('catalog_category_entity_datetime')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`value_id`),
  UNIQUE KEY `IDX_BASE` (`entity_type_id`,`entity_id`,`attribute_id`,`store_id`),
  KEY `FK_ATTRIBUTE_DATETIME_ENTITY` (`entity_id`),
  KEY `FK_CATALOG_CATEGORY_ENTITY_DATETIME_ATTRIBUTE` (`attribute_id`),
  KEY `FK_CATALOG_CATEGORY_ENTITY_DATETIME_STORE` (`store_id`),
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_DATETIME_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_DATETIME_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('catalog_category_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_DATETIME_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_category_entity_decimal')}`;
CREATE TABLE `{$installer->getTable('catalog_category_entity_decimal')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` decimal(12,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`value_id`),
  UNIQUE KEY `IDX_BASE` (`entity_type_id`,`entity_id`,`attribute_id`,`store_id`),
  KEY `FK_ATTRIBUTE_DECIMAL_ENTITY` (`entity_id`),
  KEY `FK_CATALOG_CATEGORY_ENTITY_DECIMAL_ATTRIBUTE` (`attribute_id`),
  KEY `FK_CATALOG_CATEGORY_ENTITY_DECIMAL_STORE` (`store_id`),
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_DECIMAL_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_DECIMAL_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('catalog_category_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_DECIMAL_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_category_entity_int')}`;
CREATE TABLE `{$installer->getTable('catalog_category_entity_int')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY  (`value_id`),
  UNIQUE KEY `IDX_BASE` (`entity_type_id`,`entity_id`,`attribute_id`,`store_id`),
  KEY `FK_ATTRIBUTE_INT_ENTITY` (`entity_id`),
  KEY `FK_CATALOG_CATEGORY_EMTITY_INT_ATTRIBUTE` (`attribute_id`),
  KEY `FK_CATALOG_CATEGORY_EMTITY_INT_STORE` (`store_id`),
  CONSTRAINT `FK_CATALOG_CATEGORY_EMTITY_INT_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_CATEGORY_EMTITY_INT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('catalog_category_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_CATEGORY_EMTITY_INT_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_category_entity_text')}`;
CREATE TABLE `{$installer->getTable('catalog_category_entity_text')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`value_id`),
  UNIQUE KEY `IDX_BASE` (`entity_type_id`,`entity_id`,`attribute_id`,`store_id`),
  KEY `FK_ATTRIBUTE_TEXT_ENTITY` (`entity_id`),
  KEY `FK_CATALOG_CATEGORY_ENTITY_TEXT_ATTRIBUTE` (`attribute_id`),
  KEY `FK_CATALOG_CATEGORY_ENTITY_TEXT_STORE` (`store_id`),
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_TEXT_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_TEXT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('catalog_category_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_TEXT_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_category_entity_varchar')}`;
CREATE TABLE `{$installer->getTable('catalog_category_entity_varchar')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`value_id`),
  UNIQUE KEY `IDX_BASE` USING BTREE (`entity_type_id`,`entity_id`,`attribute_id`,`store_id`),
  KEY `FK_ATTRIBUTE_VARCHAR_ENTITY` (`entity_id`),
  KEY `FK_CATALOG_CATEGORY_ENTITY_VARCHAR_ATTRIBUTE` (`attribute_id`),
  KEY `FK_CATALOG_CATEGORY_ENTITY_VARCHAR_STORE` (`store_id`),
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_VARCHAR_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_VARCHAR_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('catalog_category_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_VARCHAR_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_category_product')}`;
CREATE TABLE `{$installer->getTable('catalog_category_product')}` (
  `category_id` int(10) unsigned NOT NULL default '0',
  `product_id` int(10) unsigned NOT NULL default '0',
  `position` int(10) unsigned NOT NULL default '0',
  KEY `CATALOG_CATEGORY_PRODUCT_CATEGORY` (`category_id`),
  KEY `CATALOG_CATEGORY_PRODUCT_PRODUCT` (`product_id`),
  CONSTRAINT `CATALOG_CATEGORY_PRODUCT_CATEGORY` FOREIGN KEY (`category_id`) REFERENCES `{$installer->getTable('catalog_category_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `CATALOG_CATEGORY_PRODUCT_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_compare_item')}`;
CREATE TABLE `{$installer->getTable('catalog_compare_item')}` (
  `catalog_compare_item_id` int(11) unsigned NOT NULL auto_increment,
  `visitor_id` int(11) unsigned NOT NULL default '0',
  `customer_id` int(11) unsigned default NULL,
  `product_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`catalog_compare_item_id`),
  KEY `FK_CATALOG_COMPARE_ITEM_CUSTOMER` (`customer_id`),
  KEY `FK_CATALOG_COMPARE_ITEM_PRODUCT` (`product_id`),
  CONSTRAINT `FK_CATALOG_COMPARE_ITEM_CUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_COMPARE_ITEM_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_bundle_option')}`;
CREATE TABLE `{$installer->getTable('catalog_product_bundle_option')}` (
  `option_id` int(10) unsigned NOT NULL auto_increment,
  `product_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`option_id`),
  KEY `FK_catalog_product_bundle_option` (`product_id`),
  CONSTRAINT `FK_catalog_product_bundle_option` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_bundle_option_link')}`;
CREATE TABLE `{$installer->getTable('catalog_product_bundle_option_link')}` (
  `link_id` int(10) unsigned NOT NULL auto_increment,
  `option_id` int(10) unsigned NOT NULL default '0',
  `product_id` int(10) unsigned NOT NULL default '0',
  `discount` decimal(10,4) unsigned default NULL,
  PRIMARY KEY  (`link_id`),
  KEY `FK_catalog_product_bundle_option_link` (`option_id`),
  KEY `FK_catalog_product_bundle_option_link_entity` (`product_id`),
  CONSTRAINT `FK_catalog_product_bundle_option_link` FOREIGN KEY (`option_id`) REFERENCES `{$installer->getTable('catalog_product_bundle_option')}` (`option_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_catalog_product_bundle_option_link_entity` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_bundle_option_value')}`;
CREATE TABLE `{$installer->getTable('catalog_product_bundle_option_value')}` (
  `value_id` int(10) unsigned NOT NULL auto_increment,
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `option_id` int(10) unsigned NOT NULL default '0',
  `label` varchar(255) default NULL,
  `position` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`value_id`),
  KEY `FK_catalog_product_bundle_option_label` (`option_id`),
  CONSTRAINT `FK_catalog_product_bundle_option_label` FOREIGN KEY (`option_id`) REFERENCES `{$installer->getTable('catalog_product_bundle_option')}` (`option_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_entity')}`;
CREATE TABLE `{$installer->getTable('catalog_product_entity')}` (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `entity_type_id` smallint(8) unsigned NOT NULL default '0',
  `attribute_set_id` smallint(5) unsigned NOT NULL default '0',
  `type_id` varchar(32) NOT NULL default 'simple',
  `sku` varchar(64) default NULL,
  `category_ids` text,
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`entity_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_ENTITY_TYPE` (`entity_type_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_ATTRIBUTE_SET_ID` (`attribute_set_id`),
  KEY `sku` (`sku`),
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_ATTRIBUTE_SET_ID` FOREIGN KEY (`attribute_set_id`) REFERENCES `{$installer->getTable('eav_attribute_set')}` (`attribute_set_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_ENTITY_TYPE` FOREIGN KEY (`entity_type_id`) REFERENCES `{$installer->getTable('eav_entity_type')}` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Product Entities';

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_entity_datetime')}`;
CREATE TABLE `{$installer->getTable('catalog_product_entity_datetime')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`value_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_DATETIME_ATTRIBUTE` (`attribute_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_DATETIME_STORE` (`store_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_DATETIME_PRODUCT_ENTITY` (`entity_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_DATETIME_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_DATETIME_PRODUCT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_DATETIME_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_entity_decimal')}`;
CREATE TABLE `{$installer->getTable('catalog_product_entity_decimal')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` decimal(12,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`value_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_DECIMAL_STORE` (`store_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_DECIMAL_PRODUCT_ENTITY` (`entity_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_DECIMAL_ATTRIBUTE` (`attribute_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_DECIMAL_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_DECIMAL_PRODUCT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_DECIMAL_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_entity_gallery')}`;
CREATE TABLE `{$installer->getTable('catalog_product_entity_gallery')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` smallint(5) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `position` int(11) NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`value_id`),
  KEY `IDX_BASE` USING BTREE (`entity_type_id`,`entity_id`,`attribute_id`,`store_id`),
  KEY `FK_ATTRIBUTE_GALLERY_ENTITY` (`entity_id`),
  KEY `FK_CATALOG_CATEGORY_ENTITY_GALLERY_ATTRIBUTE` (`attribute_id`),
  KEY `FK_CATALOG_CATEGORY_ENTITY_GALLERY_STORE` (`store_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_GALLERY_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_GALLERY_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_GALLERY_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_entity_int')}`;
CREATE TABLE `{$installer->getTable('catalog_product_entity_int')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` mediumint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY  (`value_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_INT_ATTRIBUTE` (`attribute_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_INT_STORE` (`store_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_INT_PRODUCT_ENTITY` (`entity_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_INT_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_INT_PRODUCT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_INT_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_entity_media_gallery')}`;
CREATE TABLE `{$installer->getTable('catalog_product_entity_media_gallery')}` (
  `value_id` int(11) unsigned NOT NULL auto_increment,
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` varchar(255) default NULL,
  PRIMARY KEY  (`value_id`),
  KEY `FK_CATALOG_PRODUCT_MEDIA_GALLERY_ATTRIBUTE` (`attribute_id`),
  KEY `FK_CATALOG_PRODUCT_MEDIA_GALLERY_ENTITY` (`entity_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_MEDIA_GALLERY_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_MEDIA_GALLERY_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog product media gallery';

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_entity_media_gallery_value')}`;
CREATE TABLE `{$installer->getTable('catalog_product_entity_media_gallery_value')}` (
  `value_id` int(11) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `label` varchar(255) default NULL,
  `position` int(11) unsigned default NULL,
  `disabled` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`value_id`,`store_id`),
  KEY `FK_CATALOG_PRODUCT_MEDIA_GALLERY_VALUE_STORE` (`store_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_MEDIA_GALLERY_VALUE_GALLERY` FOREIGN KEY (`value_id`) REFERENCES `{$installer->getTable('catalog_product_entity_media_gallery')}` (`value_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_MEDIA_GALLERY_VALUE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog product media gallery values';

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_entity_text')}`;
CREATE TABLE `{$installer->getTable('catalog_product_entity_text')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` mediumint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`value_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_TEXT_ATTRIBUTE` (`attribute_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_TEXT_STORE` (`store_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_TEXT_PRODUCT_ENTITY` (`entity_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_TEXT_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_TEXT_PRODUCT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_TEXT_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_entity_tier_price')}`;
CREATE TABLE `{$installer->getTable('catalog_product_entity_tier_price')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `all_groups` tinyint(1) unsigned NOT NULL default '1',
  `customer_group_id` smallint(5) unsigned NOT NULL default '0',
  `qty` smallint(5) unsigned NOT NULL default '1',
  `value` decimal(12,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`value_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_TIER_PRICE_STORE` (`store_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_TIER_PRICE_PRODUCT_ENTITY` (`entity_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_TIER_PRICE_GROUP` (`customer_group_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_TIER_PRICE_GROUP` FOREIGN KEY (`customer_group_id`) REFERENCES `{$installer->getTable('customer_group')}` (`customer_group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_TIER_PRICE_PRODUCT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_TIER_PRICE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_entity_varchar')}`;
CREATE TABLE `{$installer->getTable('catalog_product_entity_varchar')}` (
  `value_id` int(11) NOT NULL auto_increment,
  `entity_type_id` mediumint(8) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`value_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_VARCHAR_ATTRIBUTE` (`attribute_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_VARCHAR_STORE` (`store_id`),
  KEY `FK_CATALOG_PRODUCT_ENTITY_VARCHAR_PRODUCT_ENTITY` (`entity_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_VARCHAR_ATTRIBUTE` FOREIGN KEY (`attribute_id`) REFERENCES `{$installer->getTable('eav_attribute')}` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_VARCHAR_PRODUCT_ENTITY` FOREIGN KEY (`entity_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_VARCHAR_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_link')}`;
CREATE TABLE `{$installer->getTable('catalog_product_link')}` (
  `link_id` int(11) unsigned NOT NULL auto_increment,
  `product_id` int(10) unsigned NOT NULL default '0',
  `linked_product_id` int(10) unsigned NOT NULL default '0',
  `link_type_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`link_id`),
  KEY `FK_LINK_PRODUCT` (`product_id`),
  KEY `FK_LINKED_PRODUCT` (`linked_product_id`),
  KEY `FK_PRODUCT_LINK_TYPE` (`link_type_id`),
  CONSTRAINT `FK_PRODUCT_LINK_LINKED_PRODUCT` FOREIGN KEY (`linked_product_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_PRODUCT_LINK_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_PRODUCT_LINK_TYPE` FOREIGN KEY (`link_type_id`) REFERENCES `{$installer->getTable('catalog_product_link_type')}` (`link_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Related products';

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_link_attribute')}`;
CREATE TABLE `{$installer->getTable('catalog_product_link_attribute')}` (
  `product_link_attribute_id` smallint(6) unsigned NOT NULL auto_increment,
  `link_type_id` tinyint(3) unsigned NOT NULL default '0',
  `product_link_attribute_code` varchar(32) NOT NULL default '',
  `data_type` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`product_link_attribute_id`),
  KEY `FK_ATTRIBUTE_PRODUCT_LINK_TYPE` (`link_type_id`),
  CONSTRAINT `FK_ATTRIBUTE_PRODUCT_LINK_TYPE` FOREIGN KEY (`link_type_id`) REFERENCES `{$installer->getTable('catalog_product_link_type')}` (`link_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Attributes for product link';

INSERT INTO `{$installer->getTable('catalog_product_link_attribute')}` VALUES
    (1, 2, 'qty', 'decimal'), (2, 1, 'position', 'int'), (3, 4, 'position', 'int'), (4, 5, 'position', 'int'), (6, 1, 'qty', 'decimal'), (7, 3, 'position', 'int'), (8, 3, 'qty', 'decimal');

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_link_attribute_decimal')}`;
CREATE TABLE `{$installer->getTable('catalog_product_link_attribute_decimal')}` (
  `value_id` int(11) unsigned NOT NULL auto_increment,
  `product_link_attribute_id` smallint(6) unsigned default NULL,
  `link_id` int(11) unsigned default NULL,
  `value` decimal(12,4) NOT NULL default '0.0000',
  PRIMARY KEY  (`value_id`),
  KEY `FK_DECIMAL_PRODUCT_LINK_ATTRIBUTE` (`product_link_attribute_id`),
  KEY `FK_DECIMAL_LINK` (`link_id`),
  CONSTRAINT `FK_DECIMAL_LINK` FOREIGN KEY (`link_id`) REFERENCES `{$installer->getTable('catalog_product_link')}` (`link_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_DECIMAL_PRODUCT_LINK_ATTRIBUTE` FOREIGN KEY (`product_link_attribute_id`) REFERENCES `{$installer->getTable('catalog_product_link_attribute')}` (`product_link_attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Decimal attributes values';

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_link_attribute_int')}`;
CREATE TABLE `{$installer->getTable('catalog_product_link_attribute_int')}` (
  `value_id` int(11) unsigned NOT NULL auto_increment,
  `product_link_attribute_id` smallint(6) unsigned default NULL,
  `link_id` int(11) unsigned default NULL,
  `value` int(11) NOT NULL default '0',
  PRIMARY KEY  (`value_id`),
  KEY `FK_INT_PRODUCT_LINK_ATTRIBUTE` (`product_link_attribute_id`),
  KEY `FK_INT_PRODUCT_LINK` (`link_id`),
  CONSTRAINT `FK_INT_PRODUCT_LINK` FOREIGN KEY (`link_id`) REFERENCES `{$installer->getTable('catalog_product_link')}` (`link_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_INT_PRODUCT_LINK_ATTRIBUTE` FOREIGN KEY (`product_link_attribute_id`) REFERENCES `{$installer->getTable('catalog_product_link_attribute')}` (`product_link_attribute_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_link_attribute_varchar')}`;
CREATE TABLE `{$installer->getTable('catalog_product_link_attribute_varchar')}` (
  `value_id` int(11) unsigned NOT NULL auto_increment,
  `product_link_attribute_id` smallint(6) unsigned NOT NULL default '0',
  `link_id` int(11) unsigned default NULL,
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`value_id`),
  KEY `FK_VARCHAR_PRODUCT_LINK_ATTRIBUTE` (`product_link_attribute_id`),
  KEY `FK_VARCHAR_LINK` (`link_id`),
  CONSTRAINT `FK_VARCHAR_LINK` FOREIGN KEY (`link_id`) REFERENCES `{$installer->getTable('catalog_product_link')}` (`link_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_VARCHAR_PRODUCT_LINK_ATTRIBUTE` FOREIGN KEY (`product_link_attribute_id`) REFERENCES `{$installer->getTable('catalog_product_link_attribute')}` (`product_link_attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Varchar attributes values';

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_link_type')}`;
CREATE TABLE `{$installer->getTable('catalog_product_link_type')}` (
  `link_type_id` tinyint(3) unsigned NOT NULL auto_increment,
  `code` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`link_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Types of product link(Related, superproduct, bundles)';

INSERT INTO `{$installer->getTable('catalog_product_link_type')}` VALUES
    (1, 'relation'), (2, 'bundle'), (3, 'super'), (4, 'up_sell'), (5, 'cross_sell');

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_super_attribute')}`;
CREATE TABLE `{$installer->getTable('catalog_product_super_attribute')}` (
  `product_super_attribute_id` int(10) unsigned NOT NULL auto_increment,
  `product_id` int(10) unsigned NOT NULL default '0',
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `position` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`product_super_attribute_id`),
  KEY `FK_SUPER_PRODUCT_ATTRIBUTE_PRODUCT` (`product_id`),
  CONSTRAINT `FK_SUPER_PRODUCT_ATTRIBUTE_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_super_attribute_label')}`;
CREATE TABLE `{$installer->getTable('catalog_product_super_attribute_label')}` (
  `value_id` int(10) unsigned NOT NULL auto_increment,
  `product_super_attribute_id` int(10) unsigned NOT NULL default '0',
  `store_id` smallint(5) unsigned NOT NULL default '0',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`value_id`),
  KEY `FK_SUPER_PRODUCT_ATTRIBUTE_LABEL` (`product_super_attribute_id`),
  CONSTRAINT `FK_SUPER_PRODUCT_ATTRIBUTE_LABEL` FOREIGN KEY (`product_super_attribute_id`) REFERENCES `{$installer->getTable('catalog_product_super_attribute')}` (`product_super_attribute_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_super_attribute_pricing')}`;
CREATE TABLE `{$installer->getTable('catalog_product_super_attribute_pricing')}` (
  `value_id` int(10) unsigned NOT NULL auto_increment,
  `product_super_attribute_id` int(10) unsigned NOT NULL default '0',
  `value_index` varchar(255) NOT NULL default '',
  `is_percent` tinyint(1) unsigned default '0',
  `pricing_value` decimal(10,4) default NULL,
  PRIMARY KEY  (`value_id`),
  KEY `FK_SUPER_PRODUCT_ATTRIBUTE_PRICING` (`product_super_attribute_id`),
  CONSTRAINT `FK_SUPER_PRODUCT_ATTRIBUTE_PRICING` FOREIGN KEY (`product_super_attribute_id`) REFERENCES `{$installer->getTable('catalog_product_super_attribute')}` (`product_super_attribute_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_super_link')}`;
CREATE TABLE `{$installer->getTable('catalog_product_super_link')}` (
  `link_id` int(10) unsigned NOT NULL auto_increment,
  `product_id` int(10) unsigned NOT NULL default '0',
  `parent_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`link_id`),
  KEY `FK_SUPER_PRODUCT_LINK_PARENT` (`parent_id`),
  KEY `FK_catalog_product_super_link` (`product_id`),
  CONSTRAINT `FK_SUPER_PRODUCT_LINK_ENTITY` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_SUPER_PRODUCT_LINK_PARENT` FOREIGN KEY (`parent_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `{$installer->getTable('catalog_product_website')}`;
CREATE TABLE `{$installer->getTable('catalog_product_website')}` (
  `product_id` int(10) unsigned NOT NULL auto_increment,
  `website_id` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`product_id`,`website_id`),
  KEY `FK_CATALOG_PRODUCT_WEBSITE_WEBSITE` (`website_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

$installer->endSetup();

$installer->installEntities();

/**
 * Create Root category node
 */
$category = Mage::getModel('catalog/category');
/* @var $category Mage_Catalog_Model_Category */
$category->setId(1)
    ->setPath(1)
    ->setName('Root Catalog')
    ->save();

$category->setId(null)
    ->setStoreId(0)
    ->setName('Default Category')
    ->setDisplayMode('PRODUCTS')
    ->setAttributeSetId($category->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setPath('1/')
    ->setInitialSetupFlag(true)
    ->save();

$category->setStoreId(1)
    ->save();