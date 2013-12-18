DROP TABLE IF EXISTS `cake_appraiselogs`;
CREATE TABLE IF NOT EXISTS `cake_appraiselogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT '',
  `cate_id` int(11) DEFAULT '0',
  `creator` int(13) DEFAULT '0',
  `lastupdator` int(11) DEFAULT '0',
  `remoteurl` varchar(200) DEFAULT '',
  `status` int(11) DEFAULT '0',
  `published` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `model` varchar(60) DEFAULT NULL,
  `data_id` int(11) DEFAULT NULL,
  `q_id` int(11) DEFAULT NULL,
  `q_optid` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;
REPLACE INTO `cake_i18nfields` (`id`, `name`, `savetodb`, `translate`, `type`, `model`, `locale`, `length`, `sort`, `allowadd`, `allowedit`, `selectmodel`, `selectvaluefield`, `selecttxtfield`, `selectparentid`, `selectautoload`, `selectvalues`, `associateflag`, `associateelement`, `associatefield`, `associatetype`, `formtype`, `default`, `allownull`, `validationregular`, `description`, `onchange`, `explodeimplode`, `explain`, `deleted`, `created`, `updated`, `conditions`) VALUES (NULL, 'id', '1', '编号', 'integer', 'Appraiselog', 'zh_cn', '11', 15, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-05 17:21:15', '2010-10-05 17:21:15', NULL),
(NULL, 'name', '1', '名称', 'string', 'Appraiselog', 'zh_cn', '200', 7, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-05 17:21:15', '2010-10-05 17:21:15', NULL),
(NULL, 'cate_id', '1', '所属分类', 'integer', 'Appraiselog', 'zh_cn', '11', 10, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-05 17:21:15', '2010-10-05 17:21:15', NULL),
(NULL, 'creator', '1', '编创建者', 'integer', 'Appraiselog', 'zh_cn', '11', 9, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-05 17:21:15', '2010-10-05 17:21:15', NULL),
(NULL, 'lastupdator', '1', '最后修改人', 'integer', 'Appraiselog', 'zh_cn', '11', 8, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-05 17:21:15', '2010-10-05 17:21:15', NULL),
(NULL, 'remoteurl', '1', '引用地址', 'string', 'Appraiselog', 'zh_cn', '200', 6, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-05 17:21:15', '2010-10-05 17:21:15', NULL),
(NULL, 'status', '1', '状态', 'integer', 'Appraiselog', 'zh_cn', '11', 5, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-05 17:21:15', '2010-10-05 17:21:15', NULL),
(NULL, 'published', '1', '是否发布', 'integer', 'Appraiselog', 'zh_cn', '11', 4, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-05 17:21:15', '2010-10-05 17:21:15', NULL),
(NULL, 'deleted', '1', '是否删除', 'integer', 'Appraiselog', 'zh_cn', '11', 3, '1', '1', NULL, NULL, NULL, NULL, '1', '0=>否\n1=>是', '0', NULL, NULL, 'equal', 'select', '0', '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-05 17:21:15', '2010-10-05 17:21:15', NULL),
(NULL, 'created', '1', '创建时间', 'datetime', 'Appraiselog', 'zh_cn', NULL, 2, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-05 17:21:15', '2010-10-05 17:21:15', NULL),
(NULL, 'updated', '1', '修改时间', 'datetime', 'Appraiselog', 'zh_cn', NULL, 1, '1', '1', NULL, NULL, NULL, NULL, '1', NULL, '0', NULL, NULL, 'equal', 'datetime', NULL, '1', NULL, NULL, NULL, NULL, NULL, 0, '2010-10-05 17:21:15', '2010-10-05 17:21:15', NULL),
(NULL, 'model', '1', '模块', 'string', 'Appraiselog', 'zh_cn', '60', 14, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', 'Article', '1', '', NULL, '', '', '', 0, '2010-10-05 17:23:29', '2010-10-05 17:23:29', NULL),
(NULL, 'data_id', '1', '数据编号', 'integer', 'Appraiselog', 'zh_cn', '11', 13, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-05 17:23:59', '2010-10-05 17:23:59', NULL),
(NULL, 'q_id', '1', '投票问题', 'integer', 'Appraiselog', 'zh_cn', '11', 12, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-05 17:25:05', '2010-10-05 17:25:05', NULL),
(NULL, 'q_optid', '1', '投票选项', 'integer', 'Appraiselog', 'zh_cn', '11', 11, '1', '1', '', NULL, NULL, NULL, '1', '', '0', '', '', '', 'input', '', '1', '', NULL, '', '', '', 0, '2010-10-05 17:25:41', '2010-10-05 17:25:41', NULL);
REPLACE INTO `cake_modelextends` (`id`, `name`, `cname`, `belongtype`, `modeltype`, `idtype`, `status`, `created`, `updated`, `tablename`, `related_model`, `security`, `operatorfields`, `deleted`, `cate_id`, `localetype`) VALUES (NULL, 'Appraiselog', '投票记录', '', 'default', '', 27, '2010-10-05 17:21:14', '2010-10-05 17:21:14', 'cake_appraiselogs', '', '', '', '0', 0, 0);
