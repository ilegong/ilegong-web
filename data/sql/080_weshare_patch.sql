ALTER TABLE `cake_weshare_products`
ADD COLUMN `tbd` TINYINT NOT NULL DEFAULT 0 AFTER `limit`;

ALTER TABLE `cake_orders`
ADD COLUMN `parent_order_id` INT NOT NULL DEFAULT 0 AFTER `remark_address`;

ALTER TABLE `cake_orders`
ADD COLUMN `price_difference` INT NOT NULL DEFAULT 0 AFTER `parent_order_id`;

ALTER TABLE `cake_orders`
ADD COLUMN `is_prepaid` TINYINT NOT NULL DEFAULT 0 AFTER `price_difference`;


ALTER TABLE `cake_refund_logs`
ADD COLUMN `type` TINYINT NOT NULL DEFAULT 0 AFTER `remark`;

--add
ALTER TABLE `cake_orders`
ADD COLUMN `is_process_prepaid` TINYINT(4) NOT NULL DEFAULT 0 AFTER `is_prepaid`;

ALTER TABLE `cake_orders`
CHANGE COLUMN `is_process_prepaid` `Is_process_prepaid` INT(4) NOT NULL DEFAULT '0' ;

ALTER TABLE `cake_orders`
CHANGE COLUMN `is_process_prepaid` `process_prepaid_status` INT(4) NOT NULL DEFAULT '0' ;

ALTER TABLE `cake_carts`
ADD COLUMN `confirm_price` TINYINT(4) NOT NULL DEFAULT 1 AFTER `tuan_buy_id`;


