DROP TABLE IF EXISTS `cake_sessions`;
CREATE TABLE IF NOT EXISTS `cake_sessions` (
  `id` char(44) NOT NULL,
  `username` varchar(200) DEFAULT NULL,
  `uid` bigint(11) DEFAULT '0',
  `data` text,
  `expires` bigint(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'char', 'Session', 'zh_cn', '44', 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-12-04 09:44:29', '2011-12-04 09:44:29', ''),
(NULL, 'username', '1', '用户名', 'string', 'Session', 'zh_cn', '200', 5, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-12-04 09:44:29', '2011-12-04 09:44:29', ''),
(NULL, 'uid', '1', '用户编号', 'integer', 'Session', 'zh_cn', '11', 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', '', '', '1', '', NULL, '', '', '', 0, '2011-12-04 09:44:29', '2011-12-04 09:44:29', ''),
(NULL, 'data', '1', 'session信息', 'content', 'Session', 'zh_cn', '', 2, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'textarea', '', '1', '', NULL, '', '', '', 0, '2011-12-04 09:44:29', '2011-12-04 09:44:29', ''),
(NULL, 'expires', '1', '过期时间', 'integer', 'Session', 'zh_cn', '11', 1, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', 'equal', 'input', '', '1', '', NULL, '', '', '', 0, '2011-12-04 09:44:29', '2011-12-04 09:44:29', '');
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Session', 'Session', '', 'default', '', 27, '2011-12-04 09:44:29', '2011-12-04 09:44:29', 'cake_sessions', '', '', '', '0', 0, 0);
