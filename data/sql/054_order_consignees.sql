ALTER TABLE `cake_offline_stores`
ADD COLUMN `can_remark_address` TINYINT(1) NOT NULL DEFAULT 0 AFTER `deleted`;
ALTER TABLE `cake_order_consignees`
ADD COLUMN `remark_address` VARCHAR(100) NULL DEFAULT NULL AFTER `ziti_type`;
