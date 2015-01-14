alter table cake_users add score int default 0;
alter table cake_users add score_spent int default 0;

create table cake_score_logs (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `reason` int(10) NOT NULL,
  `score` int(10)  NOT NULL,
  `created` datetime null,
  PRIMARY KEY (`id`)
);

create table cake_score_spents (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `reason` int(10) NOT NULL,
  `spent` int(10)  NOT NULL,
  `created` datetime null,
  PRIMARY KEY (`id`)
);