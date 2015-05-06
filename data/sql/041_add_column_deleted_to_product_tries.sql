ALTER TABLE `cake_product_tries`
ADD COLUMN `deleted` SMALLINT(1) default '0' not null;

update cake_product_tries set deleted = 1 where status = 0;
