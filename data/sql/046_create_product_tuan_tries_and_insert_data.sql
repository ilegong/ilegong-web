CREATE TABLE `cake_product_tuan_tries` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `try_id` bigint(20) unsigned NOT NULL,
  `team_id` int(11)  unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8;

ALTER TABLE cake_product_tuan_tries
     ADD CONSTRAINT fk_cake_product_tuan_tries_try_id
     FOREIGN KEY (try_id)
     REFERENCES cake_product_tries(id);

ALTER TABLE cake_product_tuan_tries
     ADD CONSTRAINT fk_cake_product_tuan_tries_team_id
     FOREIGN KEY (team_id)
     REFERENCES cake_tuan_teams(id);

ALTER TABLE `cake_product_tries`
ADD COLUMN `global_show` int default 0;

insert into cake_product_tuan_tries (`try_id`, `team_id`) select pr.id, pr.tuan_id from cake_product_tries pr where pr.tuan_id>0;

update `cake_product_tries` set global_show = 1 where tuan_id is null;
