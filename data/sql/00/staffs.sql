DROP TABLE IF EXISTS `cake_staffs`;
CREATE TABLE IF NOT EXISTS `cake_staffs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` varchar(240) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `deleted` int(11) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `name` varchar(60) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `cardid` varchar(30) DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  `nickname` varchar(60) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Staff', 'zh_cn', '11', 15, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'status', '1', '职员状态', 'integer', 'Staff', 'zh_cn', '11', 4, '1', '1', '', '', '', NULL, '1', '1=>正式职员\n0=>临时职员\n2=>已离职', '0', '', '', 'equal', 'select', '0', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Staff', 'zh_cn', '11', 3, '0', '1', '', '', '', NULL, '1', '0=>否\n1=>是', '0', '', '', 'equal', 'select', '0', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Staff', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Staff', 'zh_cn', NULL, 1, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'name', '1', '员工姓名', 'string', 'Staff', 'zh_cn', '60', 14, '1', '1', '', '', '', NULL, '1', '', '0', NULL, NULL, 'equal', 'input', '', '1', '', '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'password', '1', '密码', 'string', 'Staff', 'zh_cn', '100', 13, '1', '1', '', '', '', NULL, '1', '', '0', '', '', '', 'input', '', '1', '', '', '', '', '若不修改密码，请留空', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'cardid', '1', '员工工号', 'string', 'Staff', 'zh_cn', '30', 12, '1', '1', '', '', '', NULL, '1', '', '0', NULL, NULL, 'equal', 'input', '', '1', '', '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'role_id', '1', '角色', 'string', 'Staff', 'zh_cn', '240', 10, '1', '1', 'Role', 'id', 'name', NULL, '1', '', '0', '', '', '', 'checkbox', '2', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', ''),
(NULL, 'email', '1', '邮箱', 'string', 'Staff', 'zh_cn', '60', 7, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'nickname', '1', '昵称', 'string', 'Staff', 'zh_cn', '60', 9, '1', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'image', '1', '头像', 'string', 'Staff', 'zh_cn', '255', 6, '0', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'file', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'last_login', '1', '最后登录时间', 'datetime', 'Staff', 'zh_cn', NULL, 5, '0', '1', '', '', '', NULL, '1', '', '0', '', '', 'equal', 'datetime', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'sex', '1', '性别', 'string', 'Staff', 'zh_cn', '10', 8, '1', '1', '', '', '', NULL, '1', '男=>男\n女=>女', '0', '', '', 'equal', 'select', '', '1', '', '', '', 'none', '', 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Staff', '职员', 'onetomany', 'default', '<id>', 27, '2010-08-03 22:42:05', '2010-08-03 22:42:05', 'cake_staffs', 'Tenure|hasMany=>staff_id', NULL, NULL, '0', 0, 0);
