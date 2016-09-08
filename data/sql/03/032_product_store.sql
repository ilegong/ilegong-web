ALTER TABLE `51daifan`.`cake_weshare_products`
ADD COLUMN `sell_num` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `weight`;

ALTER TABLE `51daifan`.`cake_weshare_products`
ADD COLUMN `left_num` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `sell_num`;

ALTER TABLE `51daifan`.`cake_weshare_products`
CHANGE COLUMN `left_num` `left_num` INT(10) UNSIGNED NULL DEFAULT NULL ;

--reset product sell num

UPDATE cake_weshare_products as cwp inner join (SELECT sum(num) as sell_num,product_id FROM cake_carts WHERE order_id IN (SELECT id FROM cake_orders WHERE status != 0 AND status != 10 AND type = 9) GROUP BY product_id) as p_summary on p_summary.product_id = cwp.id
SET cwp.sell_num = p_summary.sell_num


--reset product store
update 51daifan.cake_weshare_products set store=store-cast(sell_num as signed) where store > 0;

update 51daifan.cake_weshare_products set store=9999 where store=0;

update 51daifan.cake_weshare_products set store=0 where store=-1;
