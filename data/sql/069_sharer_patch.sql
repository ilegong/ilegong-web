ALTER TABLE `cake_share_offers`
ADD COLUMN `sharer_id` INT NOT NULL DEFAULT 0 AFTER `is_default`;
