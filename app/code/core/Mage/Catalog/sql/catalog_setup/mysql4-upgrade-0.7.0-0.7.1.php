<?php

$this->startSetup()->run("

alter table {$this->getTable('catalog_product_entity_tier_price')}
    ,add column `customer_group_id` smallint (5)UNSIGNED  DEFAULT '0' NOT NULL  after `entity_id`
    ,add constraint `FK_catalog_product_entity_tier_price_group` foreign key (`customer_group_id`) references {$this->getTable('customer_group')} (`customer_group_id`) on delete cascade  on update cascade
;

update {$this->getTable('catalog_product_entity_tier_price')} set `customer_group_id`=(select {$this->getTable('customer_group_id')} from `customer_group` limit 1);

")->endSetup();
