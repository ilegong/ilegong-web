ALTER TABLE `cake_weshares`
ADD COLUMN `cake_wesharescol` VARCHAR(45) NULL AFTER `offline_address_id`,
ADD COLUMN `view_count` INT NULL DEFAULT 0 AFTER `cake_wesharescol`;
