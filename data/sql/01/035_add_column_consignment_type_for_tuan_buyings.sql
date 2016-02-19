alter table cake_tuan_buyings drop column `consignment_type`;

alter table cake_tuan_buyings add column `consignment_type` SMALLINT(1) default '0' not null;

update cake_tuan_buyings set consignment_type = 1 where pid in (851, 381, 868, 874, 876, 879, 883, 884);