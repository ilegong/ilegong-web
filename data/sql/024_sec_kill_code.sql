create table cake_seckillings (
 `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT 0,
  `type` char(12) NOT NULL,
  `code` varchar(32) NOT NULL default '',
  `delete` tinyint not null default 0,
  `published` tinyint not null default 1,
  `sub_type` char(12) NOT NULL,
  `valid_begin` int(11) DEFAULT '0',
  `valid_end` int(11) DEFAULT '0',
    `created` datetime null,
    `updated` datetime null,
    `occupied_at` datetime not null,
  PRIMARY KEY (`id`),
  unique (`uid`, `type`)
);


-- 规则里要说明，每个人只能秒一次大奖？是否需要这个约束？
-- 做好统计， 用于跟商家要数据， 要求更多大奖

-- 2/09
INSERT INTO `cake_seckillings` (`uid`, `type`, `sub_type`, `valid_begin`, `valid_end`, `code`)
VALUES
	(0, 'xiyang', 'wgwg-1', UNIX_TIMESTAMP('2015-02-09 09:00:00'), UNIX_TIMESTAMP('2015-02-09 09:59:59'), 'JXJ69WEM56'),
	(0, 'xiyang', 'wgwg-1', UNIX_TIMESTAMP('2015-02-09 12:00:00'), UNIX_TIMESTAMP('2015-02-09 12:59:59'), 'H34FX27VYK'),
	(0, 'xiyang', 'wgwg-1', UNIX_TIMESTAMP('2015-02-09 16:00:00'), UNIX_TIMESTAMP('2015-02-09 16:59:59'), 'NZ6X72X4C6'),
	(0, 'xiyang', 'wgwg-1', UNIX_TIMESTAMP('2015-02-09 21:00:00'), UNIX_TIMESTAMP('2015-02-09 21:59:59'), 'DXHPXVW4K3'),

	(0, 'xiyang', 'fqsm-1', UNIX_TIMESTAMP('2015-02-09 09:00:00'), UNIX_TIMESTAMP('2015-02-09 09:59:59'), ''),
	(0, 'xiyang', 'fqsm-1', UNIX_TIMESTAMP('2015-02-09 09:00:00'), UNIX_TIMESTAMP('2015-02-09 09:59:59'), ''),
	(0, 'xiyang', 'fqsm-1', UNIX_TIMESTAMP('2015-02-09 12:00:00'), UNIX_TIMESTAMP('2015-02-09 12:59:59'), ''),
	(0, 'xiyang', 'fqsm-1', UNIX_TIMESTAMP('2015-02-09 16:00:00'), UNIX_TIMESTAMP('2015-02-09 16:59:59'), ''),
	(0, 'xiyang', 'fqsm-1', UNIX_TIMESTAMP('2015-02-09 21:00:00'), UNIX_TIMESTAMP('2015-02-09 21:59:59'), ''),

	(0, 'xiyang', 'ytl-1', UNIX_TIMESTAMP('2015-02-09 09:00:00'), UNIX_TIMESTAMP('2015-02-09 09:59:59'), ''),
	(0, 'xiyang', 'ytl-1', UNIX_TIMESTAMP('2015-02-09 12:00:00'), UNIX_TIMESTAMP('2015-02-09 12:59:59'), ''),
	(0, 'xiyang', 'ytl-1', UNIX_TIMESTAMP('2015-02-09 16:00:00'), UNIX_TIMESTAMP('2015-02-09 16:59:59'), ''),
	(0, 'xiyang', 'ytl-1', UNIX_TIMESTAMP('2015-02-09 21:00:00'), UNIX_TIMESTAMP('2015-02-09 21:59:59'), '');

-- 2/13
INSERT INTO `cake_seckillings`(`uid`, `type`, `sub_type`, `valid_begin`, `valid_end`, `code`)
VALUES
	(0, 'xiyang', 'baojia-1', UNIX_TIMESTAMP('2015-02-13 21:00:00'), UNIX_TIMESTAMP('2015-02-13 21:59:59'), 'HWF6PEJ3X'),

	(0, 'xiyang', 'wgwg-1', UNIX_TIMESTAMP('2015-02-13 09:00:00'), UNIX_TIMESTAMP('2015-02-13 09:59:59'), 'JXJ69WEM56'),
	(0, 'xiyang', 'wgwg-1', UNIX_TIMESTAMP('2015-02-13 12:00:00'), UNIX_TIMESTAMP('2015-02-13 12:59:59'), 'H34FX27VYK'),
	(0, 'xiyang', 'wgwg-1', UNIX_TIMESTAMP('2015-02-13 16:00:00'), UNIX_TIMESTAMP('2015-02-13 16:59:59'), 'NZ6X72X4C6'),
	(0, 'xiyang', 'wgwg-1', UNIX_TIMESTAMP('2015-02-13 21:00:00'), UNIX_TIMESTAMP('2015-02-13 21:59:59'), 'DXHPXVW4K3'),

	(0, 'xiyang', 'fqsm-1', UNIX_TIMESTAMP('2015-02-13 09:00:00'), UNIX_TIMESTAMP('2015-02-13 09:59:59'), 'JXJ69WEM56'),
	(0, 'xiyang', 'fqsm-1', UNIX_TIMESTAMP('2015-02-13 09:00:00'), UNIX_TIMESTAMP('2015-02-13 09:59:59'), '4HWF6PEJ3X'),
	(0, 'xiyang', 'fqsm-1', UNIX_TIMESTAMP('2015-02-13 12:00:00'), UNIX_TIMESTAMP('2015-02-13 12:59:59'), 'H34FX27VYK'),
	(0, 'xiyang', 'fqsm-1', UNIX_TIMESTAMP('2015-02-13 16:00:00'), UNIX_TIMESTAMP('2015-02-13 16:59:59'), 'NZ6X72X4C6'),
	(0, 'xiyang', 'fqsm-1', UNIX_TIMESTAMP('2015-02-13 21:00:00'), UNIX_TIMESTAMP('2015-02-13 21:59:59'), 'DXHPXVW4K3'),

	(0, 'xiyang', 'ytl-1', UNIX_TIMESTAMP('2015-02-13 09:00:00'), UNIX_TIMESTAMP('2015-02-13 09:59:59'), ''),
	(0, 'xiyang', 'ytl-1', UNIX_TIMESTAMP('2015-02-13 12:00:00'), UNIX_TIMESTAMP('2015-02-13 12:59:59'), ''),
	(0, 'xiyang', 'ytl-1', UNIX_TIMESTAMP('2015-02-13 16:00:00'), UNIX_TIMESTAMP('2015-02-13 16:59:59'), ''),
	(0, 'xiyang', 'ytl-1', UNIX_TIMESTAMP('2015-02-13 21:00:00'), UNIX_TIMESTAMP('2015-02-13 21:59:59'), '');



