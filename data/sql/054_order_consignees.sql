ALTER TABLE `cake_order_consignees`
ADD COLUMN `remark_address` VARCHAR(100) NULL DEFAULT NULL AFTER `ziti_type`;
