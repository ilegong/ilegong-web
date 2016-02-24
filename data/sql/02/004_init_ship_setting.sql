INSERT INTO `cake_weshare_delivery_templates`
(`id`,
`user_id`,
`weshare_id`,
`unit_type`,
`start_units`,
`start_fee`,
`add_units`,
`add_fee`,
`is_default`,
`created`)
SELECT 0,b.creator,a.weshare_id,0,1,a.ship_fee,1,0,1,'2016-02-24 19:00:00' FROM cake_weshare_ship_settings as a join cake_weshares as b on a.weshare_id = b.id where tag='kuai_di';

SET SQL_SAFE_UPDATES=0;
DELETE FROM `cake_weshare_delivery_templates`
USING `cake_weshare_delivery_templates`,(
  SELECT DISTINCT MIN(`id`) AS `id`,`weshare_id`,`user_id`
  FROM `cake_weshare_delivery_templates`
  GROUP BY `weshare_id`,`user_id`
  HAVING COUNT(1) > 1
) AS `t2`
WHERE `cake_weshare_delivery_templates`.`weshare_id` = `t2`.`weshare_id`
  AND `cake_weshare_delivery_templates`.`user_id` = `t2`.`user_id`
  AND `cake_weshare_delivery_templates`.`id` <> `t2`.`id`;

SELECT * from (SELECT count(*) as c FROM `cake_weshare_delivery_templates` group by weshare_id) as t where t.c>1