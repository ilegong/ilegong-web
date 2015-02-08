create table cake_seckillings (
 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT 0,
  `type` char(12) NOT NULL,
  `delete` tinyint not null default 0,
  `sub_type` char(12) NOT NULL,
  `valid_begin` int(11) DEFAULT '0',
  `valid_end` int(11) DEFAULT '0',
    `created` datetime null,
    `updated` datetime null,
  PRIMARY KEY (`id`)
);


INSERT INTO `cake_seckillings` (`id`, `uid`, `type`, `delete`, `sub_type`, `valid_begin`, `valid_end`, `created`, `updated`)
VALUES
	(NULL, 0, '', 0, '', 0, 0, NULL, NULL);



