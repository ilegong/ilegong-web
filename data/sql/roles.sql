DROP TABLE IF EXISTS `cake_roles`;
CREATE TABLE IF NOT EXISTS `cake_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `deleted` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias` (`alias`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Role', 'zh_cn', '11', 5, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'name', '1', '角色名称', 'string', 'Role', 'zh_cn', '100', 4, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', 'input', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'alias', '1', '别名', 'string', 'Role', 'zh_cn', '100', 3, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', '', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'created', '1', '创建时间', 'datetime', 'Role', 'zh_cn', NULL, 2, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'datetime', '', '1', '', '', '', '', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Role', 'zh_cn', NULL, 1, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Role', 'zh_cn', '11', 0, '0', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Role', '角色', 'onetomany', 'default', '', 26, '2010-06-30 23:06:27', '2010-06-30 23:06:27', 'cake_roles', NULL, NULL, NULL, '0', 0, 0);



REPLACE INTO `cake_roles` (`id`, `name`, `alias`, `deleted`, `created`, `updated`) VALUES (1, '系统管理组', 'admin', 0, '2009-04-05 00:10:34', '2010-08-09 21:20:33'),
(2, '普通职员组', 'registered', 0, '2009-04-05 00:10:50', '2010-08-09 21:21:45'),
(3, '人力资源组', 'hr', 0, '2010-08-21 15:20:05', '2010-08-29 11:51:12'),
(4, '内容管理组', 'content', 0, '2010-12-05 08:38:24', '2012-08-07 22:03:59'),
(5, '系统维护组', 'system', 0, '2012-08-07 21:46:34', '2012-08-07 22:26:42');
