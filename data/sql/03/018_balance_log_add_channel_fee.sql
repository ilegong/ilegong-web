ALTER TABLE `51daifan`.`cake_balance_logs`
ADD COLUMN `channel_total_fee` FLOAT NOT NULL DEFAULT 0 AFTER `proxy_rebate`;
