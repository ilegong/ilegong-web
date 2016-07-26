ALTER TABLE `51daifan`.`cake_balance_logs`
CHANGE COLUMN `rebate_fee` `rebate_fee` FLOAT NOT NULL DEFAULT 0 AFTER `coupon_fee`;


ALTER TABLE `51daifan`.`cake_balance_logs`
ADD COLUMN `use_rebate_fee` FLOAT NOT NULL DEFAULT 0 AFTER `rebate_fee`;
