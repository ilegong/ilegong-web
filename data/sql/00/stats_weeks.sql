DROP TABLE IF EXISTS `cake_stats_weeks`;
CREATE TABLE IF NOT EXISTS `cake_stats_weeks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` varchar(20) DEFAULT NULL,
  `week` int(11) DEFAULT NULL,
  `view_nums` bigint(11) DEFAULT '0',
  `model` varchar(60) DEFAULT NULL,
  `data_id` int(11) DEFAULT NULL,
  `related` varchar(60) DEFAULT NULL,
  `favor_nums` bigint(11) DEFAULT '0',
  `comment_nums` bigint(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `year` (`year`,`week`,`model`,`data_id`,`related`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'StatsWeek', 'zh_cn', '11', 18, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-09 22:06:16', '2010-10-09 22:06:16', NULL),
(NULL, 'year', '1', '年', 'string', 'StatsWeek', 'zh_cn', '20', 17, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 22:07:58', '2010-10-09 22:07:58', NULL),
(NULL, 'week', '1', '周', 'integer', 'StatsWeek', 'zh_cn', '11', 16, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 22:08:24', '2010-10-09 22:08:24', NULL),
(NULL, 'view_nums', '1', '次数', 'integer', 'StatsWeek', 'zh_cn', '11', 15, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 22:08:53', '2010-10-09 22:08:53', NULL),
(NULL, 'model', '1', '模块', 'string', 'StatsWeek', 'zh_cn', '60', 14, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 22:09:11', '2010-10-09 22:09:11', NULL),
(NULL, 'data_id', '1', '数据编号', 'integer', 'StatsWeek', 'zh_cn', '11', 13, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 22:09:39', '2010-10-09 22:09:39', NULL),
(NULL, 'related', '1', '相关标记', 'string', 'StatsWeek', 'zh_cn', '60', 11, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 22:11:21', '2010-10-09 22:11:21', NULL),
(NULL, 'favor_nums', '1', '收藏数', 'integer', 'StatsWeek', 'zh_cn', '11', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-24 16:23:16', '2010-10-24 16:23:16', NULL),
(NULL, 'comment_nums', '1', '评论数', 'integer', 'StatsWeek', 'zh_cn', '11', NULL, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-24 16:23:42', '2010-10-24 16:23:42', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'StatsWeek', '周统计', '', 'default', '', 27, '2010-10-09 22:06:16', '2010-10-09 22:06:16', 'cake_stats_weeks', '', '', '', '0', 0, 0);
