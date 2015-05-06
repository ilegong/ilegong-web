alter table cake_tuan_products drop column `consignment_type`;

alter table cake_tuan_products add column `consignment_type` SMALLINT(1) default '0' not null;

update cake_tuan_products set consignment_type = 1 where product_id in (851, 381, 868, 874, 876, 879, 883, 884);

ALTER TABLE `cake_tuan_products`
CHANGE COLUMN `tuan_price` `tuan_price` FLOAT NOT NULL DEFAULT -1 ;
