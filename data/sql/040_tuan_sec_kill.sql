ALTER TABLE `cake_product_tries`
ADD COLUMN `tuan_id` INT NULL AFTER `spec`;


ALTER TABLE `cake_product_tries`
ADD COLUMN `consignment_date` DATE NULL AFTER `tuan_id`;

