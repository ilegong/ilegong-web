DROP TABLE IF EXISTS `cake_languages`;
CREATE TABLE IF NOT EXISTS `cake_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `native` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `weight` int(11) DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `locale` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Language', 'zh_cn', '11', 11, '0', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'title', '1', '标题', 'string', 'Language', 'zh_cn', '255', 10, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'native', '1', 'native', 'string', 'Language', 'zh_cn', '255', 9, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'alias', '1', 'alias', 'string', 'Language', 'zh_cn', '255', 8, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'status', '1', '发布状态', 'boolean', 'Language', 'zh_cn', '1', 6, '1', '1', 'Misccate', 'id', 'name', 25, '1', NULL, '0', NULL, NULL, 'treenode', 'select', '1', '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'weight', '1', 'weight', 'integer', 'Language', 'zh_cn', '11', 5, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', '', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Language', 'zh_cn', NULL, 4, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Language', 'zh_cn', NULL, 3, '0', '0', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, '', NULL, NULL, NULL, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', NULL),
(NULL, 'default', '1', 'default', 'boolean', 'Language', 'zh_cn', '1', 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:44', '2011-02-20 09:21:44', NULL),
(NULL, 'active', '1', 'active', 'boolean', 'Language', 'zh_cn', '1', 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2011-02-20 09:21:44', '2011-02-20 09:21:44', NULL),
(NULL, 'locale', '1', '语言类型', 'string', 'Language', 'zh_cn', '10', 7, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', 'zh_cn', '1', '', NULL, '', '', '', 0, '2011-03-07 17:57:02', '2011-03-07 17:57:02', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Language', '语言', 'onetomany', 'default', '', 26, '2010-06-30 23:06:27', '2010-06-30 23:06:27', 'cake_languages', NULL, NULL, NULL, '0', 0, 0);



REPLACE INTO `cake_languages` (`id`, `title`, `native`, `alias`, `default`, `active`, `status`, `weight`, `updated`, `created`, `locale`) VALUES (2, 'chinese', '中文', 'zh-cn', '0', '1', '1', 1, '2011-03-06 23:46:05', '2010-03-01 11:56:03', 'zh_cn'),
(1, 'English', 'English', 'en-us', '0', '0', '1', 2, '2012-12-28 00:04:35', '2009-11-02 20:52:00', 'en_us'),
(3, 'Traditional Chinese', '繁體', 'zh-tw', '0', '0', '1', 3, '2012-12-28 00:04:28', '2012-08-17 16:35:45', 'zh_tw');
