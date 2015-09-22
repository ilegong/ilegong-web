ALTER TABLE `cake_weshare_products`
ADD COLUMN `tag_id` INT NOT NULL DEFAULT 0 AFTER `tbd`;

ALTER TABLE `cake_carts`
ADD COLUMN `tag_id` INT NOT NULL DEFAULT 0 AFTER `confirm_price`;

