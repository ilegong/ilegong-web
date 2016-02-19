alter table cake_users add score int default 0;
alter table cake_orders add applied_score int default 0;

create table cake_scores (
  `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint not null,
  `reason` int(10) NOT NULL,
  `score` int(10)  NOT NULL,
  `desc` varchar(255) NOT NULL,
  `orderId` bigint not null default 0,
  `commentId` bigint not null default 0,
  `data` varchar(1024) not null default '',
  `created` datetime null,
  PRIMARY KEY (`id`),
  key(`user_id`)
);