CREATE TABLE `cake_refers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `step` int(11) NOT NULL DEFAULT '0',
  `from` bigint(20) NOT NULL,
  `to` bigint(20) NOT NULL,
  `last_ip` varchar(30) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `updated` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `latest_click_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_from` (`from`),
  KEY `idx_to` (`to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

alter table cake_refers add bind_done tinyint default 0;
alter table cake_refers add first_order_done tinyint default 0;
alter table cake_refers add first_comment_done tinyint default 0;
alter table cake_refers add got_notify tinyint default 0;

ALTER TABLE `cake_refers`
ADD COLUMN `first_order_id` INT NULL DEFAULT 0 AFTER `got_notify`;
