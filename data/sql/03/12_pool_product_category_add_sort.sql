ALTER TABLE `cake_pool_product_categories`
ADD COLUMN `sort` INT NOT NULL DEFAULT 0 AFTER `deleted`;
