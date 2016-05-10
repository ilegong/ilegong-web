DROP TABLE IF EXISTS `cake_stats_days`;
CREATE TABLE IF NOT EXISTS `cake_stats_days` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` varchar(10) DEFAULT NULL,
  `month` varchar(2) DEFAULT NULL,
  `day` varchar(2) DEFAULT NULL,
  `view_nums` bigint(11) DEFAULT '0',
  `model` varchar(60) DEFAULT NULL,
  `data_id` int(11) DEFAULT NULL,
  `related` varchar(60) DEFAULT '',
  `date` char(10) DEFAULT NULL,
  `favor_nums` bigint(11) DEFAULT '0',
  `comment_nums` bigint(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `year` (`year`,`month`,`day`,`model`,`data_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'StatsDay', 'zh_cn', '11', 12, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-06 17:31:32', '2010-10-06 17:31:32', NULL),
(NULL, 'year', '1', '年', 'string', 'StatsDay', 'zh_cn', '10', 10, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-06 19:51:18', '2010-10-06 19:51:18', NULL),
(NULL, 'month', '1', '月', 'string', 'StatsDay', 'zh_cn', '2', 9, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-06 19:51:56', '2010-10-06 19:51:56', NULL),
(NULL, 'day', '1', '日', 'string', 'StatsDay', 'zh_cn', '2', 8, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-06 19:52:17', '2010-10-06 19:52:17', NULL),
(NULL, 'view_nums', '1', '次数', 'integer', 'StatsDay', 'zh_cn', '11', 7, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-06 19:53:19', '2010-10-06 19:53:19', NULL),
(NULL, 'model', '1', '模块', 'string', 'StatsDay', 'zh_cn', '60', 4, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-06 19:53:40', '2010-10-06 19:53:40', NULL),
(NULL, 'data_id', '1', '数据编号', 'integer', 'StatsDay', 'zh_cn', '11', 3, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-06 19:54:10', '2010-10-06 19:54:10', NULL),
(NULL, 'related', '1', '相关标记', 'string', 'StatsDay', 'zh_cn', '60', 1, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '记录问题编号选项编号等qid-optid', 0, '2010-10-06 19:56:30', '2010-10-06 19:56:30', NULL),
(NULL, 'date', '1', '日期', 'string', 'StatsDay', 'zh_cn', '20', 11, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-09 09:46:13', '2010-10-09 09:46:13', NULL),
(NULL, 'favor_nums', '1', '收藏数', 'integer', 'StatsDay', 'zh_cn', '11', 6, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-20 15:48:46', '2010-10-20 15:48:46', NULL),
(NULL, 'comment_nums', '1', '参与评论数', 'integer', 'StatsDay', 'zh_cn', '11', 5, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-20 15:49:33', '2010-10-20 15:49:33', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'StatsDay', '日统计', '', 'default', '', 27, '2010-10-06 17:31:32', '2010-10-06 17:31:32', 'cake_stats_days', '', '', '', '0', 0, 0);
