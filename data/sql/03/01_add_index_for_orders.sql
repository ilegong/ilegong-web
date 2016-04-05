ALTER TABLE `cake_orders`
ADD INDEX `idx_orders_memberid_type` (`member_id` ASC, `type` ASC);


ALTER TABLE `cake_orders`
DROP INDEX `idx_orders_brandid_status` ,
ADD INDEX `idx_orders_memberid_status_type` (`status` ASC, `member_id` ASC, `type` ASC);

