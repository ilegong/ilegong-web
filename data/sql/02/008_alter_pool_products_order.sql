ALTER TABLE `cake_pool_products` ADD COLUMN `sort` smallint AFTER `status`;
update cake_pool_products set sort = id where sort is null;
