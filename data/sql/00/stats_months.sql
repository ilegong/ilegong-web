DROP TABLE IF EXISTS `cake_stats_months`;
CREATE TABLE IF NOT EXISTS `cake_stats_months` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` char(4) DEFAULT NULL,
  `month` char(2) DEFAULT NULL,
  `year_month` char(7) DEFAULT NULL,
  `view_nums` bigint(11) DEFAULT '0',
  `model` varchar(60) DEFAULT NULL,
  `data_id` int(11) DEFAULT NULL,
  `related` varchar(60) DEFAULT '',
  `favor_nums` bigint(11) DEFAULT '0',
  `comment_nums` bigint(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `year_month` (`year_month`,`model`,`data_id`,`related`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'StatsMonth', 'zh_cn', '11', 19, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-09 22:12:27', '2010-10-09 22:12:27', NULL),
(NULL, 'year', '1', '年', 'string', 'StatsMonth', 'zh_cn', '20', 18, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 22:14:28', '2010-10-09 22:14:28', NULL),
(NULL, 'month', '1', '月', 'string', 'StatsMonth', 'zh_cn', '11', 17, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 22:14:48', '2010-10-09 22:14:48', NULL),
(NULL, 'year_month', '1', '年月', 'string', 'StatsMonth', 'zh_cn', '60', 16, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 22:15:17', '2010-10-09 22:15:17', NULL),
(NULL, 'view_nums', '1', '次数', 'integer', 'StatsMonth', 'zh_cn', '11', 15, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 22:16:06', '2010-10-09 22:16:06', NULL),
(NULL, 'model', '1', '模块', 'string', 'StatsMonth', 'zh_cn', '60', 14, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 22:16:34', '2010-10-09 22:16:34', NULL),
(NULL, 'data_id', '1', '数据编号', 'integer', 'StatsMonth', 'zh_cn', '11', 13, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 22:16:56', '2010-10-09 22:16:56', NULL),
(NULL, 'related', '1', '相关标记', 'string', 'StatsMonth', 'zh_cn', '60', 11, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 22:17:53', '2010-10-09 22:17:53', NULL),
(NULL, 'favor_nums', '1', '收藏数', 'integer', 'StatsMonth', 'zh_cn', '11', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-24 16:22:05', '2010-10-24 16:22:05', NULL),
(NULL, 'comment_nums', '1', '评论数', 'integer', 'StatsMonth', 'zh_cn', '11', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-24 16:22:29', '2010-10-24 16:22:29', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'StatsMonth', '月统计', '', 'default', '', 27, '2010-10-09 22:12:27', '2010-10-09 22:12:27', 'cake_stats_months', '', '', '', '0', 0, 0);
