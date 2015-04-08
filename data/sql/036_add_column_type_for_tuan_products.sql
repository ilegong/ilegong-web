alter table cake_tuan_products add column `type` TINYINT(1) default '0' not null;

update cake_tuan_products set type = 1 where product_id in (851, 381, 868, 874, 876, 879, 883, 884);