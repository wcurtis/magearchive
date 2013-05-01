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

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();
$conn = $installer->getConnection();
$conn->addColumn($installer->getTable('core_url_rewrite'), 'category_id', 'int unsigned NULL AFTER `store_id`');
$conn->addColumn($installer->getTable('core_url_rewrite'), 'product_id', 'int unsigned NULL AFTER `category_id`');
$installer->run("
UPDATE `{$installer->getTable('core_url_rewrite')}`
    SET `category_id`=SUBSTRING_INDEX(SUBSTR(`id_path` FROM 10),'/',1)
    WHERE `id_path` LIKE 'category/%';
UPDATE `{$installer->getTable('core_url_rewrite')}`
    SET `product_id`=SUBSTRING_INDEX(SUBSTR(`id_path` FROM 9),'/',1)
    WHERE `id_path` RLIKE 'product/[0-9]+$';
UPDATE `{$installer->getTable('core_url_rewrite')}`
    SET `category_id`=SUBSTRING_INDEX(SUBSTR(`id_path` FROM 9),'/',-1),
    `product_id`=SUBSTRING_INDEX(SUBSTR(`id_path` FROM 9),'/',1)
    WHERE `id_path` LIKE 'product/%/%';

CREATE TEMPORARY TABLE `_core_url_rewrite_delete` (`id` int unsigned not null) ENGINE=MEMORY;
INSERT INTO `_core_url_rewrite_delete` (`id`)
    SELECT `ur`.`url_rewrite_id` FROM `{$installer->getTable('core_url_rewrite')}` as `ur`
            LEFT JOIN `{$installer->getTable('catalog_category_entity')}` as `cc` ON `ur`.`category_id`=`cc`.`entity_id`
        WHERE `ur`.`category_id` IS NOT NULL
            AND `cc`.`entity_id` IS NULL;
INSERT INTO `_core_url_rewrite_delete` (`id`)
    SELECT `ur`.`url_rewrite_id` FROM `{$installer->getTable('core_url_rewrite')}` as `ur`
        LEFT JOIN `{$installer->getTable('catalog_product_entity')}` as `cp` ON `ur`.`product_id`=`cp`.`entity_id`
        WHERE `ur`.`product_id` IS NOT NULL
            AND `cp`.`entity_id` IS NULL;
DELETE FROM `{$installer->getTable('core_url_rewrite')}` WHERE `url_rewrite_id` IN(
    SELECT `id` FROM `_core_url_rewrite_delete`
);
DROP TABLE `_core_url_rewrite_delete`;

ALTER TABLE {$installer->getTable('core_url_rewrite')}
    ADD CONSTRAINT `FK_CORE_URL_REWRITE_CATEGORY` FOREIGN KEY (`category_id`)
    REFERENCES {$installer->getTable('catalog_category_entity')} (`entity_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `FK_CORE_URL_REWRITE_PRODUCT` FOREIGN KEY (`product_id`)
    REFERENCES {$installer->getTable('catalog_product_entity')} (`entity_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
");
$installer->endSetup();