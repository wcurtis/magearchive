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
 * @package    Mage_Customer
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */
/**
 * Tables can not exist store columns
 */
try {
    $installer->startSetup();
    $installer->run("
        ALTER TABLE {$this->getTable('customer_entity_varchar')} DROP COLUMN `store_id`, DROP INDEX `FK_CUSTOMER_VARCHAR_STORE`, DROP FOREIGN KEY `FK_CUSTOMER_VARCHAR_STORE`;
        ALTER TABLE {$this->getTable('customer_entity_text')} DROP COLUMN `store_id`, DROP INDEX `FK_CUSTOMER_TEXT_STORE`, DROP FOREIGN KEY `FK_CUSTOMER_TEXT_STORE`;
        ALTER TABLE {$this->getTable('customer_entity_int')} DROP COLUMN `store_id`, DROP INDEX `FK_CUSTOMER_INT_STORE`, DROP FOREIGN KEY `FK_CUSTOMER_INT_STORE`;
        ALTER TABLE {$this->getTable('customer_entity_decimal')} DROP COLUMN `store_id`, DROP INDEX `FK_CUSTOMER_DECIMAL_STORE`, DROP FOREIGN KEY `FK_CUSTOMER_DECIMAL_STORE`;
        ALTER TABLE {$this->getTable('customer_entity_datetime')} DROP COLUMN `store_id`, DROP INDEX `FK_CUSTOMER_DATETIME_STORE`, DROP FOREIGN KEY `FK_CUSTOMER_DATETIME_STORE`;

        ALTER TABLE {$this->getTable('customer_address_entity_varchar')} DROP COLUMN `store_id`, DROP INDEX `FK_CUSTOMER_ADDRESS_VARCHAR_STORE`, DROP FOREIGN KEY `FK_CUSTOMER_ADDRESS_VARCHAR_STORE`;
        ALTER TABLE {$this->getTable('customer_address_entity_text')} DROP COLUMN `store_id`, DROP INDEX `FK_CUSTOMER_ADDRESS_TEXT_STORE`, DROP FOREIGN KEY `FK_CUSTOMER_ADDRESS_TEXT_STORE`;
        ALTER TABLE {$this->getTable('customer_address_entity_int')} DROP COLUMN `store_id`, DROP INDEX `FK_CUSTOMER_ADDRESS_INT_STORE`, DROP FOREIGN KEY `FK_CUSTOMER_ADDRESS_INT_STORE`;
        ALTER TABLE {$this->getTable('customer_address_entity_decimal')} DROP COLUMN `store_id`, DROP INDEX `FK_CUSTOMER_ADDRESS_DECIMAL_STORE`, DROP FOREIGN KEY `FK_CUSTOMER_ADDRESS_DECIMAL_STORE`;
        ALTER TABLE {$this->getTable('customer_address_entity_datetime')} DROP COLUMN `store_id`, DROP INDEX `FK_CUSTOMER_ADDRESS_DATETIME_STORE`, DROP FOREIGN KEY `FK_CUSTOMER_ADDRESS_DATETIME_STORE`;
    ");
}
catch (Exception $e) {}
$installer->endSetup();
